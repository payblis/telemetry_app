<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../api/chatgpt.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Traitement du formulaire
$message = '';
$messageType = '';
$circuitData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $nom = trim($_POST['nom'] ?? '');
    
    // Validation des données
    $errors = [];
    
    if (empty($nom)) {
        $errors[] = "Le nom du circuit est obligatoire";
    }
    
    // Si pas d'erreurs, interroger ChatGPT pour obtenir les informations du circuit
    if (empty($errors)) {
        // Obtenir les informations du circuit via ChatGPT
        $response = obtenirInfosCircuit($nom);
        
        if ($response['success']) {
            $circuitData = $response['message'];
            
            // Analyser la réponse pour extraire les informations structurées
            $pays = '';
            $longueur = null;
            $largeur = null;
            $details_virages = '';
            
            // Extraction du pays (recherche de la localisation)
            if (preg_match('/localisation.*?:.*?([A-Za-z]+)/i', $circuitData, $matches) || 
                preg_match('/situ[ée] (?:en|au|aux) ([A-Za-z]+)/i', $circuitData, $matches) ||
                preg_match('/([A-Za-z]+)(?:\.|,)/i', $circuitData, $matches)) {
                $pays = trim($matches[1]);
            }
            
            // Extraction de la longueur
            if (preg_match('/longueur.*?:.*?(\d+[.,]?\d*)\s*(?:km|kilomètres)/i', $circuitData, $matches) ||
                preg_match('/(\d+[.,]?\d*)\s*(?:km|kilomètres)/i', $circuitData, $matches)) {
                $longueur = floatval(str_replace(',', '.', $matches[1]));
            }
            
            // Extraction de la largeur moyenne (si disponible)
            if (preg_match('/largeur.*?:.*?(\d+[.,]?\d*)\s*(?:m|mètres)/i', $circuitData, $matches) ||
                preg_match('/largeur moyenne.*?:.*?(\d+[.,]?\d*)\s*(?:m|mètres)/i', $circuitData, $matches)) {
                $largeur = intval($matches[1]);
            }
            
            // Extraction des détails des virages
            if (preg_match('/virages?.*?:(.*?)(?:zones de freinage|difficultés particulières|réglages recommandés|$)/is', $circuitData, $matches)) {
                $details_virages = trim($matches[1]);
            } else if (preg_match('/caractéristiques.*?virages?.*?:(.*?)(?:zones de freinage|difficultés particulières|réglages recommandés|$)/is', $circuitData, $matches)) {
                $details_virages = trim($matches[1]);
            }
            
            // Insérer les données dans la base de données
            $sql = "INSERT INTO circuits (nom, pays, longueur, largeur, details_virages) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdis", $nom, $pays, $longueur, $largeur, $details_virages);
            
            if ($stmt->execute()) {
                // Redirection vers la liste des circuits avec un message de succès
                header("Location: " . url('circuits/index.php?success=1'));
                exit;
            } else {
                $message = "Erreur lors de l'ajout du circuit : " . $conn->error;
                $messageType = "danger";
            }
        } else {
            $message = "Erreur lors de la récupération des informations du circuit : " . ($response['error'] ?? 'Erreur inconnue');
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
    <h2 class="card-title">Ajouter un Circuit via ChatGPT</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="mb-3">
        <p>Entrez simplement le nom du circuit et notre IA importera automatiquement toutes les informations disponibles (longueur, virages, caractéristiques techniques, etc.).</p>
    </div>
    
    <form method="POST" action="" class="needs-validation">
        <div class="form-group">
            <label for="nom">Nom du circuit *</label>
            <input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" placeholder="Ex: Circuit Bugatti, Le Mans">
            <small class="form-text">Soyez précis dans le nom du circuit pour obtenir les informations les plus exactes.</small>
        </div>
        
        <div class="form-actions">
            <a href="<?php echo url('circuits/index.php'); ?>" class="btn">Annuler</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-robot"></i> Importer les données via IA
            </button>
        </div>
    </form>
    
    <?php if ($circuitData): ?>
        <div class="circuit-data mt-4">
            <h3>Données récupérées par l'IA</h3>
            <div class="ai-response">
                <pre><?php echo htmlspecialchars($circuitData); ?></pre>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.ai-response {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: var(--border-radius);
    padding: 1rem;
    border: 1px solid var(--light-gray);
    max-height: 400px;
    overflow-y: auto;
}
.ai-response pre {
    white-space: pre-wrap;
    font-family: inherit;
    margin: 0;
}
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
