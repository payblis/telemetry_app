<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $pseudo = $_POST['pseudo'] ?? '';
    $taille_cm = $_POST['taille_cm'] ?? '';
    $poids_kg = $_POST['poids_kg'] ?? '';
    $niveau = $_POST['niveau'] ?? '';
    $experience_annees = $_POST['experience_annees'] ?? '';
    $style_pilotage = $_POST['style_pilotage'] ?? '';
    $sensibilite_grip = $_POST['sensibilite_grip'] ?? '';
    $licence = isset($_POST['licence']) ? 1 : 0;
    $numero_licence = $_POST['numero_licence'] ?? '';
    $notes = $_POST['notes'] ?? '';

    if (empty($nom) || empty($prenom)) {
        $error = 'Le nom et le prénom sont obligatoires';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO pilotes (user_id, nom, prenom, pseudo, taille_cm, poids_kg, niveau, experience_annees, style_pilotage, sensibilite_grip, licence, numero_licence, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                $nom,
                $prenom,
                $pseudo,
                $taille_cm,
                $poids_kg,
                $niveau,
                $experience_annees,
                $style_pilotage,
                $sensibilite_grip,
                $licence,
                $numero_licence,
                $notes
            ]);
            $success = 'Pilote ajouté avec succès !';
        } catch (PDOException $e) {
            $error = 'Une erreur est survenue lors de l\'ajout du pilote';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un pilote - Moto SaaS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Ajouter un pilote</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom *</label>
                <input type="text" id="prenom" name="prenom" required>
            </div>
            
            <div class="form-group">
                <label for="pseudo">Pseudo</label>
                <input type="text" id="pseudo" name="pseudo">
            </div>
            
            <div class="form-group">
                <label for="taille_cm">Taille (cm)</label>
                <input type="number" id="taille_cm" name="taille_cm">
            </div>
            
            <div class="form-group">
                <label for="poids_kg">Poids (kg)</label>
                <input type="number" id="poids_kg" name="poids_kg">
            </div>
            
            <div class="form-group">
                <label for="niveau">Niveau</label>
                <select id="niveau" name="niveau">
                    <option value="">Sélectionner</option>
                    <option value="débutant">Débutant</option>
                    <option value="intermédiaire">Intermédiaire</option>
                    <option value="avancé">Avancé</option>
                    <option value="expert">Expert</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="experience_annees">Années d'expérience</label>
                <input type="number" id="experience_annees" name="experience_annees">
            </div>
            
            <div class="form-group">
                <label for="style_pilotage">Style de pilotage</label>
                <select id="style_pilotage" name="style_pilotage">
                    <option value="">Sélectionner</option>
                    <option value="smooth">Smooth</option>
                    <option value="agressif">Agressif</option>
                    <option value="mixte">Mixte</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="sensibilite_grip">Sensibilité au grip</label>
                <select id="sensibilite_grip" name="sensibilite_grip">
                    <option value="">Sélectionner</option>
                    <option value="faible">Faible</option>
                    <option value="moyenne">Moyenne</option>
                    <option value="élevée">Élevée</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="licence" value="1">
                    Possède une licence
                </label>
            </div>
            
            <div class="form-group">
                <label for="numero_licence">Numéro de licence</label>
                <input type="text" id="numero_licence" name="numero_licence">
            </div>
            
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="4"></textarea>
            </div>
            
            <button type="submit">Ajouter le pilote</button>
        </form>
        
        <p><a href="../dashboard.php">Retour au tableau de bord</a></p>
    </div>
</body>
</html> 