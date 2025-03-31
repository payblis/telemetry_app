<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/auth_functions.php';

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier que l'utilisateur est administrateur
requireAdmin();

// Connexion à la base de données
$conn = getDBConnection();

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';

// Récupérer les statistiques
$stats = [];

// Nombre total d'utilisateurs
$sql = "SELECT COUNT(*) as total, 
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
        SUM(CASE WHEN role = 'expert' THEN 1 ELSE 0 END) as experts,
        SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as users
        FROM utilisateurs";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $stats['utilisateurs'] = $result->fetch_assoc();
}

// Nombre de sessions
$sql = "SELECT COUNT(*) as total,
        SUM(CASE WHEN type = 'course' THEN 1 ELSE 0 END) as courses,
        SUM(CASE WHEN type = 'qualification' THEN 1 ELSE 0 END) as qualifications,
        SUM(CASE WHEN type = 'free_practice' THEN 1 ELSE 0 END) as free_practices,
        SUM(CASE WHEN type = 'entrainement' THEN 1 ELSE 0 END) as entrainements,
        SUM(CASE WHEN type = 'track_day' THEN 1 ELSE 0 END) as track_days
        FROM sessions";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $stats['sessions'] = $result->fetch_assoc();
}

// Nombre de recommandations
$sql = "SELECT COUNT(*) as total,
        SUM(CASE WHEN source = 'chatgpt' THEN 1 ELSE 0 END) as chatgpt,
        SUM(CASE WHEN source = 'expert' THEN 1 ELSE 0 END) as expert,
        SUM(CASE WHEN validation = 'positif' THEN 1 ELSE 0 END) as positives,
        SUM(CASE WHEN validation = 'neutre' THEN 1 ELSE 0 END) as neutres,
        SUM(CASE WHEN validation = 'negatif' THEN 1 ELSE 0 END) as negatives
        FROM recommandations";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $stats['recommandations'] = $result->fetch_assoc();
}

// Nombre de connexions par jour (30 derniers jours)
$sql = "SELECT DATE(created_at) as date, COUNT(*) as count
        FROM connexions_log
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date";
$result = $conn->query($sql);
$connexions_par_jour = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $connexions_par_jour[$row['date']] = $row['count'];
    }
}

// Nombre de sessions par circuit
$sql = "SELECT c.nom, COUNT(s.id) as count
        FROM sessions s
        JOIN circuits c ON s.circuit_id = c.id
        GROUP BY c.id
        ORDER BY count DESC
        LIMIT 10";
$result = $conn->query($sql);
$sessions_par_circuit = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sessions_par_circuit[$row['nom']] = $row['count'];
    }
}

// Nombre de sessions par moto
$sql = "SELECT CONCAT(m.marque, ' ', m.modele) as moto, COUNT(s.id) as count
        FROM sessions s
        JOIN motos m ON s.moto_id = m.id
        GROUP BY m.id
        ORDER BY count DESC
        LIMIT 10";
$result = $conn->query($sql);
$sessions_par_moto = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sessions_par_moto[$row['moto']] = $row['count'];
    }
}
?>

<div class="admin-dashboard">
    <h1 class="admin-title">Statistiques d'Utilisation</h1>
    
    <div class="stats-cards">
        <div class="stats-card">
            <div class="stats-card-header">
                <h3>Utilisateurs</h3>
                <i class="fas fa-users"></i>
            </div>
            <div class="stats-card-body">
                <div class="stats-number"><?php echo $stats['utilisateurs']['total'] ?? 0; ?></div>
                <div class="stats-details">
                    <div class="stats-detail">
                        <span class="stats-label">Admins:</span>
                        <span class="stats-value"><?php echo $stats['utilisateurs']['admins'] ?? 0; ?></span>
                    </div>
                    <div class="stats-detail">
                        <span class="stats-label">Experts:</span>
                        <span class="stats-value"><?php echo $stats['utilisateurs']['experts'] ?? 0; ?></span>
                    </div>
                    <div class="stats-detail">
                        <span class="stats-label">Utilisateurs:</span>
                        <span class="stats-value"><?php echo $stats['utilisateurs']['users'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="stats-card">
            <div class="stats-card-header">
                <h3>Sessions</h3>
                <i class="fas fa-stopwatch"></i>
            </div>
            <div class="stats-card-body">
                <div class="stats-number"><?php echo $stats['sessions']['total'] ?? 0; ?></div>
                <div class="stats-details">
                    <div class="stats-detail">
                        <span class="stats-label">Courses:</span>
                        <span class="stats-value"><?php echo $stats['sessions']['courses'] ?? 0; ?></span>
                    </div>
                    <div class="stats-detail">
                        <span class="stats-label">Qualifications:</span>
                        <span class="stats-value"><?php echo $stats['sessions']['qualifications'] ?? 0; ?></span>
                    </div>
                    <div class="stats-detail">
                        <span class="stats-label">Essais libres:</span>
                        <span class="stats-value"><?php echo $stats['sessions']['free_practices'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="stats-card">
            <div class="stats-card-header">
                <h3>Recommandations</h3>
                <i class="fas fa-lightbulb"></i>
            </div>
            <div class="stats-card-body">
                <div class="stats-number"><?php echo $stats['recommandations']['total'] ?? 0; ?></div>
                <div class="stats-details">
                    <div class="stats-detail">
                        <span class="stats-label">ChatGPT:</span>
                        <span class="stats-value"><?php echo $stats['recommandations']['chatgpt'] ?? 0; ?></span>
                    </div>
                    <div class="stats-detail">
                        <span class="stats-label">Experts:</span>
                        <span class="stats-value"><?php echo $stats['recommandations']['expert'] ?? 0; ?></span>
                    </div>
                    <div class="stats-detail">
                        <span class="stats-label">Positives:</span>
                        <span class="stats-value"><?php echo $stats['recommandations']['positives'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="stats-charts">
        <div class="stats-chart-container">
            <h3>Connexions par jour (30 derniers jours)</h3>
            <canvas id="connexionsChart"></canvas>
        </div>
        
        <div class="stats-chart-container">
            <h3>Sessions par circuit (Top 10)</h3>
            <canvas id="circuitsChart"></canvas>
        </div>
        
        <div class="stats-chart-container">
            <h3>Sessions par moto (Top 10)</h3>
            <canvas id="motosChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Connexions par jour
    const connexionsData = <?php echo json_encode($connexions_par_jour); ?>;
    const connexionsLabels = Object.keys(connexionsData);
    const connexionsValues = Object.values(connexionsData);
    
    new Chart(document.getElementById('connexionsChart'), {
        type: 'line',
        data: {
            labels: connexionsLabels,
            datasets: [{
                label: 'Connexions',
                data: connexionsValues,
                borderColor: '#00a8ff',
                backgroundColor: 'rgba(0, 168, 255, 0.1)',
                tension: 0.4,
                fill: true
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
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#e0e0e0'
                    }
                }
            }
        }
    });
    
    // Sessions par circuit
    const circuitsData = <?php echo json_encode($sessions_par_circuit); ?>;
    const circuitsLabels = Object.keys(circuitsData);
    const circuitsValues = Object.values(circuitsData);
    
    new Chart(document.getElementById('circuitsChart'), {
        type: 'bar',
        data: {
            labels: circuitsLabels,
            datasets: [{
                label: 'Sessions',
                data: circuitsValues,
                backgroundColor: 'rgba(0, 168, 255, 0.7)',
                borderColor: '#00a8ff',
                borderWidth: 1
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
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#e0e0e0'
                    }
                }
            }
        }
    });
    
    // Sessions par moto
    const motosData = <?php echo json_encode($sessions_par_moto); ?>;
    const motosLabels = Object.keys(motosData);
    const motosValues = Object.values(motosData);
    
    new Chart(document.getElementById('motosChart'), {
        type: 'bar',
        data: {
            labels: motosLabels,
            datasets: [{
                label: 'Sessions',
                data: motosValues,
                backgroundColor: 'rgba(255, 62, 62, 0.7)',
                borderColor: '#ff3e3e',
                borderWidth: 1
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
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#e0e0e0'
                    }
                }
            }
        }
    });
});
</script>

<style>
.admin-dashboard {
    padding: 1rem 0;
}

.admin-title {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 0.5rem;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stats-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    border: 1px solid var(--light-gray);
    overflow: hidden;
}

.stats-card-header {
    background-color: rgba(0, 0, 0, 0.2);
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--light-gray);
}

.stats-card-header h3 {
    margin: 0;
    color: var(--primary-color);
}

.stats-card-header i {
    font-size: 1.5rem;
    color: var(--primary-color);
}

.stats-card-body {
    padding: 1.5rem;
}

.stats-number {
    font-size: 3rem;
    font-weight: bold;
    color: var(--text-color);
    margin-bottom: 1rem;
    text-align: center;
}

.stats-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 1rem;
}

.stats-detail {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.stats-label {
    font-size: 0.9rem;
    color: var(--dark-gray);
    margin-bottom: 0.25rem;
}

.stats-value {
    font-size: 1.2rem;
    font-weight: bold;
    color: var(--text-color);
}

.stats-charts {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.stats-chart-container {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
}

.stats-chart-container h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
    text-align: center;
}

canvas {
    width: 100% !important;
    height: 300px !important;
}

@media (min-width: 992px) {
    .stats-charts {
        grid-template-columns: 1fr 1fr;
    }
    
    .stats-charts > div:first-child {
        grid-column: 1 / -1;
    }
}
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
