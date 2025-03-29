<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Traitement du formulaire
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $taille = $_POST['taille'];
    $poids = $_POST['poids'];
    $championnat = $_POST['championnat'];
    
    // Validation des données
    $errors = [];
    
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire";
    }
    
    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire";
    }
    
    // Si pas d'erreurs, insérer dans la base de données
    if (empty($errors)) {
        $sql = "INSERT INTO pilotes (nom, prenom, taille, poids, championnat) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssddss", $nom, $prenom, $taille, $poids, $championnat);
        
        if ($stmt->execute()) {
            // Redirection vers la liste des pilotes avec un message de succès
            header("Location: " . url('pilotes/index.php?success=1'));
            exit;
        } else {
            $message = "Erreur lors de l'ajout du pilote : " . $conn->error;
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
    <h2 class="card-title">Ajouter un Pilote</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" class="needs-validation">
        <div class="form-group">
            <label for="nom">Nom *</label>
            <input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="prenom">Prénom *</label>
            <input type="text" id="prenom" name="prenom" required value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="taille">Taille (m)</label>
            <input type="number" id="taille" name="taille" step="0.01" min="1.00" max="2.50" value="<?php echo htmlspecialchars($_POST['taille'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="poids">Poids (kg)</label>
            <input type="number" id="poids" name="poids" min="30" max="150" value="<?php echo htmlspecialchars($_POST['poids'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="championnat">Championnat</label>
            <input type="text" id="championnat" name="championnat" value="<?php echo htmlspecialchars($_POST['championnat'] ?? ''); ?>">
        </div>
        
        <div class="form-actions">
            <a href="<?php echo url('pilotes/index.php'); ?>" class="btn">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
</div>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
