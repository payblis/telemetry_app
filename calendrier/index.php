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

// Récupérer les événements à venir
$sql = "SELECT e.*, c.nom as circuit_nom, c.pays as circuit_pays 
        FROM evenements e
        JOIN circuits c ON e.circuit_id = c.id
        WHERE e.date >= CURDATE()
        ORDER BY e.date ASC
        LIMIT 10";

$result = $conn->query($sql);
$evenements = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $evenements[] = $row;
    }
}

// Récupérer les circuits pour le formulaire
$sql = "SELECT id, nom, pays FROM circuits ORDER BY nom";
$result = $conn->query($sql);
$circuits = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $circuits[] = $row;
    }
}

// Traitement du formulaire d'ajout d'événement
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $titre = $_POST['titre'] ?? '';
    $circuit_id = intval($_POST['circuit_id'] ?? 0);
    $date = $_POST['date'] ?? '';
    $heure_debut = $_POST['heure_debut'] ?? '';
    $heure_fin = $_POST['heure_fin'] ?? '';
    $type = $_POST['type'] ?? '';
    $description = $_POST['description'] ?? '';
    $prix = floatval($_POST['prix'] ?? 0);
    $places_disponibles = intval($_POST['places_disponibles'] ?? 0);
    $organisateur = $_POST['organisateur'] ?? '';
    $contact = $_POST['contact'] ?? '';
    
    // Validation des données
    if (empty($titre) || empty($circuit_id) || empty($date) || empty($type)) {
        $error_message = 'Tous les champs obligatoires doivent être remplis.';
    } else {
        // Insérer l'événement dans la base de données
        $stmt = $conn->prepare("INSERT INTO evenements (titre, circuit_id, date, heure_debut, heure_fin, type, description, prix, places_disponibles, organisateur, contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisssssdiss", $titre, $circuit_id, $date, $heure_debut, $heure_fin, $type, $description, $prix, $places_disponibles, $organisateur, $contact);
        
        if ($stmt->execute()) {
            $success_message = 'Événement ajouté avec succès.';
            
            // Rediriger pour éviter la soumission multiple du formulaire
            header("Location: " . url("calendrier/?success=1"));
            exit;
        } else {
            $error_message = 'Erreur lors de l\'ajout de l\'événement: ' . $conn->error;
        }
    }
}

// Traitement de l'inscription à un événement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_event'])) {
    $event_id = intval($_POST['event_id'] ?? 0);
    $user_id = $_SESSION['user_id'];
    $moto_id = intval($_POST['moto_id'] ?? 0);
    $commentaire = $_POST['commentaire'] ?? '';
    
    // Vérifier si l'utilisateur est déjà inscrit
    $stmt = $conn->prepare("SELECT id FROM inscriptions_evenements WHERE evenement_id = ? AND utilisateur_id = ?");
    $stmt->bind_param("ii", $event_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error_message = 'Vous êtes déjà inscrit à cet événement.';
    } else {
        // Vérifier s'il reste des places disponibles
        $stmt = $conn->prepare("SELECT places_disponibles FROM evenements WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $event = $result->fetch_assoc();
        
        if ($event['places_disponibles'] <= 0) {
            $error_message = 'Il n\'y a plus de places disponibles pour cet événement.';
        } else {
            // Insérer l'inscription
            $stmt = $conn->prepare("INSERT INTO inscriptions_evenements (evenement_id, utilisateur_id, moto_id, commentaire, date_inscription) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiis", $event_id, $user_id, $moto_id, $commentaire);
            
            if ($stmt->execute()) {
                // Mettre à jour le nombre de places disponibles
                $stmt = $conn->prepare("UPDATE evenements SET places_disponibles = places_disponibles - 1 WHERE id = ?");
                $stmt->bind_param("i", $event_id);
                $stmt->execute();
                
                $success_message = 'Inscription réussie.';
                
                // Rediriger pour éviter la soumission multiple du formulaire
                header("Location: " . url("calendrier/?success=2"));
                exit;
            } else {
                $error_message = 'Erreur lors de l\'inscription: ' . $conn->error;
            }
        }
    }
}

// Message de succès après redirection
if (isset($_GET['success'])) {
    if ($_GET['success'] == 1) {
        $success_message = 'Événement ajouté avec succès.';
    } elseif ($_GET['success'] == 2) {
        $success_message = 'Inscription réussie.';
    }
}

// Récupérer les motos de l'utilisateur pour le formulaire d'inscription
$user_id = $_SESSION['user_id'];
$sql = "SELECT id, marque, modele FROM motos WHERE utilisateur_id = $user_id ORDER BY marque, modele";
$result = $conn->query($sql);
$motos = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $motos[] = $row;
    }
}

// Récupérer les événements auxquels l'utilisateur est inscrit
$sql = "SELECT e.*, c.nom as circuit_nom, c.pays as circuit_pays, ie.date_inscription
        FROM inscriptions_evenements ie
        JOIN evenements e ON ie.evenement_id = e.id
        JOIN circuits c ON e.circuit_id = c.id
        WHERE ie.utilisateur_id = $user_id
        ORDER BY e.date ASC";

$result = $conn->query($sql);
$mes_evenements = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mes_evenements[] = $row;
    }
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="calendrier-container">
    <h1 class="calendrier-title">Planificateur de Journées Circuit</h1>
    
    <div class="calendrier-intro">
        <p>Planifiez vos journées circuit, inscrivez-vous à des événements et organisez vos sessions de pilotage. Restez informé des événements à venir et préparez vos réglages en fonction des circuits.</p>
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
    
    <div class="calendrier-tabs">
        <button class="tab-button active" data-tab="upcoming">Événements à venir</button>
        <button class="tab-button" data-tab="my-events">Mes événements</button>
        <button class="tab-button" data-tab="add-event">Ajouter un événement</button>
    </div>
    
    <div class="tab-content active" id="upcoming">
        <h2>Événements à venir</h2>
        
        <?php if (empty($evenements)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Aucun événement à venir n'est programmé.
            </div>
        <?php else: ?>
            <div class="events-list">
                <?php foreach ($evenements as $event): ?>
                    <div class="event-card">
                        <div class="event-date">
                            <div class="date-day"><?php echo date('d', strtotime($event['date'])); ?></div>
                            <div class="date-month"><?php echo date('M', strtotime($event['date'])); ?></div>
                            <div class="date-year"><?php echo date('Y', strtotime($event['date'])); ?></div>
                        </div>
                        
                        <div class="event-details">
                            <h3 class="event-title"><?php echo htmlspecialchars($event['titre']); ?></h3>
                            
                            <div class="event-info">
                                <div class="event-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($event['circuit_nom'] . ($event['circuit_pays'] ? ', ' . $event['circuit_pays'] : '')); ?>
                                </div>
                                
                                <div class="event-time">
                                    <i class="fas fa-clock"></i>
                                    <?php 
                                    if ($event['heure_debut'] && $event['heure_fin']) {
                                        echo substr($event['heure_debut'], 0, 5) . ' - ' . substr($event['heure_fin'], 0, 5);
                                    } elseif ($event['heure_debut']) {
                                        echo 'À partir de ' . substr($event['heure_debut'], 0, 5);
                                    } else {
                                        echo 'Toute la journée';
                                    }
                                    ?>
                                </div>
                                
                                <div class="event-type">
                                    <i class="fas fa-tag"></i>
                                    <?php echo getEventTypeLabel($event['type']); ?>
                                </div>
                                
                                <?php if ($event['prix'] > 0): ?>
                                    <div class="event-price">
                                        <i class="fas fa-euro-sign"></i>
                                        <?php echo number_format($event['prix'], 2, ',', ' '); ?> €
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($event['places_disponibles'] !== null): ?>
                                    <div class="event-availability <?php echo $event['places_disponibles'] <= 5 ? 'low' : ''; ?>">
                                        <i class="fas fa-users"></i>
                                        <?php 
                                        if ($event['places_disponibles'] > 0) {
                                            echo $event['places_disponibles'] . ' place' . ($event['places_disponibles'] > 1 ? 's' : '') . ' disponible' . ($event['places_disponibles'] > 1 ? 's' : '');
                                        } else {
                                            echo 'Complet';
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($event['description'])): ?>
                                <div class="event-description">
                                    <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($event['organisateur']) || !empty($event['contact'])): ?>
                                <div class="event-organizer">
                                    <?php if (!empty($event['organisateur'])): ?>
                                        <div><strong>Organisateur:</strong> <?php echo htmlspecialchars($event['organisateur']); ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($event['contact'])): ?>
                                        <div><strong>Contact:</strong> <?php echo htmlspecialchars($event['contact']); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="event-actions">
                            <?php if ($event['places_disponibles'] > 0): ?>
                                <button class="btn btn-primary register-btn" data-event-id="<?php echo $event['id']; ?>" data-event-title="<?php echo htmlspecialchars($event['titre']); ?>">
                                    <i class="fas fa-check-circle"></i> S'inscrire
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>
                                    <i class="fas fa-ban"></i> Complet
                                </button>
                            <?php endif; ?>
                            
                            <a href="<?php echo url('sessions/prepare.php?circuit_id=' . $event['circuit_id'] . '&date=' . $event['date']); ?>" class="btn btn-outline">
                                <i class="fas fa-cog"></i> Préparer mes réglages
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="tab-content" id="my-events">
        <h2>Mes événements</h2>
        
        <?php if (empty($mes_evenements)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Vous n'êtes inscrit à aucun événement.
            </div>
        <?php else: ?>
            <div class="events-list">
                <?php foreach ($mes_evenements as $event): ?>
                    <div class="event-card">
                        <div class="event-date">
                            <div class="date-day"><?php echo date('d', strtotime($event['date'])); ?></div>
                            <div class="date-month"><?php echo date('M', strtotime($event['date'])); ?></div>
                            <div class="date-year"><?php echo date('Y', strtotime($event['date'])); ?></div>
                        </div>
                        
                        <div class="event-details">
                            <h3 class="event-title"><?php echo htmlspecialchars($event['titre']); ?></h3>
                            
                            <div class="event-info">
                                <div class="event-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($event['circuit_nom'] . ($event['circuit_pays'] ? ', ' . $event['circuit_pays'] : '')); ?>
                                </div>
                                
                                <div class="event-time">
                                    <i class="fas fa-clock"></i>
                                    <?php 
                                    if ($event['heure_debut'] && $event['heure_fin']) {
                                        echo substr($event['heure_debut'], 0, 5) . ' - ' . substr($event['heure_fin'], 0, 5);
                                    } elseif ($event['heure_debut']) {
                                        echo 'À partir de ' . substr($event['heure_debut'], 0, 5);
                                    } else {
                                        echo 'Toute la journée';
                                    }
                                    ?>
                                </div>
                                
                                <div class="event-type">
                                    <i class="fas fa-tag"></i>
                                    <?php echo getEventTypeLabel($event['type']); ?>
                                </div>
                                
                                <div class="event-registration">
                                    <i class="fas fa-calendar-check"></i>
                                    Inscrit le <?php echo date('d/m/Y', strtotime($event['date_inscription'])); ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($event['description'])): ?>
                                <div class="event-description">
                                    <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($event['organisateur']) || !empty($event['contact'])): ?>
                                <div class="event-organizer">
                                    <?php if (!empty($event['organisateur'])): ?>
                                        <div><strong>Organisateur:</strong> <?php echo htmlspecialchars($event['organisateur']); ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($event['contact'])): ?>
                                        <div><strong>Contact:</strong> <?php echo htmlspecialchars($event['contact']); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="event-actions">
                            <a href="<?php echo url('sessions/prepare.php?circuit_id=' . $event['circuit_id'] . '&date=' . $event['date']); ?>" class="btn btn-primary">
                                <i class="fas fa-cog"></i> Préparer mes réglages
                            </a>
                            
                            <a href="<?php echo url('meteo/?location=' . urlencode($event['circuit_nom'])); ?>" class="btn btn-outline">
                                <i class="fas fa-cloud-sun"></i> Vérifier la météo
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="tab-content" id="add-event">
        <h2>Ajouter un événement</h2>
        
        <form method="POST" action="<?php echo url('calendrier/'); ?>" class="event-form">
            <input type="hidden" name="add_event" value="1">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="titre">Titre de l'événement:</label>
                    <input type="text" id="titre" name="titre" required>
                </div>
                
                <div class="form-group">
                    <label for="circuit_id">Circuit:</label>
                    <select id="circuit_id" name="circuit_id" required>
                        <option value="">Sélectionner un circuit</option>
                        <?php foreach ($circuits as $circuit): ?>
                            <option value="<?php echo $circuit['id']; ?>">
                                <?php echo htmlspecialchars($circuit['nom'] . ($circuit['pays'] ? ', ' . $circuit['pays'] : '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required>
                </div>
                
                <div class="form-group">
                    <label for="type">Type d'événement:</label>
                    <select id="type" name="type" required>
                        <option value="">Sélectionner un type</option>
                        <option value="trackday">Trackday</option>
                        <option value="course">Course</option>
                        <option value="entrainement">Entraînement</option>
                        <option value="stage">Stage de pilotage</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="heure_debut">Heure de début:</label>
                    <input type="time" id="heure_debut" name="heure_debut">
                </div>
                
                <div class="form-group">
                    <label for="heure_fin">Heure de fin:</label>
                    <input type="time" id="heure_fin" name="heure_fin">
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="prix">Prix (€):</label>
                    <input type="number" id="prix" name="prix" min="0" step="0.01">
                </div>
                
                <div class="form-group">
                    <label for="places_disponibles">Places disponibles:</label>
                    <input type="number" id="places_disponibles" name="places_disponibles" min="0" step="1">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="organisateur">Organisateur:</label>
                    <input type="text" id="organisateur" name="organisateur">
                </div>
                
                <div class="form-group">
                    <label for="contact">Contact:</label>
                    <input type="text" id="contact" name="contact">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Ajouter l'événement</button>
            </div>
        </form>
    </div>
    
    <!-- Formulaire d'inscription modal -->
    <div class="modal" id="registerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>S'inscrire à l'événement</h3>
                <button class="close-modal">&times;</button>
            </div>
            
            <div class="modal-body">
                <form method="POST" action="<?php echo url('calendrier/'); ?>" id="registerForm">
                    <input type="hidden" name="register_event" value="1">
                    <input type="hidden" name="event_id" id="event_id" value="">
                    
                    <div class="form-group">
                        <label for="moto_id">Moto:</label>
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
                        <label for="commentaire">Commentaire:</label>
                        <textarea id="commentaire" name="commentaire" rows="3"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Confirmer l'inscription</button>
                        <button type="button" class="btn btn-secondary close-modal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
    
    // Gestion du modal d'inscription
    const modal = document.getElementById('registerModal');
    const registerBtns = document.querySelectorAll('.register-btn');
    const closeBtns = document.querySelectorAll('.close-modal');
    const eventIdInput = document.getElementById('event_id');
    
    registerBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const eventId = this.getAttribute('data-event-id');
            const eventTitle = this.getAttribute('data-event-title');
            
            eventIdInput.value = eventId;
            modal.querySelector('.modal-header h3').textContent = 'S\'inscrire à ' + eventTitle;
            
            modal.style.display = 'flex';
        });
    });
    
    closeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    });
    
    // Fermer le modal en cliquant en dehors
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>

<style>
.calendrier-container {
    padding: 1rem 0;
}

.calendrier-title {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 0.5rem;
}

.calendrier-intro {
    margin-bottom: 2rem;
    font-size: 1.1rem;
    line-height: 1.6;
}

.calendrier-tabs {
    display: flex;
    margin-bottom: 2rem;
    border-bottom: 1px solid var(--light-gray);
}

.tab-button {
    padding: 1rem 1.5rem;
    background-color: transparent;
    border: none;
    color: var(--text-color);
    cursor: pointer;
    transition: all 0.3s;
    font-weight: bold;
    position: relative;
}

.tab-button:hover {
    color: var(--primary-color);
}

.tab-button.active {
    color: var(--primary-color);
}

.tab-button.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    height: 3px;
    background-color: var(--primary-color);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.tab-content h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.events-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.event-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
    display: flex;
    gap: 1.5rem;
}

.event-date {
    min-width: 80px;
    text-align: center;
    background-color: var(--primary-color);
    color: #000;
    border-radius: var(--border-radius);
    padding: 0.5rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-self: flex-start;
}

.date-day {
    font-size: 1.8rem;
    font-weight: bold;
    line-height: 1;
}

.date-month {
    font-size: 1rem;
    text-transform: uppercase;
}

.date-year {
    font-size: 0.9rem;
}

.event-details {
    flex: 1;
}

.event-title {
    color: var(--text-color);
    margin-bottom: 1rem;
}

.event-info {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.event-location, .event-time, .event-type, .event-price, .event-availability, .event-registration {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.event-info i {
    color: var(--primary-color);
    width: 20px;
    text-align: center;
}

.event-availability.low {
    color: var(--danger-color);
}

.event-description {
    margin-bottom: 1rem;
    line-height: 1.6;
    font-size: 0.95rem;
}

.event-organizer {
    font-size: 0.9rem;
    color: var(--dark-gray);
    margin-bottom: 1rem;
}

.event-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    justify-content: center;
}

.btn {
    padding: 0.8rem 1.2rem;
    border-radius: var(--border-radius);
    border: none;
    cursor: pointer;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s;
}

.btn-primary {
    background-color: var(--primary-color);
    color: #000;
}

.btn-primary:hover {
    background-color: #0095e0;
}

.btn-secondary {
    background-color: var(--dark-gray);
    color: var(--text-color);
}

.btn-secondary:hover {
    background-color: var(--light-gray);
}

.btn-outline {
    background-color: transparent;
    border: 1px solid var(--primary-color);
    color: var(--primary-color);
}

.btn-outline:hover {
    background-color: rgba(0, 168, 255, 0.1);
}

.btn[disabled] {
    opacity: 0.6;
    cursor: not-allowed;
}

.event-form {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
    margin-bottom: 2rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

@media (min-width: 768px) {
    .form-row {
        grid-template-columns: 1fr 1fr;
    }
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
}

.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid var(--light-gray);
    border-radius: var(--border-radius);
    background-color: rgba(255, 255, 255, 0.05);
    color: var(--text-color);
}

.form-group textarea {
    resize: vertical;
}

.form-actions {
    margin-top: 1.5rem;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    animation: modalFadeIn 0.3s;
}

@keyframes modalFadeIn {
    from { opacity: 0; transform: translateY(-50px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: var(--primary-color);
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--text-color);
    cursor: pointer;
}

.modal-body {
    padding: 1.5rem;
}
</style>

<?php
// Fonction pour obtenir le libellé du type d'événement
function getEventTypeLabel($type) {
    $types = [
        'trackday' => 'Trackday',
        'course' => 'Course',
        'entrainement' => 'Entraînement',
        'stage' => 'Stage de pilotage',
        'autre' => 'Autre'
    ];
    
    return $types[$type] ?? 'Événement';
}

// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
