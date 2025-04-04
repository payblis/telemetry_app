<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO circuits (nom, pays, longueur_km, nb_virages, description)
        VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['nom'], $_POST['pays'], $_POST['longueur_km'],
        $_POST['nb_virages'], $_POST['description']
    ]);
    header('Location: list.php');
    exit;
}
?>

<h2>Ajouter un circuit</h2>
<form method="POST">
    Nom: <input name="nom"><br>
    Pays: <input name="pays"><br>
    Longueur (km): <input name="longueur_km" type="number" step="0.1"><br>
    Nombre de virages: <input name="nb_virages" type="number"><br>
    Description: <textarea name="description"></textarea><br>
    <button type="submit">Ajouter</button>
</form>

<?php include '../includes/footer.php'; ?>
