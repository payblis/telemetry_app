<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/header.php';

$pilotes = $pdo->query("SELECT * FROM pilotes")->fetchAll();
$motos = $pdo->query("SELECT * FROM motos")->fetchAll();
$circuits = $pdo->query("SELECT * FROM circuits")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO sessions (pilote_id, moto_id, circuit_id, date_session, type_session, meteo, temperature_air, temperature_piste, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['pilote_id'], $_POST['moto_id'], $_POST['circuit_id'],
        $_POST['date_session'], $_POST['type_session'],
        $_POST['meteo'], $_POST['temperature_air'], $_POST['temperature_piste'],
        $_POST['notes']
    ]);
    header('Location: list.php');
    exit;
}
?>

<h2>Ajouter une session</h2>
<form method="POST">
    Pilote:
    <select name="pilote_id"><?php foreach ($pilotes as $p) echo "<option value='{$p['id']}'>{$p['nom']} {$p['prenom']}</option>"; ?></select><br>
    Moto:
    <select name="moto_id"><?php foreach ($motos as $m) echo "<option value='{$m['id']}'>{$m['modele']}</option>"; ?></select><br>
    Circuit:
    <select name="circuit_id"><?php foreach ($circuits as $c) echo "<option value='{$c['id']}'>{$c['nom']}</option>"; ?></select><br>
    Date: <input type="date" name="date_session"><br>
    Type de session:
    <select name="type_session">
        <option>free practice</option>
        <option>qualification</option>
        <option>course</option>
        <option>trackday</option>
    </select><br>
    Météo: <input name="meteo"><br>
    Température air: <input type="number" step="0.1" name="temperature_air"><br>
    Température piste: <input type="number" step="0.1" name="temperature_piste"><br>
    Notes: <textarea name="notes"></textarea><br>
    <button type="submit">Ajouter</button>
</form>

<?php include '../includes/footer.php'; ?>
