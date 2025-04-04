<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/header.php';

$id = $_GET['id'];
$session = $pdo->prepare("SELECT * FROM sessions WHERE id = ?");
$session->execute([$id]);
$s = $session->fetch();

$pilotes = $pdo->query("SELECT * FROM pilotes")->fetchAll();
$motos = $pdo->query("SELECT * FROM motos")->fetchAll();
$circuits = $pdo->query("SELECT * FROM circuits")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE sessions SET pilote_id=?, moto_id=?, circuit_id=?, date_session=?, type_session=?, meteo=?, temperature_air=?, temperature_piste=?, notes=? WHERE id=?");
    $stmt->execute([
        $_POST['pilote_id'], $_POST['moto_id'], $_POST['circuit_id'],
        $_POST['date_session'], $_POST['type_session'],
        $_POST['meteo'], $_POST['temperature_air'], $_POST['temperature_piste'],
        $_POST['notes'], $id
    ]);
    header('Location: list.php');
    exit;
}
?>

<h2>Modifier une session</h2>
<form method="POST">
    Pilote:
    <select name="pilote_id"><?php foreach ($pilotes as $p) echo "<option value='{$p['id']}' " . ($p['id'] == $s['pilote_id'] ? "selected" : "") . ">{$p['nom']} {$p['prenom']}</option>"; ?></select><br>
    Moto:
    <select name="moto_id"><?php foreach ($motos as $m) echo "<option value='{$m['id']}' " . ($m['id'] == $s['moto_id'] ? "selected" : "") . ">{$m['modele']}</option>"; ?></select><br>
    Circuit:
    <select name="circuit_id"><?php foreach ($circuits as $c) echo "<option value='{$c['id']}' " . ($c['id'] == $s['circuit_id'] ? "selected" : "") . ">{$c['nom']}</option>"; ?></select><br>
    Date: <input type="date" name="date_session" value="<?= $s['date_session'] ?>"><br>
    Type de session: <input name="type_session" value="<?= $s['type_session'] ?>"><br>
    Météo: <input name="meteo" value="<?= $s['meteo'] ?>"><br>
    Température air: <input name="temperature_air" value="<?= $s['temperature_air'] ?>"><br>
    Température piste: <input name="temperature_piste" value="<?= $s['temperature_piste'] ?>"><br>
    Notes: <textarea name="notes"><?= $s['notes'] ?></textarea><br>
    <button type="submit">Enregistrer</button>
</form>

<?php include '../includes/footer.php'; ?>
