<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/header.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM pilotes WHERE id = ?");
$stmt->execute([$id]);
$pilote = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE pilotes SET nom=?, prenom=?, pseudo=?, taille_cm=?, poids_kg=?, niveau=?, experience_annees=?, style_pilotage=?, sensibilite_grip=?, licence=?, numero_licence=?, notes=? WHERE id=?");
    $stmt->execute([
        $_POST['nom'], $_POST['prenom'], $_POST['pseudo'],
        $_POST['taille_cm'], $_POST['poids_kg'], $_POST['niveau'],
        $_POST['experience_annees'], $_POST['style_pilotage'],
        $_POST['sensibilite_grip'], isset($_POST['licence']) ? 1 : 0,
        $_POST['numero_licence'], $_POST['notes'], $id
    ]);
    header('Location: list.php');
    exit;
}
?>

<h2>Modifier le pilote</h2>
<form method="POST">
    Nom: <input name="nom" value="<?= htmlspecialchars($pilote['nom']) ?>"><br>
    Prénom: <input name="prenom" value="<?= htmlspecialchars($pilote['prenom']) ?>"><br>
    Pseudo: <input name="pseudo" value="<?= htmlspecialchars($pilote['pseudo']) ?>"><br>
    Taille (cm): <input name="taille_cm" type="number" value="<?= $pilote['taille_cm'] ?>"><br>
    Poids (kg): <input name="poids_kg" type="number" value="<?= $pilote['poids_kg'] ?>"><br>
    Niveau: <input name="niveau" value="<?= $pilote['niveau'] ?>"><br>
    Expérience (années): <input name="experience_annees" type="number" value="<?= $pilote['experience_annees'] ?>"><br>
    Style de pilotage: <input name="style_pilotage" value="<?= htmlspecialchars($pilote['style_pilotage']) ?>"><br>
    Sensibilité au grip: <input name="sensibilite_grip" value="<?= $pilote['sensibilite_grip'] ?>"><br>
    Licence: <input type="checkbox" name="licence" <?= $pilote['licence'] ? 'checked' : '' ?>><br>
    Numéro de licence: <input name="numero_licence" value="<?= htmlspecialchars($pilote['numero_licence']) ?>"><br>
    Notes: <textarea name="notes"><?= htmlspecialchars($pilote['notes']) ?></textarea><br>
    <button type="submit">Enregistrer</button>
</form>

<?php include '../includes/footer.php'; ?>
