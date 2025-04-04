<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/header.php';

$sessions = $pdo->query("SELECT s.*, p.nom AS pilote, m.modele, c.nom AS circuit
                         FROM sessions s
                         JOIN pilotes p ON s.pilote_id = p.id
                         JOIN motos m ON s.moto_id = m.id
                         JOIN circuits c ON s.circuit_id = c.id
                         ORDER BY s.date_session DESC")->fetchAll();
?>

<h2>Liste des sessions</h2>
<a href="add.php">➕ Ajouter une session</a>
<table border="1" cellpadding="10">
    <tr>
        <th>Date</th><th>Pilote</th><th>Moto</th><th>Circuit</th><th>Type</th><th>Actions</th>
    </tr>
    <?php foreach ($sessions as $s): ?>
    <tr>
        <td><?= $s['date_session'] ?></td>
        <td><?= htmlspecialchars($s['pilote']) ?></td>
        <td><?= htmlspecialchars($s['modele']) ?></td>
        <td><?= htmlspecialchars($s['circuit']) ?></td>
        <td><?= htmlspecialchars($s['type_session']) ?></td>
        <td>
            <a href="edit.php?id=<?= $s['id'] ?>">✏️</a>
            <a href="delete.php?id=<?= $s['id'] ?>" onclick="return confirm('Supprimer ?')">🗑️</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php include '../includes/footer.php'; ?>
