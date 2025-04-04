<?php
session_start();
require_once 'config/database.php';

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ajout d'un pilote
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_rider'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    try {
        $stmt = $db->prepare("INSERT INTO riders (name, email, phone) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $phone]);
        $success = "Pilote ajouté avec succès";
    } catch(PDOException $e) {
        $error = "Erreur lors de l'ajout du pilote : " . $e->getMessage();
    }
}

// Récupération des pilotes
$stmt = $db->query("SELECT * FROM riders ORDER BY created_at DESC");
$riders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Pilotes - Moto Telemetry</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Gestion des Pilotes</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="add-rider-form">
            <h2>Ajouter un pilote</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Nom</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="text" id="phone" name="phone">
                </div>
                <button type="submit" name="add_rider">Ajouter le pilote</button>
            </form>
        </div>

        <div class="riders-list">
            <h2>Liste des pilotes</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Date d'ajout</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riders as $rider): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rider['name']); ?></td>
                            <td><?php echo htmlspecialchars($rider['email']); ?></td>
                            <td><?php echo htmlspecialchars($rider['phone']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($rider['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 