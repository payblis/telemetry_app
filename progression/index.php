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

// Récupérer les badges de l'utilisateur
$user_id = $_SESSION['user_id'];
$sql = "SELECT b.*, ub.date_obtention 
        FROM badges b
        JOIN utilisateurs_badges ub ON b.id = ub.badge_id
        WHERE ub.utilisateur_id = ?
        ORDER BY ub.date_obtention DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$badges_utilisateur = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $badges_utilisateur[] = $row;
    }
}

// Récupérer tous les badges disponibles
$sql = "SELECT * FROM badges ORDER BY categorie, niveau";
$result = $conn->query($sql);

$tous_badges = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tous_badges[] = $row;
    }
}

// Récupérer les statistiques de l'utilisateur
$sql = "SELECT 
            COUNT(DISTINCT s.id) as total_sessions,
            COUNT(DISTINCT c.id) as total_circuits,
            COUNT(DISTINCT m.id) as total_motos,
            SUM(s.tours_effectues) as total_tours,
            MAX(s.meilleur_chrono) as meilleur_chrono
        FROM sessions s
        LEFT JOIN circuits c ON s.circuit_id = c.id
        LEFT JOIN motos m ON s.moto_id = m.id
        WHERE s.pilote_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();

// Récupérer le niveau et l'expérience de l'utilisateur
$sql = "SELECT niveau, experience, experience_totale FROM utilisateurs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_level = $result->fetch_assoc();

// Calculer l'expérience nécessaire pour le niveau suivant
$niveau_actuel = $user_level['niveau'] ?? 1;
$experience_actuelle = $user_level['experience'] ?? 0;
$experience_totale = $user_level['experience_totale'] ?? 0;
$experience_niveau_suivant = 1000 * pow(1.5, $niveau_actuel - 1);
$pourcentage_progression = ($experience_actuelle / $experience_niveau_suivant) * 100;

// Récupérer les dernières activités de l'utilisateur
$sql = "SELECT 'session' as type, s.id, s.date, s.type as sous_type, c.nom as circuit_nom, NULL as badge_nom, NULL as badge_description
        FROM sessions s
        JOIN circuits c ON s.circuit_id = c.id
        WHERE s.pilote_id = ?
        UNION
        SELECT 'badge' as type, ub.badge_id as id, ub.date_obtention as date, NULL as sous_type, NULL as circuit_nom, b.nom as badge_nom, b.description as badge_description
        FROM utilisateurs_badges ub
        JOIN badges b ON ub.badge_id = b.id
        WHERE ub.utilisateur_id = ?
        ORDER BY date DESC
        LIMIT 10";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$activites = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $activites[] = $row;
    }
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="progression-container">
    <h1 class="progression-title">Système de Badges et Progression</h1>
    
    <div class="progression-intro">
        <p>Suivez votre progression, gagnez des badges et montez en niveau en participant à des sessions, en améliorant vos chronos et en contribuant à la communauté.</p>
    </div>
    
    <div class="progression-dashboard">
        <div class="user-level-card">
            <div class="level-header">
                <div class="level-badge">
                    <span class="level-number"><?php echo $niveau_actuel; ?></span>
                </div>
                <div class="level-info">
                    <h2>Niveau <?php echo $niveau_actuel; ?></h2>
                    <div class="level-title"><?php echo getNiveauTitre($niveau_actuel); ?></div>
                </div>
            </div>
            
            <div class="level-progress">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $pourcentage_progression; ?>%"></div>
                </div>
                <div class="progress-text">
                    <?php echo number_format($experience_actuelle, 0, ',', ' '); ?> / <?php echo number_format($experience_niveau_suivant, 0, ',', ' '); ?> XP
                </div>
            </div>
            
            <div class="level-stats">
                <div class="stat-item">
                    <div class="stat-value"><?php echo number_format($experience_totale, 0, ',', ' '); ?></div>
                    <div class="stat-label">XP Totale</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-value"><?php echo count($badges_utilisateur); ?></div>
                    <div class="stat-label">Badges</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-value"><?php echo $stats['total_sessions'] ?? 0; ?></div>
                    <div class="stat-label">Sessions</div>
                </div>
            </div>
        </div>
        
        <div class="stats-card">
            <h2>Statistiques</h2>
            
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $stats['total_sessions'] ?? 0; ?></div>
                        <div class="stat-label">Sessions</div>
                    </div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-road"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $stats['total_circuits'] ?? 0; ?></div>
                        <div class="stat-label">Circuits</div>
                    </div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-motorcycle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $stats['total_motos'] ?? 0; ?></div>
                        <div class="stat-label">Motos</div>
                    </div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-circle-notch"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $stats['total_tours'] ?? 0; ?></div>
                        <div class="stat-label">Tours</div>
                    </div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-stopwatch"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $stats['meilleur_chrono'] ?? '-'; ?></div>
                        <div class="stat-label">Meilleur Chrono</div>
                    </div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo count($badges_utilisateur); ?></div>
                        <div class="stat-label">Badges</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="progression-tabs">
        <button class="tab-button active" data-tab="badges">Mes Badges</button>
        <button class="tab-button" data-tab="all-badges">Tous les Badges</button>
        <button class="tab-button" data-tab="activities">Activités Récentes</button>
    </div>
    
    <div class="tab-content active" id="badges">
        <h2>Mes Badges</h2>
        
        <?php if (empty($badges_utilisateur)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Vous n'avez pas encore obtenu de badges. Participez à des sessions et améliorez vos performances pour en gagner !
            </div>
        <?php else: ?>
            <div class="badges-grid">
                <?php foreach ($badges_utilisateur as $badge): ?>
                    <div class="badge-card">
                        <div class="badge-icon <?php echo $badge['categorie']; ?>">
                            <i class="<?php echo getBadgeIcon($badge['categorie']); ?>"></i>
                        </div>
                        <div class="badge-content">
                            <h3 class="badge-title"><?php echo htmlspecialchars($badge['nom']); ?></h3>
                            <div class="badge-description"><?php echo htmlspecialchars($badge['description']); ?></div>
                            <div class="badge-meta">
                                <div class="badge-category"><?php echo getBadgeCategorie($badge['categorie']); ?></div>
                                <div class="badge-level">Niveau <?php echo $badge['niveau']; ?></div>
                            </div>
                            <div class="badge-date">Obtenu le <?php echo date('d/m/Y', strtotime($badge['date_obtention'])); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="tab-content" id="all-badges">
        <h2>Tous les Badges</h2>
        
        <div class="badges-categories">
            <?php
            $categories = [];
            foreach ($tous_badges as $badge) {
                if (!in_array($badge['categorie'], $categories)) {
                    $categories[] = $badge['categorie'];
                }
            }
            ?>
            
            <?php foreach ($categories as $categorie): ?>
                <div class="badge-category-section">
                    <h3><?php echo getBadgeCategorie($categorie); ?></h3>
                    
                    <div class="badges-grid">
                        <?php
                        $badges_categorie = array_filter($tous_badges, function($badge) use ($categorie) {
                            return $badge['categorie'] === $categorie;
                        });
                        
                        foreach ($badges_categorie as $badge):
                            $obtenu = false;
                            foreach ($badges_utilisateur as $user_badge) {
                                if ($user_badge['id'] === $badge['id']) {
                                    $obtenu = true;
                                    break;
                                }
                            }
                        ?>
                            <div class="badge-card <?php echo $obtenu ? 'obtained' : 'locked'; ?>">
                                <div class="badge-icon <?php echo $badge['categorie']; ?>">
                                    <i class="<?php echo getBadgeIcon($badge['categorie']); ?>"></i>
                                </div>
                                <div class="badge-content">
                                    <h3 class="badge-title"><?php echo htmlspecialchars($badge['nom']); ?></h3>
                                    <div class="badge-description"><?php echo htmlspecialchars($badge['description']); ?></div>
                                    <div class="badge-meta">
                                        <div class="badge-category"><?php echo getBadgeCategorie($badge['categorie']); ?></div>
                                        <div class="badge-level">Niveau <?php echo $badge['niveau']; ?></div>
                                    </div>
                                    <?php if ($obtenu): ?>
                                        <div class="badge-status obtained">Obtenu</div>
                                    <?php else: ?>
                                        <div class="badge-status locked">Non obtenu</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="tab-content" id="activities">
        <h2>Activités Récentes</h2>
        
        <?php if (empty($activites)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Aucune activité récente.
            </div>
        <?php else: ?>
            <div class="activities-timeline">
                <?php foreach ($activites as $activite): ?>
                    <div class="activity-item">
                        <div class="activity-date">
                            <div class="date-badge"><?php echo date('d', strtotime($activite['date'])); ?></div>
                            <div class="date-month"><?php echo date('M', strtotime($activite['date'])); ?></div>
                        </div>
                        
                        <div class="activity-content">
                            <?php if ($activite['type'] === 'session'): ?>
                                <div class="activity-icon session">
                                    <i class="fas fa-flag-checkered"></i>
                                </div>
                                <div class="activity-details">
                                    <div class="activity-title">Session sur <?php echo htmlspecialchars($activite['circuit_nom']); ?></div>
                                    <div class="activity-subtitle"><?php echo getSessionTypeLabel($activite['sous_type']); ?></div>
                                </div>
                            <?php elseif ($activite['type'] === 'badge'): ?>
                                <div class="activity-icon badge">
                                    <i class="fas fa-medal"></i>
                                </div>
                                <div class="activity-details">
                                    <div class="activity-title">Badge obtenu : <?php echo htmlspecialchars($activite['badge_nom']); ?></div>
                                    <div class="activity-subtitle"><?php echo htmlspecialchars($activite['badge_description']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="progression-info">
        <h2>Comment gagner des badges et de l'expérience</h2>
        
        <div class="info-grid">
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-flag-checkered"></i>
                </div>
                <div class="info-content">
                    <h3>Participez à des sessions</h3>
                    <p>Chaque session sur circuit vous rapporte de l'expérience. Plus vous participez à des sessions, plus vous gagnez d'XP et débloquez des badges.</p>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-stopwatch"></i>
                </div>
                <div class="info-content">
                    <h3>Améliorez vos chronos</h3>
                    <p>Battez vos records personnels pour gagner des badges de performance et de l'expérience supplémentaire.</p>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-road"></i>
                </div>
                <div class="info-content">
                    <h3>Explorez de nouveaux circuits</h3>
                    <p>Visitez différents circuits pour obtenir des badges d'exploration et diversifier votre expérience.</p>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-motorcycle"></i>
                </div>
                <div class="info-content">
                    <h3>Utilisez différentes motos</h3>
                    <p>Testez différentes motos pour gagner des badges de diversité et approfondir vos connaissances.</p>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="info-content">
                    <h3>Contribuez à la communauté</h3>
                    <p>Partagez vos connaissances, répondez aux questions et aidez les autres pilotes pour gagner des badges de contribution.</p>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="info-content">
                    <h3>Progressez régulièrement</h3>
                    <p>La constance est récompensée ! Connectez-vous régulièrement et améliorez-vous progressivement pour obtenir des badges de progression.</p>
                </div>
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
});
</script>

<style>
.progression-container {
    padding: 1rem 0;
}

.progression-title {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 0.5rem;
}

.progression-intro {
    margin-bottom: 2rem;
    font-size: 1.1rem;
    line-height: 1.6;
}

.progression-dashboard {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

@media (min-width: 992px) {
    .progression-dashboard {
        grid-template-columns: 1fr 2fr;
    }
}

.user-level-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
}

.level-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.level-badge {
    width: 80px;
    height: 80px;
    background-color: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: bold;
    color: #000;
}

.level-info h2 {
    margin: 0 0 0.5rem;
    color: var(--primary-color);
}

.level-title {
    font-size: 1.1rem;
    color: var(--dark-gray);
}

.level-progress {
    margin-bottom: 1.5rem;
}

.progress-bar {
    height: 10px;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 5px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-fill {
    height: 100%;
    background-color: var(--primary-color);
    border-radius: 5px;
}

.progress-text {
    text-align: right;
    font-size: 0.9rem;
    color: var(--dark-gray);
}

.level-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    text-align: center;
}

.stat-item {
    padding: 1rem;
    background-color: rgba(255, 255, 255, 0.05);
    border-radius: var(--border-radius);
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.9rem;
    color: var(--dark-gray);
}

.stats-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
}

.stats-card h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
}

.stat-box {
    background-color: rgba(255, 255, 255, 0.05);
    border-radius: var(--border-radius);
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    width: 40px;
    height: 40px;
    background-color: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #000;
}

.progression-tabs {
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

.badges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.badge-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
    display: flex;
    gap: 1.5rem;
    transition: transform 0.3s;
}

.badge-card:hover {
    transform: translateY(-5px);
}

.badge-card.obtained {
    border-color: var(--primary-color);
}

.badge-card.locked {
    opacity: 0.7;
}

.badge-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: #000;
}

.badge-icon.sessions {
    background-color: #00a8ff;
}

.badge-icon.performance {
    background-color: #ff3e3e;
}

.badge-icon.exploration {
    background-color: #00c853;
}

.badge-icon.contribution {
    background-color: #ffc107;
}

.badge-icon.progression {
    background-color: #9c27b0;
}

.badge-content {
    flex: 1;
}

.badge-title {
    margin: 0 0 0.5rem;
    color: var(--text-color);
}

.badge-description {
    margin-bottom: 1rem;
    font-size: 0.95rem;
    line-height: 1.6;
}

.badge-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    color: var(--dark-gray);
    margin-bottom: 0.5rem;
}

.badge-date {
    font-size: 0.9rem;
    color: var(--dark-gray);
}

.badge-status {
    font-size: 0.9rem;
    font-weight: bold;
}

.badge-status.obtained {
    color: var(--primary-color);
}

.badge-status.locked {
    color: var(--dark-gray);
}

.badge-category-section {
    margin-bottom: 2rem;
}

.badge-category-section h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.activities-timeline {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    position: relative;
}

.activities-timeline::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 40px;
    width: 2px;
    background-color: var(--primary-color);
}

.activity-item {
    display: flex;
    gap: 1.5rem;
    position: relative;
}

.activity-date {
    min-width: 80px;
    text-align: center;
    background-color: var(--primary-color);
    color: #000;
    border-radius: var(--border-radius);
    padding: 0.5rem;
    z-index: 1;
}

.date-badge {
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 1;
}

.date-month {
    font-size: 0.9rem;
    text-transform: uppercase;
}

.activity-content {
    flex: 1;
    display: flex;
    gap: 1rem;
    background-color: rgba(255, 255, 255, 0.05);
    border-radius: var(--border-radius);
    padding: 1rem;
    border: 1px solid var(--light-gray);
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #000;
}

.activity-icon.session {
    background-color: #00a8ff;
}

.activity-icon.badge {
    background-color: #ffc107;
}

.activity-details {
    flex: 1;
}

.activity-title {
    font-weight: bold;
    margin-bottom: 0.25rem;
}

.activity-subtitle {
    font-size: 0.9rem;
    color: var(--dark-gray);
}

.progression-info {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
    margin-top: 2rem;
}

.progression-info h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.info-card {
    background-color: rgba(255, 255, 255, 0.05);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
    display: flex;
    gap: 1.5rem;
}

.info-icon {
    width: 50px;
    height: 50px;
    background-color: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #000;
}

.info-content {
    flex: 1;
}

.info-content h3 {
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.info-content p {
    line-height: 1.6;
    font-size: 0.95rem;
}
</style>

<?php
// Fonction pour obtenir le titre du niveau
function getNiveauTitre($niveau) {
    $titres = [
        1 => 'Débutant',
        2 => 'Novice',
        3 => 'Intermédiaire',
        4 => 'Avancé',
        5 => 'Expert',
        6 => 'Maître',
        7 => 'Élite',
        8 => 'Champion',
        9 => 'Légende',
        10 => 'Grand Maître'
    ];
    
    return $titres[$niveau] ?? 'Pilote';
}

// Fonction pour obtenir l'icône du badge
function getBadgeIcon($categorie) {
    $icons = [
        'sessions' => 'fas fa-flag-checkered',
        'performance' => 'fas fa-stopwatch',
        'exploration' => 'fas fa-road',
        'contribution' => 'fas fa-comments',
        'progression' => 'fas fa-chart-line'
    ];
    
    return $icons[$categorie] ?? 'fas fa-medal';
}

// Fonction pour obtenir le libellé de la catégorie de badge
function getBadgeCategorie($categorie) {
    $categories = [
        'sessions' => 'Sessions',
        'performance' => 'Performance',
        'exploration' => 'Exploration',
        'contribution' => 'Contribution',
        'progression' => 'Progression'
    ];
    
    return $categories[$categorie] ?? 'Autre';
}

// Fonction pour obtenir le libellé du type de session
function getSessionTypeLabel($type) {
    $types = [
        'course' => 'Course',
        'qualification' => 'Qualification',
        'free_practice' => 'Essai libre',
        'entrainement' => 'Entraînement',
        'track_day' => 'Track Day'
    ];
    
    return $types[$type] ?? 'Session';
}

// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
