<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/header.php';

$total_pilotes = $pdo->query("SELECT COUNT(*) FROM pilotes")->fetchColumn();
$total_motos = $pdo->query("SELECT COUNT(*) FROM motos")->fetchColumn();
$total_circuits = $pdo->query("SELECT COUNT(*) FROM circuits")->fetchColumn();
$total_sessions = $pdo->query("SELECT COUNT(*) FROM sessions")->fetchColumn();

$sessions_par_mois = $pdo->query("SELECT DATE_FORMAT(date_session, '%Y-%m') AS mois, COUNT(*) AS total
    FROM sessions
    GROUP BY mois
    ORDER BY mois DESC
    LIMIT 6")->fetchAll();
?>

<h2>Dashboard</h2>
<div style="display:flex;gap:20px;margin-bottom:20px;">
    <div><strong>Total pilotes:</strong> <?= $total_pilotes ?></div>
    <div><strong>Total motos:</strong> <?= $total_motos ?></div>
    <div><strong>Total circuits:</strong> <?= $total_circuits ?></div>
    <div><strong>Total sessions:</strong> <?= $total_sessions ?></div>
</div>

<h3>Sessions par mois</h3>
<table border="1" cellpadding="5">
    <tr><th>Mois</th><th>Sessions</th></tr>
    <?php foreach ($sessions_par_mois as $s): ?>
    <tr>
        <td><?= $s['mois'] ?></td>
        <td><?= $s['total'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php include '../includes/footer.php'; ?>
