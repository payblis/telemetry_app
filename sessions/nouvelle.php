<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/session_handler.php';

// Vérifier l'authentification
if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Token de sécurité invalide';
    } else {
        $data = [
            'circuit_id' => $_POST['circuit_id'],
            'pilote_id' => $_POST['pilote_id'],
            'moto_id' => $_POST['moto_id'],
            'date_session' => $_POST['date_session'],
            'conditions_meteo' => $_POST['conditions_meteo'],
            'temperature' => $_POST['temperature'],
            'humidite' => $_POST['humidite'],
            'reglages' => [
                'precharge_avant' => $_POST['precharge_avant'],
                'precharge_arriere' => $_POST['precharge_arriere'],
                'compression_avant' => $_POST['compression_avant'],
                'compression_arriere' => $_POST['compression_arriere'],
                'detente_avant' => $_POST['detente_avant'],
                'detente_arriere' => $_POST['detente_arriere'],
                'pression_avant' => $_POST['pression_avant'],
                'pression_arriere' => $_POST['pression_arriere']
            ]
        ];
        
        $result = $sessionHandler->createSession($data);
        
        if ($result['success']) {
            header('Location: details.php?id=' . $result['session_id']);
            exit();
        } else {
            $error = $result['error'];
        }
    }
}

// Récupérer les listes pour les select
try {
    // Circuits
    $circuits = $pdo->query("SELECT id, nom FROM circuits ORDER BY nom")->fetchAll();
    
    // Pilotes
    $pilotes = $pdo->query("SELECT id, nom FROM pilotes ORDER BY nom")->fetchAll();
    
    // Motos
    $motos = $pdo->query("SELECT id, marque, modele FROM motos ORDER BY marque, modele")->fetchAll();
} catch (PDOException $e) {
    $error = 'Erreur lors de la récupération des données';
    logCustomError('Erreur lors de la récupération des données pour nouvelle session', ['error' => $e->getMessage()]);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Session - TéléMoto AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <div class="logo">TéléMoto AI</div>
                <ul>
                    <li><a href="../index.php">Tableau de bord</a></li>
                    <li><a href="../sessions.php">Sessions</a></li>
                    <li><a href="../logout.php">Déconnexion</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <h1>Nouvelle Session</h1>
            
            <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="nouvelle.php" class="form-grid">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-section">
                    <h2>Informations générales</h2>
                    
                    <div class="form-group">
                        <label for="circuit_id">Circuit</label>
                        <select id="circuit_id" name="circuit_id" required>
                            <option value="">Sélectionner un circuit</option>
                            <?php foreach ($circuits as $circuit): ?>
                            <option value="<?php echo $circuit['id']; ?>">
                                <?php echo htmlspecialchars($circuit['nom']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="pilote_id">Pilote</label>
                        <select id="pilote_id" name="pilote_id" required>
                            <option value="">Sélectionner un pilote</option>
                            <?php foreach ($pilotes as $pilote): ?>
                            <option value="<?php echo $pilote['id']; ?>">
                                <?php echo htmlspecialchars($pilote['nom']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="moto_id">Moto</label>
                        <select id="moto_id" name="moto_id" required>
                            <option value="">Sélectionner une moto</option>
                            <?php foreach ($motos as $moto): ?>
                            <option value="<?php echo $moto['id']; ?>">
                                <?php echo htmlspecialchars($moto['marque'] . ' ' . $moto['modele']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date_session">Date et heure</label>
                        <input type="datetime-local" id="date_session" name="date_session" required>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Conditions</h2>
                    
                    <div class="form-group">
                        <label for="conditions_meteo">Conditions météo</label>
                        <select id="conditions_meteo" name="conditions_meteo" required>
                            <option value="sec">Sec</option>
                            <option value="humide">Humide</option>
                            <option value="mouille">Mouillé</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="temperature">Température (°C)</label>
                        <input type="number" id="temperature" name="temperature" required min="0" max="50">
                    </div>

                    <div class="form-group">
                        <label for="humidite">Humidité (%)</label>
                        <input type="number" id="humidite" name="humidite" required min="0" max="100">
                    </div>
                </div>

                <div class="form-section">
                    <h2>Réglages initiaux</h2>
                    
                    <div class="form-group">
                        <label for="precharge_avant">Précharge avant (mm)</label>
                        <input type="number" id="precharge_avant" name="precharge_avant" required step="0.5">
                    </div>

                    <div class="form-group">
                        <label for="precharge_arriere">Précharge arrière (mm)</label>
                        <input type="number" id="precharge_arriere" name="precharge_arriere" required step="0.5">
                    </div>

                    <div class="form-group">
                        <label for="compression_avant">Compression avant (clicks)</label>
                        <input type="number" id="compression_avant" name="compression_avant" required>
                    </div>

                    <div class="form-group">
                        <label for="compression_arriere">Compression arrière (clicks)</label>
                        <input type="number" id="compression_arriere" name="compression_arriere" required>
                    </div>

                    <div class="form-group">
                        <label for="detente_avant">Détente avant (clicks)</label>
                        <input type="number" id="detente_avant" name="detente_avant" required>
                    </div>

                    <div class="form-group">
                        <label for="detente_arriere">Détente arrière (clicks)</label>
                        <input type="number" id="detente_arriere" name="detente_arriere" required>
                    </div>

                    <div class="form-group">
                        <label for="pression_avant">Pression pneu avant (bar)</label>
                        <input type="number" id="pression_avant" name="pression_avant" required step="0.1">
                    </div>

                    <div class="form-group">
                        <label for="pression_arriere">Pression pneu arrière (bar)</label>
                        <input type="number" id="pression_arriere" name="pression_arriere" required step="0.1">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="button">Créer la session</button>
                    <a href="../index.php" class="button button-secondary">Annuler</a>
                </div>
            </form>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> TéléMoto AI - Tous droits réservés</p>
        </footer>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pré-remplir la date et l'heure actuelles
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('date_session').value = now.toISOString().slice(0, 16);
            
            // Validation du formulaire
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                if (!validateForm(form)) {
                    e.preventDefault();
                    showNotification('Veuillez remplir tous les champs requis', 'error');
                }
            });
        });
    </script>
</body>
</html> 