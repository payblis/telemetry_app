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
?>

<div class="admin-dashboard">
    <h1 class="admin-title">Panneau d'Administration</h1>
    
    <div class="admin-cards">
        <div class="admin-card">
            <div class="admin-card-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="admin-card-content">
                <h3>Gestion des Utilisateurs</h3>
                <p>Gérer les utilisateurs, experts et administrateurs</p>
                <a href="<?php echo url('admin/users.php'); ?>" class="btn btn-primary">Accéder</a>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="admin-card-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="admin-card-content">
                <h3>Statistiques d'Utilisation</h3>
                <p>Consulter les statistiques d'utilisation de l'application</p>
                <a href="<?php echo url('admin/stats.php'); ?>" class="btn btn-primary">Accéder</a>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="admin-card-icon">
                <i class="fas fa-database"></i>
            </div>
            <div class="admin-card-content">
                <h3>Base de Données</h3>
                <p>Gérer les données de l'application</p>
                <a href="<?php echo url('admin/database.php'); ?>" class="btn btn-primary">Accéder</a>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="admin-card-icon">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="admin-card-content">
                <h3>Configuration</h3>
                <p>Paramètres généraux de l'application</p>
                <a href="<?php echo url('admin/config.php'); ?>" class="btn btn-primary">Accéder</a>
            </div>
        </div>
    </div>
    
    <div class="admin-section">
        <h2>Activité Récente</h2>
        
        <div class="admin-table-container">
            <?php
            // Récupérer les dernières connexions
            $sql = "SELECT cl.id, cl.utilisateur_id, cl.ip_address, cl.created_at, 
                    u.nom, u.prenom, u.email, u.role
                    FROM connexions_log cl
                    JOIN utilisateurs u ON cl.utilisateur_id = u.id
                    ORDER BY cl.created_at DESC
                    LIMIT 10";
            
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                echo '<table class="admin-table">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>IP</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>';
                
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>
                            <td>' . htmlspecialchars($row['prenom'] . ' ' . $row['nom']) . '</td>
                            <td>' . htmlspecialchars($row['email']) . '</td>
                            <td><span class="badge badge-' . $row['role'] . '">' . ucfirst($row['role']) . '</span></td>
                            <td>' . htmlspecialchars($row['ip_address']) . '</td>
                            <td>' . date('d/m/Y H:i', strtotime($row['created_at'])) . '</td>
                        </tr>';
                }
                
                echo '</tbody>
                    </table>';
            } else {
                echo '<div class="alert alert-info">Aucune activité récente.</div>';
            }
            ?>
        </div>
    </div>
</div>

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

.admin-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.admin-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    border: 1px solid var(--light-gray);
    transition: transform 0.3s, border-color 0.3s;
}

.admin-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary-color);
}

.admin-card-icon {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-right: 1.5rem;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(0, 168, 255, 0.1);
    border-radius: 50%;
}

.admin-card-content {
    flex: 1;
}

.admin-card-content h3 {
    margin-bottom: 0.5rem;
    color: var(--primary-color);
}

.admin-card-content p {
    margin-bottom: 1rem;
    color: var(--text-color);
    opacity: 0.8;
}

.admin-section {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid var(--light-gray);
}

.admin-section h2 {
    color: var(--primary-color);
    margin-bottom: 1rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.admin-table-container {
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th, .admin-table td {
    padding: 0.8rem;
    text-align: left;
    border-bottom: 1px solid var(--light-gray);
}

.admin-table th {
    background-color: rgba(58, 66, 85, 0.5);
    font-weight: bold;
    color: var(--primary-color);
}

.admin-table tr:hover {
    background-color: rgba(255, 255, 255, 0.05);
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
    text-transform: uppercase;
}

.badge-admin {
    background-color: var(--primary-color);
    color: #000;
}

.badge-expert {
    background-color: var(--warning-color);
    color: #000;
}

.badge-user {
    background-color: var(--light-gray);
    color: var(--text-color);
}
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
