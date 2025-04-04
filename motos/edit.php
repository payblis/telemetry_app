<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/header.php';

$id = $_GET['id'];
$moto = $pdo->prepare("SELECT * FROM motos WHERE id = ?");
$moto->execute([$id]);
$m = $moto->fetch();

$pilotes = $pdo->query("SELECT * FROM pilotes")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE motos SET pilote_id=?, marque=?, modele=?, annee=?, configuration=? WHERE id=?");
    $stmt->execute([
        $_POST['pilote_id'], $_POST['marque'], $_POST['modele'],
        $_POST['annee'], $_POST['configuration'], $id
    ]);
    header('Location: list.php');
    exit;
}
?>

<h2>Modifier une moto</h2>
<form method="POST">
    Pilote:
    <select name="pilote_id"><?php foreach ($pilotes as $p) echo "<option value='{$p['id']}' " . ($p['id'] == $m['pilote_id'] ? "selected" : "") . ">{$p['nom']} {$p['prenom']}</option>"; ?></select><br>
    Marque: <input name="marque" value="<?= $m['marque'] ?>"><br>
    Modèle: <input name="modele" value="<?= $m['modele'] ?>"><br>
    Année: <input name="annee" type="number" value="<?= $m['annee'] ?>"><br>
    Configuration: <textarea name="configuration"><?= $m['configuration'] ?></textarea><br>
    <button type="submit">Enregistrer</button>
</form>

<?php include '../includes/footer.php'; ?>
