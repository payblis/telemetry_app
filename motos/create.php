<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/database.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Traitement du formulaire
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $marque = trim($_POST['marque'] ?? '');
    $modele = trim($_POST['modele'] ?? '');
    $cylindree = !empty($_POST['cylindree']) ? intval($_POST['cylindree']) : null;
    $annee = !empty($_POST['annee']) ? intval($_POST['annee']) : null;
    $type = trim($_POST['type'] ?? 'origine');
    $reglages_standards = trim($_POST['reglages_standards'] ?? '');
    $equipements_specifiques = trim($_POST['equipements_specifiques'] ?? '');
    
    // Validation des données
    $errors = [];
    
    if (empty($marque)) {
        $errors[] = "La marque est obligatoire";
    }
    
    if (empty($modele)) {
        $errors[] = "Le modèle est obligatoire";
    }
    
    // Si pas d'erreurs, insérer dans la base de données
    if (empty($errors)) {
        $sql = "INSERT INTO motos (marque, modele, cylindree, annee, type, reglages_standards, equipements_specifiques) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiisss", $marque, $modele, $cylindree, $annee, $type, $reglages_standards, $equipements_specifiques);
        
        if ($stmt->execute()) {
            // Récupérer l'ID de la moto insérée
            $moto_id = $conn->insert_id;
            
            // Traitement des équipements spécifiques si type = race
            if ($type === 'race' && isset($_POST['equipement'])) {
                foreach ($_POST['equipement'] as $index => $equipement) {
                    if (!empty($equipement['categorie']) && !empty($equipement['marque'])) {
                        $categorie = trim($equipement['categorie']);
                        $type_equipement = trim($equipement['type_equipement'] ?? '');
                        $eq_marque = trim($equipement['marque']);
                        $eq_modele = trim($equipement['modele'] ?? '');
                        $specifications = trim($equipement['specifications'] ?? '');
                        $valeurs_defaut = trim($equipement['valeurs_defaut'] ?? '');
                        
                        $sql = "INSERT INTO equipements_moto (moto_id, categorie, type_equipement, marque, modele, specifications, valeurs_defaut) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                        
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("issssss", $moto_id, $categorie, $type_equipement, $eq_marque, $eq_modele, $specifications, $valeurs_defaut);
                        $stmt->execute();
                    }
                }
            }
            
            // Redirection vers la liste des motos avec un message de succès
            header("Location: /telemoto/motos/index.php?success=1");
            exit;
        } else {
            $message = "Erreur lors de l'ajout de la moto : " . $conn->error;
            $messageType = "danger";
        }
    } else {
        $message = "Veuillez corriger les erreurs suivantes :<br>" . implode("<br>", $errors);
        $messageType = "danger";
    }
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2 class="card-title">Ajouter une Moto</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" class="needs-validation" id="motoForm">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="marque">Marque *</label>
                <input type="text" id="marque" name="marque" required value="<?php echo htmlspecialchars($_POST['marque'] ?? ''); ?>">
            </div>
            
            <div class="form-group col-md-6">
                <label for="modele">Modèle *</label>
                <input type="text" id="modele" name="modele" required value="<?php echo htmlspecialchars($_POST['modele'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="cylindree">Cylindrée (cc)</label>
                <input type="number" id="cylindree" name="cylindree" min="50" max="2000" value="<?php echo htmlspecialchars($_POST['cylindree'] ?? ''); ?>">
            </div>
            
            <div class="form-group col-md-6">
                <label for="annee">Année</label>
                <input type="number" id="annee" name="annee" min="1970" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($_POST['annee'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label>Type de moto *</label>
            <div class="radio-group">
                <label class="radio-container">
                    <input type="radio" name="type" value="origine" <?php echo (!isset($_POST['type']) || $_POST['type'] === 'origine') ? 'checked' : ''; ?> onchange="toggleEquipementsSection()">
                    <span class="radio-label">Moto d'origine (100% standard)</span>
                </label>
                <label class="radio-container">
                    <input type="radio" name="type" value="race" <?php echo (isset($_POST['type']) && $_POST['type'] === 'race') ? 'checked' : ''; ?> onchange="toggleEquipementsSection()">
                    <span class="radio-label">Moto de course (équipements spécifiques)</span>
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label for="reglages_standards">Réglages standards</label>
            <textarea id="reglages_standards" name="reglages_standards" rows="4" placeholder="Fourche standard, amortisseur, pignon avant/arrière, etc."><?php echo htmlspecialchars($_POST['reglages_standards'] ?? ''); ?></textarea>
            <small class="form-text">Vous pouvez saisir les réglages généraux ou précis (précharge, détente, compression, rapports de transmission, etc.)</small>
        </div>
        
        <div id="equipements-section" style="<?php echo (isset($_POST['type']) && $_POST['type'] === 'race') ? 'display:block' : 'display:none'; ?>">
            <h3>Équipements spécifiques</h3>
            
            <div class="form-group">
                <label for="equipements_specifiques">Description générale des équipements</label>
                <textarea id="equipements_specifiques" name="equipements_specifiques" rows="3" placeholder="Description générale des modifications et équipements spécifiques"><?php echo htmlspecialchars($_POST['equipements_specifiques'] ?? ''); ?></textarea>
            </div>
            
            <div id="equipements-container">
                <!-- Les équipements seront ajoutés ici dynamiquement -->
                <div class="equipement-item">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Catégorie *</label>
                            <select name="equipement[0][categorie]" class="equipement-categorie" required>
                                <option value="">Sélectionner</option>
                                <option value="Suspension">Suspension</option>
                                <option value="Châssis">Châssis</option>
                                <option value="Transmission">Transmission</option>
                                <option value="Freinage">Freinage</option>
                                <option value="Moteur">Moteur</option>
                                <option value="Électronique">Électronique</option>
                                <option value="Échappement">Échappement</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Type d'équipement *</label>
                            <select name="equipement[0][type_equipement]" class="type-equipement" required>
                                <option value="">Sélectionner</option>
                                <option value="Fourche avant">Fourche avant</option>
                                <option value="Amortisseur arrière">Amortisseur arrière</option>
                                <option value="Bras oscillant">Bras oscillant</option>
                                <option value="Tés de fourche">Tés de fourche</option>
                                <option value="Cadre">Cadre</option>
                                <option value="Direction">Direction</option>
                                <option value="Suspension arrière">Suspension arrière</option>
                                <option value="Roues">Roues</option>
                                <option value="Pneus">Pneus</option>
                                <option value="Freins">Freins</option>
                                <option value="Commande de frein">Commande de frein</option>
                                <option value="Embrayage">Embrayage</option>
                                <option value="Guidon">Guidon</option>
                                <option value="Repose-pieds">Repose-pieds</option>
                                <option value="Sélecteur de vitesse">Sélecteur de vitesse</option>
                                <option value="Boîte de vitesses">Boîte de vitesses</option>
                                <option value="Électronique">Électronique</option>
                                <option value="Télémétrie">Télémétrie</option>
                                <option value="Contrôle de traction">Contrôle de traction</option>
                                <option value="Échappement">Échappement</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Marque *</label>
                            <input type="text" name="equipement[0][marque]" placeholder="Ex: Öhlins, Brembo..." required>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Modèle</label>
                            <input type="text" name="equipement[0][modele]" placeholder="Ex: FGR 300, M50...">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Spécifications</label>
                            <textarea name="equipement[0][specifications]" rows="2" placeholder="Détails techniques, caractéristiques spécifiques..."></textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Valeurs par défaut</label>
                            <textarea name="equipement[0][valeurs_defaut]" rows="2" placeholder="Réglages par défaut, valeurs de base (clics, mm, tours)..."></textarea>
                            <small class="form-text">Ces valeurs seront utilisées comme base pour les sessions</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-sm btn-danger remove-equipement" style="display:none;">Supprimer</button>
                    </div>
                </div>
            </div>
            
            <div class="form-group mt-2">
                <button type="button" class="btn btn-sm btn-secondary" id="add-equipement">
                    <i class="fas fa-plus"></i> Ajouter un équipement
                </button>
            </div>
        </div>
        
        <div class="form-actions">
            <a href="/telemoto/motos/index.php" class="btn">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
</div>

<style>
.form-row {
    display: flex;
    flex-wrap: wrap;
    margin-right: -10px;
    margin-left: -10px;
}
.form-row > .form-group {
    padding-right: 10px;
    padding-left: 10px;
}
.col-md-3 {
    flex: 0 0 25%;
    max-width: 25%;
}
.col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
}
.radio-group {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 0.5rem;
}
.radio-container {
    display: flex;
    align-items: center;
    cursor: pointer;
}
.radio-label {
    margin-left: 0.5rem;
}
.equipement-item {
    background-color: rgba(0, 0, 0, 0.1);
    border-radius: var(--border-radius);
    padding: 1rem;
    margin-bottom: 1rem;
}
.mt-2 {
    margin-top: 0.5rem;
}
@media (max-width: 768px) {
    .col-md-3, .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
</style>

<script>
// Fonction pour afficher/masquer la section des équipements spécifiques
function toggleEquipementsSection() {
    const type = document.querySelector('input[name="type"]:checked').value;
    const equipementsSection = document.getElementById('equipements-section');
    
    if (type === 'race') {
        equipementsSection.style.display = 'block';
    } else {
        equipementsSection.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialiser le compteur d'équipements
    let equipementCount = 1;
    
    // Ajouter un équipement
    document.getElementById('add-equipement').addEventListener('click', function() {
        const container = document.getElementById('equipements-container');
        const newEquipement = document.querySelector('.equipement-item').cloneNode(true);
        
        // Mettre à jour les noms des champs
        const inputs = newEquipement.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace(/\[\d+\]/, '[' + equipementCount + ']'));
                input.value = ''; // Réinitialiser la valeur
            }
        });
        
        // Afficher le bouton de suppression
        const removeButton = newEquipement.querySelector('.remove-equipement');
        removeButton.style.display = 'block';
        
        // Ajouter l'événement de suppression
        removeButton.addEventListener('click', function() {
            container.removeChild(newEquipement);
        });
        
        // Ajouter le nouvel équipement au conteneur
        container.appendChild(newEquipement);
        
        // Incrémenter le compteur
        equipementCount++;
    });
    
    // Initialiser l'affichage de la section des équipements
    toggleEquipementsSection();
});
</script>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
