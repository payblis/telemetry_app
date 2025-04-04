<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/header.php';

$id = $_GET['id'];
$circuit = $pdo->prepare("SELECT * FROM circuits WHERE id = ?");
$circuit->execute([$id]);
$c = $circuit->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE circuits SET nom=?, pays=?, longueur_km=?, nb_virages=?, description=? WHERE id=?");
    $stmt->execute([
        $_POST['nom'], $_POST['pays'], $_POST['longueur_km'],
        $_POST['nb_virages'], $_POST['description'], $id
    ]);
    header('Location: list.php');
    exit;
}
?>

<h2>Modifier un circuit</h2>
<form method="POST">
    Nom: <input name="nom" value="<?= $c['nom'] ?>"><br>
    Pays: <input name="pays" value="<?= $c['pays'] ?>"><br>
    Longueur (km): <input name="longueur_km" type="number" step="0.1" value="<?= $c['longueur_km'] ?>"><br>
    Nombre de virages: <input name="nb_virages" type="number" value="<?= $c['nb_virages'] ?>"><br>
    Description: <textarea name="description"><?= $c['description'] ?></textarea><br>
    <button type="submit">Enregistrer</button>
</form>

<?php include '../includes/footer.php'; ?>
