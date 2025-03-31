<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/database.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: /telemoto/circuits/index.php?error=1");
    exit;
}

$id = intval($_GET['id']);

// Récupérer les données du circuit
$sql = "SELECT * FROM circuits WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: /telemoto/circuits/index.php?error=2");
    exit;
}

$circuit = $result->fetch_assoc();

// Traitement du formulaire
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $nom = trim($_POST['nom'] ?? '');
    $pays = trim($_POST['pays'] ?? '');
    $longueur = !empty($_POST['longueur']) ? floatval($_POST['longueur']) : null;
    $largeur = !empty($_POST['largeur']) ? intval($_POST['largeur']) : null;
    $details_virages = trim($_POST['details_virages'] ?? '');
    
    // Validation des données
    $errors = [];
    
    if (empty($nom)) {
        $errors[] = "Le nom du circuit est obligatoire";
    }
    
    // Si pas d'erreurs, mettre à jour dans la base de données
    if (empty($errors)) {
        $sql = "UPDATE circuits SET nom = ?, pays = ?, longueur = ?, largeur = ?, details_virages = ? WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdssi", $nom, $pays, $longueur, $largeur, $details_virages, $id);
        
        if ($stmt->execute()) {
            // Redirection vers la liste des circuits avec un message de succès
            header("Location: /telemoto/circuits/index.php?success=2");
            exit;
        } else {
            $message = "Erreur lors de la modification du circuit : " . $conn->error;
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
    <h2 class="card-title">Modifier un Circuit</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" class="needs-validation">
        <div class="form-group">
            <label for="nom">Nom du circuit *</label>
            <input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($_POST['nom'] ?? $circuit['nom']); ?>">
        </div>
        
        <div class="form-group">
            <label for="pays">Pays</label>
            <input type="text" id="pays" name="pays" value="<?php echo htmlspecialchars($_POST['pays'] ?? $circuit['pays']); ?>">
        </div>
        
        <div class="form-group">
            <label for="longueur">Longueur (km)</label>
            <input type="number" id="longueur" name="longueur" step="0.001" min="0.5" max="20" value="<?php echo htmlspecialchars($_POST['longueur'] ?? $circuit['longueur']); ?>">
        </div>
        
        <div class="form-group">
            <label for="largeur">Largeur moyenne (m)</label>
            <input type="number" id="largeur" name="largeur" min="5" max="30" value="<?php echo htmlspecialchars($_POST['largeur'] ?? $circuit['largeur']); ?>">
        </div>
        
        <div class="form-group">
            <label for="details_virages">Détails des virages</label>
            <textarea id="details_virages" name="details_virages" rows="8" placeholder="Virage 1 (droite, angle 45°, 4ème rapport, vitesse apex ~130 km/h)
Virage 2 (gauche, angle 90°, 2ème rapport, vitesse apex ~80 km/h)
..."><?php echo htmlspecialchars($_POST['details_virages'] ?? $circuit['details_virages']); ?></textarea>
            <small class="form-text">Décrivez chaque virage avec ses caractéristiques (angle, vitesse estimée, rapport conseillé, difficultés particulières)</small>
        </div>
        
        <div class="form-actions">
            <a href="/telemoto/circuits/index.php" class="btn">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
</div>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
