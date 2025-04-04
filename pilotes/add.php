<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO pilotes (user_id, nom, prenom, pseudo, taille_cm, poids_kg, niveau, experience_annees, style_pilotage, sensibilite_grip, licence, numero_licence, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $_POST['nom'], $_POST['prenom'], $_POST['pseudo'],
        $_POST['taille_cm'], $_POST['poids_kg'], $_POST['niveau'],
        $_POST['experience_annees'], $_POST['style_pilotage'],
        $_POST['sensibilite_grip'], isset($_POST['licence']) ? 1 : 0,
        $_POST['numero_licence'], $_POST['notes']
    ]);
    header('Location: list.php');
    exit;
}
?>

<h2>Ajouter un pilote</h2>
<form method="POST">
    Nom: <input name="nom" required><br>
    Prénom: <input name="prenom"><br>
    Pseudo: <input name="pseudo"><br>
    Taille (cm): <input name="taille_cm" type="number"><br>
    Poids (kg): <input name="poids_kg" type="number"><br>
    Niveau: <select name="niveau">
        <option>Débutant</option>
        <option>Intermédiaire</option>
        <option>Confirmé</option>
        <option>Compétition</option>
    </select><br>
    Expérience (années): <input name="experience_annees" type="number"><br>
    Style de pilotage: <input name="style_pilotage"><br>
    Sensibilité au grip: <select name="sensibilite_grip">
        <option>Faible</option><option>Moyenne</option><option>Forte</option>
    </select><br>
    Licence: <input type="checkbox" name="licence"><br>
    Numéro de licence: <input name="numero_licence"><br>
    Notes: <textarea name="notes"></textarea><br>
    <button type="submit">Ajouter</button>
</form>

<?php include '../includes/footer.php'; ?>
