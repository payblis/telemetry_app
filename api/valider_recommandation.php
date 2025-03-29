<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/database.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Vérifier si les données nécessaires sont présentes
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    !isset($_POST['session_id']) || 
    !isset($_POST['probleme']) || 
    !isset($_POST['solution']) || 
    !isset($_POST['validation'])) {
    
    // Rediriger vers la page des recommandations avec une erreur
    header("Location: /telemoto/chatgpt/index.php?error=1");
    exit;
}

// Récupérer les données du formulaire
$session_id = intval($_POST['session_id']);
$probleme = trim($_POST['probleme']);
$solution = trim($_POST['solution']);
$validation = trim($_POST['validation']);

// Valider les données
if (!in_array($validation, ['positif', 'neutre', 'negatif'])) {
    header("Location: /telemoto/chatgpt/index.php?error=2");
    exit;
}

// Vérifier si la recommandation existe déjà
$sql = "SELECT id FROM recommandations 
        WHERE session_id = ? AND probleme = ? AND solution = ? AND source = 'chatgpt'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $session_id, $probleme, $solution);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    // La recommandation existe, mettre à jour la validation
    $row = $result->fetch_assoc();
    $recommandation_id = $row['id'];
    
    $sql = "UPDATE recommandations SET validation = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $validation, $recommandation_id);
    
    if ($stmt->execute()) {
        // Rediriger vers la page des recommandations avec un message de succès
        header("Location: /telemoto/chatgpt/index.php?success=1");
        exit;
    } else {
        // Erreur lors de la mise à jour
        header("Location: /telemoto/chatgpt/index.php?error=3");
        exit;
    }
} else {
    // La recommandation n'existe pas, l'insérer
    $sql = "INSERT INTO recommandations (session_id, probleme, solution, validation, source) 
            VALUES (?, ?, ?, ?, 'chatgpt')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $session_id, $probleme, $solution, $validation);
    
    if ($stmt->execute()) {
        // Rediriger vers la page des recommandations avec un message de succès
        header("Location: /telemoto/chatgpt/index.php?success=1");
        exit;
    } else {
        // Erreur lors de l'insertion
        header("Location: /telemoto/chatgpt/index.php?error=4");
        exit;
    }
}
?>
