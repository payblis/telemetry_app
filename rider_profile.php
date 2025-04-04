<?php
session_start();
require_once 'config/database.php';

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupération de l'ID du pilote depuis l'URL
$rider_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($rider_id === 0) {
    header('Location: riders.php');
    exit();
}

// Récupération des informations du pilote
$stmt = $db->prepare("SELECT * FROM riders WHERE id = ?");
$stmt->execute([$rider_id]);
$rider = $stmt->fetch();

if (!$rider) {
    header('Location: riders.php');
    exit();
}

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $data = [
        'last_name' => $_POST['last_name'],
        'first_name' => $_POST['first_name'],
        'track_name' => $_POST['track_name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'height' => (int)$_POST['height'],
        'weight' => (float)$_POST['weight'],
        'preferred_position' => $_POST['preferred_position'],
        'arm_length' => (int)$_POST['arm_length'],
        'leg_length' => (int)$_POST['leg_length'],
        'level' => $_POST['level'],
        'years_experience' => (int)$_POST['years_experience'],
        'riding_types' => implode(',', $_POST['riding_types']),
        'has_license' => isset($_POST['has_license']) ? 1 : 0,
        'license_number' => $_POST['license_number'],
        'has_coach' => isset($_POST['has_coach']) ? 1 : 0,
        'front_grip_sensitivity' => $_POST['front_grip_sensitivity'],
        'rear_grip_sensitivity' => $_POST['rear_grip_sensitivity'],
        'riding_style' => $_POST['riding_style'],
        'progression_areas' => $_POST['progression_areas']
    ];

    // Calcul de l'IMC
    if ($data['height'] > 0 && $data['weight'] > 0) {
        $height_m = $data['height'] / 100;
        $data['bmi'] = $data['weight'] / ($height_m * $height_m);
    }

    // Mise à jour dans la base de données
    $sql = "UPDATE riders SET ";
    $params = [];
    foreach ($data as $key => $value) {
        $sql .= "$key = ?, ";
        $params[] = $value;
    }
    $sql = rtrim($sql, ', ');
    $sql .= " WHERE id = ?";
    $params[] = $rider_id;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    // Redirection pour éviter la soumission multiple du formulaire
    header("Location: rider_profile.php?id=$rider_id&success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pilote - Moto Telemetry</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Profil Pilote</h1>
            <div class="user-info">
                <a href="riders.php" class="btn">Retour à la liste</a>
            </div>
        </header>

        <?php if (isset($_GET['success'])): ?>
            <div class="success">Profil mis à jour avec succès</div>
        <?php endif; ?>

        <form method="POST" action="" class="rider-profile-form">
            <div class="form-section">
                <h2>Informations personnelles</h2>
                <div class="form-group">
                    <label for="last_name">Nom *</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($rider['last_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="first_name">Prénom</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($rider['first_name']); ?>">
                </div>
                <div class="form-group">
                    <label for="track_name">Pseudo / Nom de piste</label>
                    <input type="text" id="track_name" name="track_name" value="<?php echo htmlspecialchars($rider['track_name']); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($rider['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($rider['phone']); ?>">
                </div>
            </div>

            <div class="form-section">
                <h2>Informations morphologiques</h2>
                <div class="form-group">
                    <label for="height">Taille (cm)</label>
                    <input type="number" id="height" name="height" value="<?php echo htmlspecialchars($rider['height']); ?>">
                </div>
                <div class="form-group">
                    <label for="weight">Poids (kg)</label>
                    <input type="number" step="0.1" id="weight" name="weight" value="<?php echo htmlspecialchars($rider['weight']); ?>">
                </div>
                <?php if ($rider['bmi']): ?>
                    <div class="form-group">
                        <label>IMC</label>
                        <input type="text" value="<?php echo number_format($rider['bmi'], 2); ?>" readonly>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="preferred_position">Position préférée sur la moto</label>
                    <select id="preferred_position" name="preferred_position">
                        <option value="">Sélectionner</option>
                        <option value="avant" <?php echo $rider['preferred_position'] === 'avant' ? 'selected' : ''; ?>>Avant</option>
                        <option value="neutre" <?php echo $rider['preferred_position'] === 'neutre' ? 'selected' : ''; ?>>Neutre</option>
                        <option value="arriere" <?php echo $rider['preferred_position'] === 'arriere' ? 'selected' : ''; ?>>Arrière</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="arm_length">Longueur des bras (cm)</label>
                    <input type="number" id="arm_length" name="arm_length" value="<?php echo htmlspecialchars($rider['arm_length']); ?>">
                </div>
                <div class="form-group">
                    <label for="leg_length">Longueur des jambes (cm)</label>
                    <input type="number" id="leg_length" name="leg_length" value="<?php echo htmlspecialchars($rider['leg_length']); ?>">
                </div>
            </div>

            <div class="form-section">
                <h2>Niveau / expérience</h2>
                <div class="form-group">
                    <label for="level">Niveau actuel *</label>
                    <select id="level" name="level" required>
                        <option value="debutant" <?php echo $rider['level'] === 'debutant' ? 'selected' : ''; ?>>Débutant</option>
                        <option value="intermediaire" <?php echo $rider['level'] === 'intermediaire' ? 'selected' : ''; ?>>Intermédiaire</option>
                        <option value="confirme" <?php echo $rider['level'] === 'confirme' ? 'selected' : ''; ?>>Confirmé</option>
                        <option value="competition" <?php echo $rider['level'] === 'competition' ? 'selected' : ''; ?>>Pilote Compétition</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="years_experience">Années d'expérience</label>
                    <input type="number" id="years_experience" name="years_experience" value="<?php echo htmlspecialchars($rider['years_experience']); ?>">
                </div>
                <div class="form-group">
                    <label>Type de roulages pratiqués *</label>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="riding_types[]" value="trackday" <?php echo strpos($rider['riding_types'], 'trackday') !== false ? 'checked' : ''; ?>>
                            Trackday
                        </label>
                        <label>
                            <input type="checkbox" name="riding_types[]" value="fsbk" <?php echo strpos($rider['riding_types'], 'fsbk') !== false ? 'checked' : ''; ?>>
                            Course FSBK
                        </label>
                        <label>
                            <input type="checkbox" name="riding_types[]" value="endurance" <?php echo strpos($rider['riding_types'], 'endurance') !== false ? 'checked' : ''; ?>>
                            Endurance
                        </label>
                        <label>
                            <input type="checkbox" name="riding_types[]" value="loisir" <?php echo strpos($rider['riding_types'], 'loisir') !== false ? 'checked' : ''; ?>>
                            Loisir
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="has_license" <?php echo $rider['has_license'] ? 'checked' : ''; ?>>
                        Possède une licence
                    </label>
                </div>
                <div class="form-group">
                    <label for="license_number">Numéro de licence</label>
                    <input type="text" id="license_number" name="license_number" value="<?php echo htmlspecialchars($rider['license_number']); ?>">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="has_coach" <?php echo $rider['has_coach'] ? 'checked' : ''; ?>>
                        Est coaché
                    </label>
                </div>
            </div>

            <div class="form-section">
                <h2>Comportement / ressentis</h2>
                <div class="form-group">
                    <label for="front_grip_sensitivity">Sensibilité au grip avant</label>
                    <select id="front_grip_sensitivity" name="front_grip_sensitivity">
                        <option value="">Sélectionner</option>
                        <option value="forte" <?php echo $rider['front_grip_sensitivity'] === 'forte' ? 'selected' : ''; ?>>Forte</option>
                        <option value="moyenne" <?php echo $rider['front_grip_sensitivity'] === 'moyenne' ? 'selected' : ''; ?>>Moyenne</option>
                        <option value="faible" <?php echo $rider['front_grip_sensitivity'] === 'faible' ? 'selected' : ''; ?>>Faible</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="rear_grip_sensitivity">Sensibilité au grip arrière</label>
                    <select id="rear_grip_sensitivity" name="rear_grip_sensitivity">
                        <option value="">Sélectionner</option>
                        <option value="forte" <?php echo $rider['rear_grip_sensitivity'] === 'forte' ? 'selected' : ''; ?>>Forte</option>
                        <option value="moyenne" <?php echo $rider['rear_grip_sensitivity'] === 'moyenne' ? 'selected' : ''; ?>>Moyenne</option>
                        <option value="faible" <?php echo $rider['rear_grip_sensitivity'] === 'faible' ? 'selected' : ''; ?>>Faible</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="riding_style">Style de pilotage</label>
                    <select id="riding_style" name="riding_style">
                        <option value="">Sélectionner</option>
                        <option value="freinage_tardif" <?php echo $rider['riding_style'] === 'freinage_tardif' ? 'selected' : ''; ?>>Freinage tardif</option>
                        <option value="sortie_rapide" <?php echo $rider['riding_style'] === 'sortie_rapide' ? 'selected' : ''; ?>>Sortie rapide</option>
                        <option value="neutre" <?php echo $rider['riding_style'] === 'neutre' ? 'selected' : ''; ?>>Neutre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="progression_areas">Zones de progression identifiées</label>
                    <textarea id="progression_areas" name="progression_areas" rows="4"><?php echo htmlspecialchars($rider['progression_areas']); ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Mettre à jour le profil</button>
            </div>
        </form>
    </div>
</body>
</html> 