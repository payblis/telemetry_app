<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

checkAuth();

// Vérifier si un ID est fourni
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

try {
    // Vérifier que le pilote appartient bien à l'utilisateur connecté
    $stmt = $pdo->prepare("SELECT id FROM pilotes WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() === 0) {
        header("Location: index.php");
        exit;
    }

    // Supprimer le pilote
    $stmt = $pdo->prepare("DELETE FROM pilotes WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
} catch (PDOException $e) {
    // En cas d'erreur, on pourrait rediriger vers une page d'erreur
    // Pour l'instant, on redirige simplement vers la liste des pilotes
}

header("Location: index.php");
exit;
?> 