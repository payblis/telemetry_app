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

// Vérifier si le circuit existe
$sql = "SELECT id, nom FROM circuits WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: /telemoto/circuits/index.php?error=2");
    exit;
}

$circuit = $result->fetch_assoc();

// Vérifier si le circuit a des sessions associées
$sql = "SELECT COUNT(*) as count FROM sessions WHERE circuit_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$hasSessions = ($row['count'] > 0);

// Traitement de la suppression
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    if ($hasSessions) {
        $message = "Impossible de supprimer ce circuit car il a des sessions associées.";
        $messageType = "danger";
    } else {
        $sql = "DELETE FROM circuits WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Redirection vers la liste des circuits avec un message de succès
            header("Location: /telemoto/circuits/index.php?success=3");
            exit;
        } else {
            $message = "Erreur lors de la suppression du circuit : " . $conn->error;
            $messageType = "danger";
        }
    }
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2 class="card-title">Supprimer un Circuit</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> 
        Êtes-vous sûr de vouloir supprimer le circuit <strong><?php echo htmlspecialchars($circuit['nom']); ?></strong> ?
        <?php if ($hasSessions): ?>
            <br><br>
            <strong>Attention :</strong> Ce circuit a des sessions associées. La suppression n'est pas possible.
        <?php else: ?>
            <br><br>
            Cette action est irréversible.
        <?php endif; ?>
    </div>
    
    <div class="form-actions">
        <a href="/telemoto/circuits/index.php" class="btn">Annuler</a>
        
        <?php if (!$hasSessions): ?>
            <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="confirm_delete" value="1">
                <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
