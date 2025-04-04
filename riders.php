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
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    try {
        $stmt = $db->prepare("INSERT INTO riders (last_name, first_name, email, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$last_name, $first_name, $email, $phone]);
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
        <header>
            <h1>Gestion des Pilotes</h1>
            <div class="user-info">
                <a href="dashboard.php" class="btn">Retour au tableau de bord</a>
            </div>
        </header>
        
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
                    <label for="last_name">Nom *</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="first_name">Prénom</label>
                    <input type="text" id="first_name" name="first_name">
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone">
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
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Date d'ajout</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riders as $rider): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rider['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($rider['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($rider['email']); ?></td>
                            <td><?php echo htmlspecialchars($rider['phone']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($rider['created_at'])); ?></td>
                            <td>
                                <a href="rider_profile.php?id=<?php echo $rider['id']; ?>" class="btn">Voir profil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 