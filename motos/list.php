<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/header.php';

$motos = $pdo->query("SELECT m.*, p.nom, p.prenom FROM motos m JOIN pilotes p ON m.pilote_id = p.id")->fetchAll();
?>

<h2>Liste des motos</h2>
<a href="add.php">➕ Ajouter une moto</a>
<table border="1" cellpadding="10">
    <tr>
        <th>Pilote</th><th>Marque</th><th>Modèle</th><th>Année</th><th>Actions</th>
    </tr>
    <?php foreach ($motos as $m): ?>
    <tr>
        <td><?= htmlspecialchars($m['nom'] . ' ' . $m['prenom']) ?></td>
        <td><?= htmlspecialchars($m['marque']) ?></td>
        <td><?= htmlspecialchars($m['modele']) ?></td>
        <td><?= htmlspecialchars($m['annee']) ?></td>
        <td>
            <a href="edit.php?id=<?= $m['id'] ?>">✏️</a>
            <a href="delete.php?id=<?= $m['id'] ?>" onclick="return confirm('Supprimer ?')">🗑️</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php include '../includes/footer.php'; ?>
