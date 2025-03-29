<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/database.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: /telemoto/pilotes/index.php?error=1");
    exit;
}

$id = intval($_GET['id']);

// Récupérer les données du pilote
$sql = "SELECT * FROM pilotes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: /telemoto/pilotes/index.php?error=2");
    exit;
}

$pilote = $result->fetch_assoc();

// Traitement du formulaire
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $taille = !empty($_POST['taille']) ? floatval($_POST['taille']) : null;
    $poids = !empty($_POST['poids']) ? intval($_POST['poids']) : null;
    $championnat = trim($_POST['championnat'] ?? '');
    
    // Validation des données
    $errors = [];
    
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire";
    }
    
    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire";
    }
    
    // Si pas d'erreurs, mettre à jour dans la base de données
    if (empty($errors)) {
        $sql = "UPDATE pilotes SET nom = ?, prenom = ?, taille = ?, poids = ?, championnat = ? WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdssi", $nom, $prenom, $taille, $poids, $championnat, $id);
        
        if ($stmt->execute()) {
            // Redirection vers la liste des pilotes avec un message de succès
            header("Location: /telemoto/pilotes/index.php?success=2");
            exit;
        } else {
            $message = "Erreur lors de la modification du pilote : " . $conn->error;
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
    <h2 class="card-title">Modifier un Pilote</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" class="needs-validation">
        <div class="form-group">
            <label for="nom">Nom *</label>
            <input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($_POST['nom'] ?? $pilote['nom']); ?>">
        </div>
        
        <div class="form-group">
            <label for="prenom">Prénom *</label>
            <input type="text" id="prenom" name="prenom" required value="<?php echo htmlspecialchars($_POST['prenom'] ?? $pilote['prenom']); ?>">
        </div>
        
        <div class="form-group">
            <label for="taille">Taille (m)</label>
            <input type="number" id="taille" name="taille" step="0.01" min="1.00" max="2.50" value="<?php echo htmlspecialchars($_POST['taille'] ?? $pilote['taille']); ?>">
        </div>
        
        <div class="form-group">
            <label for="poids">Poids (kg)</label>
            <input type="number" id="poids" name="poids" min="30" max="150" value="<?php echo htmlspecialchars($_POST['poids'] ?? $pilote['poids']); ?>">
        </div>
        
        <div class="form-group">
            <label for="championnat">Championnat</label>
            <input type="text" id="championnat" name="championnat" value="<?php echo htmlspecialchars($_POST['championnat'] ?? $pilote['championnat']); ?>">
        </div>
        
        <div class="form-actions">
            <a href="/telemoto/pilotes/index.php" class="btn">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
</div>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
