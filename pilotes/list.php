<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireLogin();

// Récupérer la liste des pilotes
$stmt = $pdo->prepare("SELECT * FROM pilotes WHERE user_id = ? ORDER BY nom, prenom");
$stmt->execute([$_SESSION['user_id']]);
$pilotes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des pilotes - Moto SaaS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Liste des pilotes</h1>
        
        <div class="actions">
            <a href="add.php" class="button">Ajouter un pilote</a>
        </div>
        
        <?php if (empty($pilotes)): ?>
            <p>Aucun pilote enregistré.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Pseudo</th>
                        <th>Niveau</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pilotes as $pilote): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pilote['nom']); ?></td>
                            <td><?php echo htmlspecialchars($pilote['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($pilote['pseudo']); ?></td>
                            <td><?php echo htmlspecialchars($pilote['niveau']); ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $pilote['id']; ?>" class="button">Modifier</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <p><a href="../dashboard.php">Retour au tableau de bord</a></p>
    </div>
</body>
</html> 