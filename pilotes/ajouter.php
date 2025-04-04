<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

checkAuth();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING);
    $pseudo = filter_input(INPUT_POST, 'pseudo', FILTER_SANITIZE_STRING);
    $taille = filter_input(INPUT_POST, 'taille_cm', FILTER_VALIDATE_INT);
    $poids = filter_input(INPUT_POST, 'poids_kg', FILTER_VALIDATE_INT);
    $niveau = filter_input(INPUT_POST, 'niveau', FILTER_SANITIZE_STRING);
    $experience = filter_input(INPUT_POST, 'experience_annees', FILTER_VALIDATE_INT);
    $style = filter_input(INPUT_POST, 'style_pilotage', FILTER_SANITIZE_STRING);
    $sensibilite = filter_input(INPUT_POST, 'sensibilite_grip', FILTER_SANITIZE_STRING);
    $licence = isset($_POST['licence']) ? 1 : 0;
    $numero_licence = filter_input(INPUT_POST, 'numero_licence', FILTER_SANITIZE_STRING);
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);

    if (!$nom || !$prenom) {
        $message = "Le nom et le prénom sont obligatoires";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO pilotes (user_id, nom, prenom, pseudo, taille_cm, poids_kg, niveau, experience_annees, style_pilotage, sensibilite_grip, licence, numero_licence, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([
                $_SESSION['user_id'],
                $nom,
                $prenom,
                $pseudo,
                $taille,
                $poids,
                $niveau,
                $experience,
                $style,
                $sensibilite,
                $licence,
                $numero_licence,
                $notes
            ])) {
                header("Location: index.php");
                exit;
            }
        } catch (PDOException $e) {
            $message = "Erreur lors de l'ajout du pilote : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Pilote - Télémétrie Moto</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Ajouter un Pilote</h1>
        
        <?php if ($message): ?>
            <div class="alert"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="form-pilote">
            <div class="form-group">
                <label for="nom">Nom* :</label>
                <input type="text" id="nom" name="nom" required value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="prenom">Prénom* :</label>
                <input type="text" id="prenom" name="prenom" required value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="pseudo">Pseudo :</label>
                <input type="text" id="pseudo" name="pseudo" value="<?php echo isset($_POST['pseudo']) ? htmlspecialchars($_POST['pseudo']) : ''; ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="taille_cm">Taille (cm) :</label>
                    <input type="number" id="taille_cm" name="taille_cm" value="<?php echo isset($_POST['taille_cm']) ? htmlspecialchars($_POST['taille_cm']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="poids_kg">Poids (kg) :</label>
                    <input type="number" id="poids_kg" name="poids_kg" value="<?php echo isset($_POST['poids_kg']) ? htmlspecialchars($_POST['poids_kg']) : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="niveau">Niveau :</label>
                <select id="niveau" name="niveau">
                    <option value="">Sélectionnez un niveau</option>
                    <option value="Débutant">Débutant</option>
                    <option value="Intermédiaire">Intermédiaire</option>
                    <option value="Avancé">Avancé</option>
                    <option value="Expert">Expert</option>
                    <option value="Professionnel">Professionnel</option>
                </select>
            </div>

            <div class="form-group">
                <label for="experience_annees">Années d'expérience :</label>
                <input type="number" id="experience_annees" name="experience_annees" value="<?php echo isset($_POST['experience_annees']) ? htmlspecialchars($_POST['experience_annees']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="style_pilotage">Style de pilotage :</label>
                <input type="text" id="style_pilotage" name="style_pilotage" value="<?php echo isset($_POST['style_pilotage']) ? htmlspecialchars($_POST['style_pilotage']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="sensibilite_grip">Sensibilité au grip :</label>
                <select id="sensibilite_grip" name="sensibilite_grip">
                    <option value="">Sélectionnez une sensibilité</option>
                    <option value="Faible">Faible</option>
                    <option value="Moyenne">Moyenne</option>
                    <option value="Élevée">Élevée</option>
                </select>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="licence" <?php echo isset($_POST['licence']) ? 'checked' : ''; ?>>
                    Possède une licence
                </label>
            </div>

            <div class="form-group">
                <label for="numero_licence">Numéro de licence :</label>
                <input type="text" id="numero_licence" name="numero_licence" value="<?php echo isset($_POST['numero_licence']) ? htmlspecialchars($_POST['numero_licence']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="notes">Notes :</label>
                <textarea id="notes" name="notes" rows="4"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit">Ajouter le pilote</button>
                <a href="index.php" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>

    <style>
        .form-pilote {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-row {
            display: flex;
            gap: 1rem;
        }

        .form-row .form-group {
            flex: 1;
        }

        select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            resize: vertical;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-label input[type="checkbox"] {
            width: auto;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .form-actions button,
        .form-actions .btn-secondary {
            flex: 1;
            text-align: center;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            text-decoration: none;
        }
    </style>
</body>
</html> 