<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/header.php';

$stmt = $pdo->query("SELECT * FROM pilotes");
$pilotes = $stmt->fetchAll();
?>

<h2>Liste des pilotes</h2>
<a href="add.php">➕ Ajouter un pilote</a>
<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Pseudo</th>
        <th>Niveau</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($pilotes as $p): ?>
    <tr>
        <td><?= $p['id'] ?></td>
        <td><?= htmlspecialchars($p['nom']) ?></td>
        <td><?= htmlspecialchars($p['prenom']) ?></td>
        <td><?= htmlspecialchars($p['pseudo']) ?></td>
        <td><?= htmlspecialchars($p['niveau']) ?></td>
        <td>
            <a href="edit.php?id=<?= $p['id'] ?>">✏️</a>
            <a href="delete.php?id=<?= $p['id'] ?>" onclick="return confirm('Supprimer ce pilote ?');">🗑️</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php include '../includes/footer.php'; ?>
