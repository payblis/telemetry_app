<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Vérifier si l'ID est fourni
if (!isset($_GET['id'])) {
    header("Location: " . url('pilotes/index.php?error=1'));
    exit;
}

$id = intval($_GET['id']);

// Vérifier si le pilote existe
$sql = "SELECT * FROM pilotes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: " . url('pilotes/index.php?error=2'));
    exit;
}

$pilote = $result->fetch_assoc();

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier s'il y a des sessions associées
    $sql = "SELECT COUNT(*) as count FROM sessions WHERE pilote_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        // Il y a des sessions associées, on ne peut pas supprimer
        header("Location: " . url('pilotes/index.php?error=3'));
        exit;
    }
    
    // Supprimer le pilote
    $sql = "DELETE FROM pilotes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: " . url('pilotes/index.php?success=3'));
        exit;
    }
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2 class="card-title">Supprimer le Pilote</h2>
    
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        Êtes-vous sûr de vouloir supprimer le pilote <?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?> ?
        Cette action est irréversible.
    </div>
    
    <form method="POST" class="form">
        <div class="form-actions">
            <a href="<?php echo url('pilotes/index.php'); ?>" class="btn">Annuler</a>
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Supprimer
            </button>
        </div>
    </form>
</div>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
