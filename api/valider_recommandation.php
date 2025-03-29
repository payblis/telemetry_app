<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . url('chatgpt/index.php?error=1'));
    exit;
}

// Vérifier si les paramètres requis sont présents
if (!isset($_POST['recommandation_id']) || !isset($_POST['validation'])) {
    header("Location: " . url('chatgpt/index.php?error=2'));
    exit;
}

$recommandation_id = intval($_POST['recommandation_id']);
$validation = $_POST['validation'];

// Vérifier que la validation est valide
if (!in_array($validation, ['positif', 'negatif'])) {
    header("Location: " . url('chatgpt/index.php?error=3'));
    exit;
}

// Mettre à jour la validation de la recommandation
$sql = "UPDATE recommandations SET validation = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $validation, $recommandation_id);

if ($stmt->execute()) {
    header("Location: " . url('chatgpt/index.php?success=1'));
} else {
    header("Location: " . url('chatgpt/index.php?error=4'));
}
exit;
?>
