<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/header.php';

$circuits = $pdo->query("SELECT * FROM circuits")->fetchAll();
?>

<h2>Liste des circuits</h2>
<a href="add.php">➕ Ajouter un circuit</a>
<table border="1" cellpadding="10">
    <tr>
        <th>Nom</th><th>Pays</th><th>Longueur</th><th>Virages</th><th>Actions</th>
    </tr>
    <?php foreach ($circuits as $c): ?>
    <tr>
        <td><?= htmlspecialchars($c['nom']) ?></td>
        <td><?= htmlspecialchars($c['pays']) ?></td>
        <td><?= htmlspecialchars($c['longueur_km']) ?> km</td>
        <td><?= $c['nb_virages'] ?></td>
        <td>
            <a href="edit.php?id=<?= $c['id'] ?>">✏️</a>
            <a href="delete.php?id=<?= $c['id'] ?>" onclick="return confirm('Supprimer ?')">🗑️</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php include '../includes/footer.php'; ?>
