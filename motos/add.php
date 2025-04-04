<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/header.php';

$pilotes = $pdo->query("SELECT * FROM pilotes")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO motos (pilote_id, marque, modele, annee, configuration)
        VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['pilote_id'], $_POST['marque'], $_POST['modele'],
        $_POST['annee'], $_POST['configuration']
    ]);
    header('Location: list.php');
    exit;
}
?>

<h2>Ajouter une moto</h2>
<form method="POST">
    Pilote:
    <select name="pilote_id"><?php foreach ($pilotes as $p) echo "<option value='{$p['id']}'>{$p['nom']} {$p['prenom']}</option>"; ?></select><br>
    Marque: <input name="marque"><br>
    Modèle: <input name="modele"><br>
    Année: <input name="annee" type="number"><br>
    Configuration: <textarea name="configuration"></textarea><br>
    <button type="submit">Ajouter</button>
</form>

<?php include '../includes/footer.php'; ?>
