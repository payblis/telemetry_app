<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireLogin();

$error = '';
$success = '';

// Vérifier si l'ID du pilote est fourni
if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit();
}

$pilote_id = $_GET['id'];

// Récupérer les informations du pilote
$stmt = $pdo->prepare("SELECT * FROM pilotes WHERE id = ? AND user_id = ?");
$stmt->execute([$pilote_id, $_SESSION['user_id']]);
$pilote = $stmt->fetch();

if (!$pilote) {
    header('Location: list.php');
    exit();
}

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
            $stmt = $pdo->prepare("UPDATE pilotes SET nom = ?, prenom = ?, pseudo = ?, taille_cm = ?, poids_kg = ?, niveau = ?, experience_annees = ?, style_pilotage = ?, sensibilite_grip = ?, licence = ?, numero_licence = ?, notes = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([
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
                $notes,
                $pilote_id,
                $_SESSION['user_id']
            ]);
            $success = 'Pilote mis à jour avec succès !';
            
            // Mettre à jour les données du pilote
            $pilote = [
                'nom' => $nom,
                'prenom' => $prenom,
                'pseudo' => $pseudo,
                'taille_cm' => $taille_cm,
                'poids_kg' => $poids_kg,
                'niveau' => $niveau,
                'experience_annees' => $experience_annees,
                'style_pilotage' => $style_pilotage,
                'sensibilite_grip' => $sensibilite_grip,
                'licence' => $licence,
                'numero_licence' => $numero_licence,
                'notes' => $notes
            ];
        } catch (PDOException $e) {
            $error = 'Une erreur est survenue lors de la mise à jour du pilote';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un pilote - Moto SaaS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Modifier un pilote</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($pilote['nom']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom *</label>
                <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($pilote['prenom']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="pseudo">Pseudo</label>
                <input type="text" id="pseudo" name="pseudo" value="<?php echo htmlspecialchars($pilote['pseudo']); ?>">
            </div>
            
            <div class="form-group">
                <label for="taille_cm">Taille (cm)</label>
                <input type="number" id="taille_cm" name="taille_cm" value="<?php echo htmlspecialchars($pilote['taille_cm']); ?>">
            </div>
            
            <div class="form-group">
                <label for="poids_kg">Poids (kg)</label>
                <input type="number" id="poids_kg" name="poids_kg" value="<?php echo htmlspecialchars($pilote['poids_kg']); ?>">
            </div>
            
            <div class="form-group">
                <label for="niveau">Niveau</label>
                <select id="niveau" name="niveau">
                    <option value="">Sélectionner</option>
                    <option value="débutant" <?php echo $pilote['niveau'] === 'débutant' ? 'selected' : ''; ?>>Débutant</option>
                    <option value="intermédiaire" <?php echo $pilote['niveau'] === 'intermédiaire' ? 'selected' : ''; ?>>Intermédiaire</option>
                    <option value="avancé" <?php echo $pilote['niveau'] === 'avancé' ? 'selected' : ''; ?>>Avancé</option>
                    <option value="expert" <?php echo $pilote['niveau'] === 'expert' ? 'selected' : ''; ?>>Expert</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="experience_annees">Années d'expérience</label>
                <input type="number" id="experience_annees" name="experience_annees" value="<?php echo htmlspecialchars($pilote['experience_annees']); ?>">
            </div>
            
            <div class="form-group">
                <label for="style_pilotage">Style de pilotage</label>
                <select id="style_pilotage" name="style_pilotage">
                    <option value="">Sélectionner</option>
                    <option value="smooth" <?php echo $pilote['style_pilotage'] === 'smooth' ? 'selected' : ''; ?>>Smooth</option>
                    <option value="agressif" <?php echo $pilote['style_pilotage'] === 'agressif' ? 'selected' : ''; ?>>Agressif</option>
                    <option value="mixte" <?php echo $pilote['style_pilotage'] === 'mixte' ? 'selected' : ''; ?>>Mixte</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="sensibilite_grip">Sensibilité au grip</label>
                <select id="sensibilite_grip" name="sensibilite_grip">
                    <option value="">Sélectionner</option>
                    <option value="faible" <?php echo $pilote['sensibilite_grip'] === 'faible' ? 'selected' : ''; ?>>Faible</option>
                    <option value="moyenne" <?php echo $pilote['sensibilite_grip'] === 'moyenne' ? 'selected' : ''; ?>>Moyenne</option>
                    <option value="élevée" <?php echo $pilote['sensibilite_grip'] === 'élevée' ? 'selected' : ''; ?>>Élevée</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="licence" value="1" <?php echo $pilote['licence'] ? 'checked' : ''; ?>>
                    Possède une licence
                </label>
            </div>
            
            <div class="form-group">
                <label for="numero_licence">Numéro de licence</label>
                <input type="text" id="numero_licence" name="numero_licence" value="<?php echo htmlspecialchars($pilote['numero_licence']); ?>">
            </div>
            
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="4"><?php echo htmlspecialchars($pilote['notes']); ?></textarea>
            </div>
            
            <button type="submit">Mettre à jour</button>
        </form>
        
        <p><a href="list.php">Retour à la liste des pilotes</a></p>
    </div>
</body>
</html> 