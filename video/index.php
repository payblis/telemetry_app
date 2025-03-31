<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/auth_functions.php';

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier que l'utilisateur est connecté
requireLogin();

// Connexion à la base de données
$conn = getDBConnection();

// Récupérer les sessions de l'utilisateur
$user_id = $_SESSION['user_id'];
$sql = "SELECT s.id, s.date, s.type, 
        p.nom as pilote_nom, p.prenom as pilote_prenom,
        m.marque as moto_marque, m.modele as moto_modele,
        c.nom as circuit_nom
        FROM sessions s
        JOIN pilotes p ON s.pilote_id = p.id
        JOIN motos m ON s.moto_id = m.id
        JOIN circuits c ON s.circuit_id = c.id
        ORDER BY s.date DESC, s.created_at DESC";

$result = $conn->query($sql);
$sessions = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }
}

// Traitement du formulaire d'upload de vidéo
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_video'])) {
    $session_id = intval($_POST['session_id'] ?? 0);
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // Simuler l'upload de vidéo (en production, utiliser move_uploaded_file)
    $video_path = 'uploads/videos/' . uniqid() . '.mp4';
    $thumbnail_path = 'uploads/thumbnails/' . uniqid() . '.jpg';
    
    // Validation des données
    if (empty($session_id) || empty($title)) {
        $error_message = 'Tous les champs obligatoires doivent être remplis.';
    } else {
        // Insérer la vidéo dans la base de données
        $stmt = $conn->prepare("INSERT INTO videos (session_id, title, description, video_path, thumbnail_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $session_id, $title, $description, $video_path, $thumbnail_path);
        
        if ($stmt->execute()) {
            $success_message = 'Vidéo ajoutée avec succès.';
            
            // Rediriger vers la page de détails de la vidéo
            header("Location: " . url("video/?id=" . $conn->insert_id));
            exit;
        } else {
            $error_message = 'Erreur lors de l\'ajout de la vidéo: ' . $conn->error;
        }
    }
}

// Récupérer les vidéos
$videos = [];
$sql = "SELECT v.*, s.date as session_date, 
        p.nom as pilote_nom, p.prenom as pilote_prenom,
        m.marque as moto_marque, m.modele as moto_modele,
        c.nom as circuit_nom
        FROM videos v
        JOIN sessions s ON v.session_id = s.id
        JOIN pilotes p ON s.pilote_id = p.id
        JOIN motos m ON s.moto_id = m.id
        JOIN circuits c ON s.circuit_id = c.id
        ORDER BY v.created_at DESC";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
}

// Récupérer une vidéo spécifique si un ID est fourni
$video = null;
$chronos = [];
$markers = [];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $video_id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT v.*, s.date as session_date, s.id as session_id,
                           p.nom as pilote_nom, p.prenom as pilote_prenom,
                           m.marque as moto_marque, m.modele as moto_modele,
                           c.nom as circuit_nom
                           FROM videos v
                           JOIN sessions s ON v.session_id = s.id
                           JOIN pilotes p ON s.pilote_id = p.id
                           JOIN motos m ON s.moto_id = m.id
                           JOIN circuits c ON s.circuit_id = c.id
                           WHERE v.id = ?");
    $stmt->bind_param("i", $video_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $video = $result->fetch_assoc();
        
        // Récupérer les chronos de la session
        $stmt = $conn->prepare("SELECT * FROM chronos WHERE session_id = ? ORDER BY tour_numero");
        $stmt->bind_param("i", $video['session_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $chronos[] = $row;
            }
        }
        
        // Récupérer les marqueurs vidéo
        $stmt = $conn->prepare("SELECT * FROM video_markers WHERE video_id = ? ORDER BY timestamp");
        $stmt->bind_param("i", $video_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $markers[] = $row;
            }
        }
    }
}

// Traitement de l'ajout de marqueur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_marker'])) {
    $video_id = intval($_POST['video_id'] ?? 0);
    $timestamp = floatval($_POST['timestamp'] ?? 0);
    $label = $_POST['label'] ?? '';
    $description = $_POST['description'] ?? '';
    $marker_type = $_POST['marker_type'] ?? 'note';
    
    if (empty($video_id) || empty($timestamp) || empty($label)) {
        $error_message = 'Tous les champs obligatoires doivent être remplis.';
    } else {
        $stmt = $conn->prepare("INSERT INTO video_markers (video_id, timestamp, label, description, marker_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idsss", $video_id, $timestamp, $label, $description, $marker_type);
        
        if ($stmt->execute()) {
            $success_message = 'Marqueur ajouté avec succès.';
            
            // Rediriger pour éviter la soumission multiple
            header("Location: " . url("video/?id=$video_id&success=1"));
            exit;
        } else {
            $error_message = 'Erreur lors de l\'ajout du marqueur: ' . $conn->error;
        }
    }
}

// Message de succès après redirection
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = 'Opération réussie.';
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="video-container">
    <?php if ($video): ?>
        <h1 class="video-title"><?php echo htmlspecialchars($video['title']); ?></h1>
        
        <div class="video-details">
            <div class="video-info">
                <div class="video-session">
                    <span class="session-date"><?php echo date('d/m/Y', strtotime($video['session_date'])); ?></span>
                    <span class="session-circuit"><?php echo htmlspecialchars($video['circuit_nom']); ?></span>
                    <span class="session-moto"><?php echo htmlspecialchars($video['moto_marque'] . ' ' . $video['moto_modele']); ?></span>
                </div>
                <div class="video-description">
                    <?php echo nl2br(htmlspecialchars($video['description'])); ?>
                </div>
            </div>
            
            <div class="video-actions">
                <a href="<?php echo url('video/'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>
        </div>
        
        <div class="video-player-container">
            <div class="video-player">
                <video id="videoPlayer" controls poster="<?php echo url($video['thumbnail_path']); ?>">
                    <source src="<?php echo url($video['video_path']); ?>" type="video/mp4">
                    Votre navigateur ne supporte pas la lecture de vidéos.
                </video>
                
                <div class="video-controls">
                    <button id="addMarkerBtn" class="btn btn-primary">
                        <i class="fas fa-map-marker-alt"></i> Ajouter un marqueur
                    </button>
                </div>
            </div>
            
            <div class="video-data">
                <div class="video-tabs">
                    <button class="tab-button active" data-tab="markers">Marqueurs</button>
                    <button class="tab-button" data-tab="chronos">Chronos</button>
                    <button class="tab-button" data-tab="analysis">Analyse</button>
                </div>
                
                <div class="tab-content active" id="markers">
                    <h3>Marqueurs vidéo</h3>
                    
                    <?php if (empty($markers)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Aucun marqueur n'a été ajouté à cette vidéo.
                        </div>
                    <?php else: ?>
                        <div class="markers-list">
                            <?php foreach ($markers as $marker): ?>
                                <div class="marker-item" data-timestamp="<?php echo $marker['timestamp']; ?>">
                                    <div class="marker-time"><?php echo formatTime($marker['timestamp']); ?></div>
                                    <div class="marker-content">
                                        <div class="marker-header">
                                            <h4 class="marker-label"><?php echo htmlspecialchars($marker['label']); ?></h4>
                                            <span class="marker-type <?php echo $marker['marker_type']; ?>"><?php echo getMarkerTypeLabel($marker['marker_type']); ?></span>
                                        </div>
                                        <?php if (!empty($marker['description'])): ?>
                                            <div class="marker-description"><?php echo nl2br(htmlspecialchars($marker['description'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="add-marker-form" id="addMarkerForm" style="display: none;">
                        <h4>Ajouter un marqueur</h4>
                        
                        <form method="POST" action="<?php echo url('video/'); ?>">
                            <input type="hidden" name="add_marker" value="1">
                            <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                            <input type="hidden" id="timestamp" name="timestamp" value="0">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="label">Titre:</label>
                                    <input type="text" id="label" name="label" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="marker_type">Type:</label>
                                    <select id="marker_type" name="marker_type">
                                        <option value="note">Note</option>
                                        <option value="technique">Technique</option>
                                        <option value="probleme">Problème</option>
                                        <option value="amelioration">Amélioration</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description:</label>
                                <textarea id="description" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                <button type="button" class="btn btn-secondary" id="cancelAddMarker">Annuler</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="tab-content" id="chronos">
                    <h3>Chronos de la session</h3>
                    
                    <?php if (empty($chronos)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Aucun chrono n'a été enregistré pour cette session.
                        </div>
                    <?php else: ?>
                        <div class="chronos-chart-container">
                            <canvas id="chronosChart"></canvas>
                        </div>
                        
                        <div class="chronos-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tour</th>
                                        <th>Temps</th>
                                        <th>Écart</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $best_time = min(array_column($chronos, 'temps_secondes'));
                                    foreach ($chronos as $chrono):
                                        $diff = $chrono['temps_secondes'] - $best_time;
                                        $diff_class = $diff == 0 ? 'best' : ($diff < 1 ? 'good' : ($diff < 2 ? 'average' : 'slow'));
                                    ?>
                                        <tr class="<?php echo $diff_class; ?>">
                                            <td><?php echo $chrono['tour_numero']; ?></td>
                                            <td><?php echo $chrono['temps']; ?></td>
                                            <td><?php echo $diff == 0 ? 'BEST' : '+' . number_format($diff, 3); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="tab-content" id="analysis">
                    <h3>Analyse technique</h3>
                    
                    <div class="analysis-content">
                        <p>L'analyse technique vous permet d'identifier les points forts et les axes d'amélioration de votre pilotage en vous basant sur les données vidéo.</p>
                        
                        <div class="analysis-sections">
                            <div class="analysis-section">
                                <h4>Trajectoires</h4>
                                <p>Utilisez les marqueurs pour identifier les points clés de vos trajectoires :</p>
                                <ul>
                                    <li>Point de freinage</li>
                                    <li>Point de corde</li>
                                    <li>Point de sortie</li>
                                </ul>
                                <p>Comparez vos trajectoires d'un tour à l'autre pour identifier les incohérences.</p>
                            </div>
                            
                            <div class="analysis-section">
                                <h4>Position sur la moto</h4>
                                <p>Analysez votre position sur la moto :</p>
                                <ul>
                                    <li>Position du buste</li>
                                    <li>Position de la tête (regard)</li>
                                    <li>Position des coudes</li>
                                    <li>Déhanché dans les virages</li>
                                </ul>
                            </div>
                            
                            <div class="analysis-section">
                                <h4>Technique de pilotage</h4>
                                <p>Évaluez vos techniques :</p>
                                <ul>
                                    <li>Technique de freinage</li>
                                    <li>Passage de vitesses</li>
                                    <li>Accélération en sortie de virage</li>
                                    <li>Stabilité de la moto</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <h1 class="video-title">Module d'Analyse Vidéo</h1>
        
        <div class="video-intro">
            <p>Le module d'analyse vidéo vous permet d'uploader vos vidéos de sessions, de les synchroniser avec vos données de chronométrage et d'ajouter des marqueurs pour analyser votre pilotage.</p>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="video-actions">
            <button class="btn btn-primary" id="showUploadForm">
                <i class="fas fa-upload"></i> Uploader une vidéo
            </button>
        </div>
        
        <div class="upload-form" id="uploadForm" style="display: none;">
            <h3>Uploader une nouvelle vidéo</h3>
            
            <form method="POST" action="<?php echo url('video/'); ?>" enctype="multipart/form-data" class="video-form">
                <input type="hidden" name="upload_video" value="1">
                
                <div class="form-group">
                    <label for="session_id">Session associée:</label>
                    <select id="session_id" name="session_id" required>
                        <option value="">Sélectionner une session</option>
                        <?php foreach ($sessions as $session): ?>
                            <option value="<?php echo $session['id']; ?>">
                                <?php echo date('d/m/Y', strtotime($session['date'])) . ' - ' . $session['circuit_nom'] . ' - ' . $session['moto_marque'] . ' ' . $session['moto_modele']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="title">Titre:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="video_file">Fichier vidéo:</label>
                    <input type="file" id="video_file" name="video_file" accept="video/*" required>
                    <small class="form-text">Formats acceptés: MP4, MOV, AVI. Taille maximale: 500 MB.</small>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Uploader</button>
                    <button type="button" class="btn btn-secondary" id="cancelUpload">Annuler</button>
                </div>
            </form>
        </div>
        
        <div class="videos-list">
            <h3>Mes vidéos</h3>
            
            <?php if (empty($videos)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Vous n'avez pas encore uploadé de vidéos.
                </div>
            <?php else: ?>
                <div class="videos-grid">
                    <?php foreach ($videos as $v): ?>
                        <div class="video-card">
                            <div class="video-thumbnail">
                                <a href="<?php echo url('video/?id=' . $v['id']); ?>">
                                    <img src="<?php echo url($v['thumbnail_path']); ?>" alt="<?php echo htmlspecialchars($v['title']); ?>">
                                    <div class="video-duration">10:23</div>
                                </a>
                            </div>
                            <div class="video-card-content">
                                <h4 class="video-card-title">
                                    <a href="<?php echo url('video/?id=' . $v['id']); ?>"><?php echo htmlspecialchars($v['title']); ?></a>
                                </h4>
                                <div class="video-card-info">
                                    <span class="video-date"><?php echo date('d/m/Y', strtotime($v['session_date'])); ?></span>
                                    <span class="video-circuit"><?php echo htmlspecialchars($v['circuit_nom']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des onglets
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Retirer la classe active de tous les boutons et contenus
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');
            
            // Afficher le contenu correspondant
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Gestion du formulaire d'upload
    const showUploadFormBtn = document.getElementById('showUploadForm');
    const uploadForm = document.getElementById('uploadForm');
    const cancelUploadBtn = document.getElementById('cancelUpload');
    
    if (showUploadFormBtn) {
        showUploadFormBtn.addEventListener('click', function() {
            uploadForm.style.display = 'block';
            this.style.display = 'none';
        });
    }
    
    if (cancelUploadBtn) {
        cancelUploadBtn.addEventListener('click', function() {
            uploadForm.style.display = 'none';
            showUploadFormBtn.style.display = 'block';
        });
    }
    
    // Gestion du lecteur vidéo et des marqueurs
    const videoPlayer = document.getElementById('videoPlayer');
    const addMarkerBtn = document.getElementById('addMarkerBtn');
    const addMarkerForm = document.getElementById('addMarkerForm');
    const cancelAddMarkerBtn = document.getElementById('cancelAddMarker');
    const timestampInput = document.getElementById('timestamp');
    
    if (videoPlayer && addMarkerBtn) {
        addMarkerBtn.addEventListener('click', function() {
            // Mettre en pause la vidéo
            videoPlayer.pause();
            
            // Récupérer le timestamp actuel
            const currentTime = videoPlayer.currentTime;
            timestampInput.value = currentTime;
            
            // Afficher le formulaire
            addMarkerForm.style.display = 'block';
            this.style.display = 'none';
        });
    }
    
    if (cancelAddMarkerBtn) {
        cancelAddMarkerBtn.addEventListener('click', function() {
            addMarkerForm.style.display = 'none';
            addMarkerBtn.style.display = 'block';
        });
    }
    
    // Cliquer sur un marqueur pour naviguer dans la vidéo
    const markerItems = document.querySelectorAll('.marker-item');
    
    markerItems.forEach(marker => {
        marker.addEventListener('click', function() {
            const timestamp = parseFloat(this.getAttribute('data-timestamp'));
            if (videoPlayer) {
                videoPlayer.currentTime = timestamp;
                videoPlayer.play();
            }
        });
    });
    
    // Graphique des chronos
    const chronosChart = document.getElementById('chronosChart');
    
    if (chronosChart) {
        const chronosData = <?php echo json_encode(array_map(function($chrono) { return $chrono['temps_secondes']; }, $chronos)); ?>;
        const labels = <?php echo json_encode(array_map(function($chrono) { return 'Tour ' . $chrono['tour_numero']; }, $chronos)); ?>;
        
        new Chart(chronosChart, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Temps au tour',
                    data: chronosData,
                    borderColor: '#00a8ff',
                    backgroundColor: 'rgba(0, 168, 255, 0.1)',
                    tension: 0.4,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#e0e0e0'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const minutes = Math.floor(value / 60);
                                const seconds = (value % 60).toFixed(3);
                                return context.dataset.label + ': ' + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#e0e0e0'
                        }
                    },
                    y: {
                        reverse: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#e0e0e0',
                            callback: function(value) {
                                const minutes = Math.floor(value / 60);
                                const seconds = (value % 60).toFixed(1);
                                return minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

<style>
.video-container {
    padding: 1rem 0;
}

.video-title {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 0.5rem;
}

.video-intro {
    margin-bottom: 2rem;
    font-size: 1.1rem;
    line-height: 1.6;
}

.video-actions {
    margin-bottom: 2rem;
    text-align: right;
}

.upload-form {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--light-gray);
}

.upload-form h3 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.video-form .form-group {
    margin-bottom: 1.5rem;
}

.video-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
}

.video-form input[type="text"],
.video-form input[type="file"],
.video-form select,
.video-form textarea {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid var(--light-gray);
    border-radius: var(--border-radius);
    background-color: rgba(255, 255, 255, 0.05);
    color: var(--text-color);
}

.video-form textarea {
    resize: vertical;
}

.form-text {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: var(--dark-gray);
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.videos-list {
    margin-top: 2rem;
}

.videos-list h3 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.videos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.video-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
    border: 1px solid var(--light-gray);
    transition: transform 0.3s, border-color 0.3s;
}

.video-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary-color);
}

.video-thumbnail {
    position: relative;
    width: 100%;
    height: 0;
    padding-bottom: 56.25%; /* 16:9 aspect ratio */
    overflow: hidden;
}

.video-thumbnail img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-duration {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background-color: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
}

.video-card-content {
    padding: 1rem;
}

.video-card-title {
    margin: 0 0 0.5rem;
    font-size: 1.1rem;
}

.video-card-title a {
    color: var(--text-color);
    text-decoration: none;
}

.video-card-title a:hover {
    color: var(--primary-color);
}

.video-card-info {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: var(--dark-gray);
}

.video-details {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
}

.video-info {
    flex: 1;
}

.video-session {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.video-description {
    line-height: 1.6;
}

.video-player-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

@media (min-width: 992px) {
    .video-player-container {
        grid-template-columns: 2fr 1fr;
    }
}

.video-player {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
}

.video-player video {
    width: 100%;
    border-radius: var(--border-radius);
    margin-bottom: 1rem;
}

.video-controls {
    display: flex;
    justify-content: center;
}

.video-data {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    border: 1px solid var(--light-gray);
    overflow: hidden;
}

.video-tabs {
    display: flex;
    border-bottom: 1px solid var(--light-gray);
}

.tab-button {
    flex: 1;
    padding: 1rem;
    background-color: transparent;
    border: none;
    color: var(--text-color);
    cursor: pointer;
    transition: all 0.3s;
    font-weight: bold;
    text-align: center;
}

.tab-button:hover {
    background-color: rgba(0, 168, 255, 0.1);
}

.tab-button.active {
    background-color: var(--primary-color);
    color: #000;
}

.tab-content {
    display: none;
    padding: 1.5rem;
    max-height: 500px;
    overflow-y: auto;
}

.tab-content.active {
    display: block;
}

.tab-content h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.markers-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.marker-item {
    display: flex;
    gap: 1rem;
    background-color: rgba(255, 255, 255, 0.05);
    border-radius: var(--border-radius);
    padding: 1rem;
    border: 1px solid var(--light-gray);
    cursor: pointer;
    transition: background-color 0.3s;
}

.marker-item:hover {
    background-color: rgba(0, 168, 255, 0.1);
}

.marker-time {
    min-width: 60px;
    font-weight: bold;
    color: var(--primary-color);
}

.marker-content {
    flex: 1;
}

.marker-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.marker-label {
    margin: 0;
}

.marker-type {
    padding: 0.2rem 0.5rem;
    border-radius: 20px;
    font-size: 0.8rem;
}

.marker-type.note {
    background-color: var(--primary-color);
    color: #000;
}

.marker-type.technique {
    background-color: var(--success-color);
    color: #000;
}

.marker-type.probleme {
    background-color: var(--danger-color);
    color: white;
}

.marker-type.amelioration {
    background-color: var(--warning-color);
    color: #000;
}

.marker-description {
    font-size: 0.9rem;
    line-height: 1.6;
}

.add-marker-form {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--light-gray);
}

.add-marker-form h4 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.chronos-chart-container {
    height: 200px;
    margin-bottom: 1.5rem;
}

.chronos-table {
    overflow-x: auto;
}

.chronos-table table {
    width: 100%;
    border-collapse: collapse;
}

.chronos-table th, .chronos-table td {
    padding: 0.8rem;
    text-align: left;
    border-bottom: 1px solid var(--light-gray);
}

.chronos-table th {
    background-color: rgba(58, 66, 85, 0.5);
    font-weight: bold;
    color: var(--primary-color);
}

.chronos-table tr:hover {
    background-color: rgba(255, 255, 255, 0.05);
}

.chronos-table tr.best {
    background-color: rgba(0, 168, 255, 0.2);
}

.chronos-table tr.good {
    background-color: rgba(0, 200, 83, 0.1);
}

.chronos-table tr.average {
    background-color: rgba(255, 193, 7, 0.1);
}

.chronos-table tr.slow {
    background-color: rgba(255, 62, 62, 0.1);
}

.analysis-content {
    line-height: 1.6;
}

.analysis-sections {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin-top: 1.5rem;
}

@media (min-width: 768px) {
    .analysis-sections {
        grid-template-columns: repeat(3, 1fr);
    }
}

.analysis-section {
    background-color: rgba(255, 255, 255, 0.05);
    border-radius: var(--border-radius);
    padding: 1rem;
    border: 1px solid var(--light-gray);
}

.analysis-section h4 {
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.analysis-section ul {
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}

.analysis-section li {
    margin-bottom: 0.25rem;
}
</style>

<?php
// Fonction pour formater le temps en minutes:secondes
function formatTime($seconds) {
    $minutes = floor($seconds / 60);
    $secs = $seconds % 60;
    return sprintf('%d:%02.2f', $minutes, $secs);
}

// Fonction pour obtenir le libellé du type de marqueur
function getMarkerTypeLabel($type) {
    $types = [
        'note' => 'Note',
        'technique' => 'Technique',
        'probleme' => 'Problème',
        'amelioration' => 'Amélioration'
    ];
    
    return $types[$type] ?? 'Note';
}

// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
