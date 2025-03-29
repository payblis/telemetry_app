<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/database.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: /telemoto/motos/index.php?error=1");
    exit;
}

$id = intval($_GET['id']);

// Récupérer les données de la moto
$sql = "SELECT * FROM motos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: /telemoto/motos/index.php?error=2");
    exit;
}

$moto = $result->fetch_assoc();

// Récupérer les équipements spécifiques si moto de course
$equipements = [];
if ($moto['type'] === 'race') {
    $sql = "SELECT * FROM equipements_moto WHERE moto_id = ? ORDER BY categorie";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $equipements_result = $stmt->get_result();
    
    while ($equipement = $equipements_result->fetch_assoc()) {
        $equipements[] = $equipement;
    }
}

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
    
    // Si pas d'erreurs, mettre à jour dans la base de données
    if (empty($errors)) {
        $sql = "UPDATE motos SET marque = ?, modele = ?, cylindree = ?, annee = ?, type = ?, reglages_standards = ?, equipements_specifiques = ? WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiisssi", $marque, $modele, $cylindree, $annee, $type, $reglages_standards, $equipements_specifiques, $id);
        
        if ($stmt->execute()) {
            // Traitement des équipements spécifiques si type = race
            if ($type === 'race') {
                // Supprimer les équipements existants
                $sql = "DELETE FROM equipements_moto WHERE moto_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                // Ajouter les nouveaux équipements
                if (isset($_POST['equipement'])) {
                    foreach ($_POST['equipement'] as $index => $equipement) {
                        if (!empty($equipement['categorie']) && !empty($equipement['marque'])) {
                            $categorie = trim($equipement['categorie']);
                            $eq_marque = trim($equipement['marque']);
                            $eq_modele = trim($equipement['modele'] ?? '');
                            $specifications = trim($equipement['specifications'] ?? '');
                            
                            $sql = "INSERT INTO equipements_moto (moto_id, categorie, marque, modele, specifications) 
                                    VALUES (?, ?, ?, ?, ?)";
                            
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("issss", $id, $categorie, $eq_marque, $eq_modele, $specifications);
                            $stmt->execute();
                        }
                    }
                }
            }
            
            // Redirection vers la page de détails avec un message de succès
            header("Location: /telemoto/motos/view.php?id=$id&success=1");
            exit;
        } else {
            $message = "Erreur lors de la modification de la moto : " . $conn->error;
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
    <h2 class="card-title">Modifier une Moto</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" class="needs-validation" id="motoForm">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="marque">Marque *</label>
                <input type="text" id="marque" name="marque" required value="<?php echo htmlspecialchars($moto['marque']); ?>">
            </div>
            
            <div class="form-group col-md-6">
                <label for="modele">Modèle *</label>
                <input type="text" id="modele" name="modele" required value="<?php echo htmlspecialchars($moto['modele']); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="cylindree">Cylindrée (cc)</label>
                <input type="number" id="cylindree" name="cylindree" min="50" max="2000" value="<?php echo htmlspecialchars($moto['cylindree'] ?? ''); ?>">
            </div>
            
            <div class="form-group col-md-6">
                <label for="annee">Année</label>
                <input type="number" id="annee" name="annee" min="1970" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($moto['annee'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label>Type de moto *</label>
            <div class="radio-group">
                <label class="radio-container">
                    <input type="radio" name="type" value="origine" <?php echo ($moto['type'] === 'origine') ? 'checked' : ''; ?> onchange="toggleEquipementsSection()">
                    <span class="radio-label">Moto d'origine (100% standard)</span>
                </label>
                <label class="radio-container">
                    <input type="radio" name="type" value="race" <?php echo ($moto['type'] === 'race') ? 'checked' : ''; ?> onchange="toggleEquipementsSection()">
                    <span class="radio-label">Moto de course (équipements spécifiques)</span>
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label for="reglages_standards">Réglages standards</label>
            <textarea id="reglages_standards" name="reglages_standards" rows="4" placeholder="Fourche standard, amortisseur, pignon avant/arrière, etc."><?php echo htmlspecialchars($moto['reglages_standards'] ?? ''); ?></textarea>
            <small class="form-text">Vous pouvez saisir les réglages généraux ou précis (précharge, détente, compression, rapports de transmission, etc.)</small>
        </div>
        
        <div id="equipements-section" style="<?php echo ($moto['type'] === 'race') ? 'display:block' : 'display:none'; ?>">
            <h3>Équipements spécifiques</h3>
            
            <div class="form-group">
                <label for="equipements_specifiques">Description générale des équipements</label>
                <textarea id="equipements_specifiques" name="equipements_specifiques" rows="3" placeholder="Description générale des modifications et équipements spécifiques"><?php echo htmlspecialchars($moto['equipements_specifiques'] ?? ''); ?></textarea>
            </div>
            
            <div id="equipements-container">
                <?php if (!empty($equipements)): ?>
                    <?php foreach ($equipements as $index => $equipement): ?>
                        <div class="equipement-item">
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label>Catégorie</label>
                                    <select name="equipement[<?php echo $index; ?>][categorie]" class="equipement-categorie">
                                        <option value="">Sélectionner</option>
                                        <option value="Suspension avant" <?php echo ($equipement['categorie'] === 'Suspension avant') ? 'selected' : ''; ?>>Suspension avant</option>
                                        <option value="Suspension arrière" <?php echo ($equipement['categorie'] === 'Suspension arrière') ? 'selected' : ''; ?>>Suspension arrière</option>
                                        <option value="Échappement" <?php echo ($equipement['categorie'] === 'Échappement') ? 'selected' : ''; ?>>Échappement</option>
                                        <option value="Freinage" <?php echo ($equipement['categorie'] === 'Freinage') ? 'selected' : ''; ?>>Freinage</option>
                                        <option value="Transmission" <?php echo ($equipement['categorie'] === 'Transmission') ? 'selected' : ''; ?>>Transmission</option>
                                        <option value="Moteur" <?php echo ($equipement['categorie'] === 'Moteur') ? 'selected' : ''; ?>>Moteur</option>
                                        <option value="Électronique" <?php echo ($equipement['categorie'] === 'Électronique') ? 'selected' : ''; ?>>Électronique</option>
                                        <option value="Autre" <?php echo ($equipement['categorie'] === 'Autre') ? 'selected' : ''; ?>>Autre</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Marque</label>
                                    <input type="text" name="equipement[<?php echo $index; ?>][marque]" placeholder="Ex: Öhlins, Brembo..." value="<?php echo htmlspecialchars($equipement['marque']); ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Modèle</label>
                                    <input type="text" name="equipement[<?php echo $index; ?>][modele]" placeholder="Ex: FGR 300, M50..." value="<?php echo htmlspecialchars($equipement['modele']); ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-sm btn-danger remove-equipement">Supprimer</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Spécifications</label>
                                <textarea name="equipement[<?php echo $index; ?>][specifications]" rows="2" placeholder="Détails techniques, réglages spécifiques..."><?php echo htmlspecialchars($equipement['specifications']); ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Modèle d'équipement vide -->
                    <div class="equipement-item">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>Catégorie</label>
                                <select name="equipement[0][categorie]" class="equipement-categorie">
                                    <option value="">Sélectionner</option>
                                    <option value="Suspension avant">Suspension avant</option>
                                    <option value="Suspension arrière">Suspension arrière</option>
                                    <option value="Échappement">Échappement</option>
                                    <option value="Freinage">Freinage</option>
                                    <option value="Transmission">Transmission</option>
                                    <option value="Moteur">Moteur</option>
                                    <option value="Électronique">Électronique</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Marque</label>
                                <input type="text" name="equipement[0][marque]" placeholder="Ex: Öhlins, Brembo...">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Modèle</label>
                                <input type="text" name="equipement[0][modele]" placeholder="Ex: FGR 300, M50...">
                            </div>
                            <div class="form-group col-md-3">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-sm btn-danger remove-equipement" style="display:none;">Supprimer</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Spécifications</label>
                            <textarea name="equipement[0][specifications]" rows="2" placeholder="Détails techniques, réglages spécifiques..."></textarea>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group mt-2">
                <button type="button" class="btn btn-sm btn-secondary" id="add-equipement">
                    <i class="fas fa-plus"></i> Ajouter un équipement
                </button>
            </div>
        </div>
        
        <div class="form-actions">
            <a href="/telemoto/motos/view.php?id=<?php echo $id; ?>" class="btn">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
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
    let equipementCount = <?php echo !empty($equipements) ? count($equipements) : 1; ?>;
    
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
    
    // Ajouter les événements de suppression aux boutons existants
    document.querySelectorAll('.remove-equipement').forEach(button => {
        if (button.style.display !== 'none') {
            button.addEventListener('click', function() {
                const item = this.closest('.equipement-item');
                item.parentNode.removeChild(item);
            });
        }
    });
    
    // Initialiser l'affichage de la section des équipements
    toggleEquipementsSection();
});
</script>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
