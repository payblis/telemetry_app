<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2 class="card-title">Liste des Sessions</h2>
    
    <div class="mb-3">
        <a href="<?php echo url('sessions/create.php'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle Session
        </a>
    </div>
    
    <?php
    // Récupérer la liste des sessions
    $sql = "SELECT s.*, p.nom as pilote_nom, p.prenom as pilote_prenom, m.marque as moto_marque, m.modele as moto_modele, c.nom as circuit_nom 
            FROM sessions s 
            LEFT JOIN pilotes p ON s.pilote_id = p.id 
            LEFT JOIN motos m ON s.moto_id = m.id 
            LEFT JOIN circuits c ON s.circuit_id = c.id 
            ORDER BY s.date DESC";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        // Afficher les sessions dans un tableau
        echo '<table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Pilote</th>
                        <th>Moto</th>
                        <th>Circuit</th>
                        <th>Conditions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';
        
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                    <td>' . date('d/m/Y', strtotime($row['date'])) . '</td>
                    <td>' . ucfirst(str_replace('_', ' ', $row['type'])) . '</td>
                    <td>' . htmlspecialchars($row['pilote_nom'] . ' ' . $row['pilote_prenom']) . '</td>
                    <td>' . htmlspecialchars($row['moto_marque'] . ' ' . $row['moto_modele']) . '</td>
                    <td>' . htmlspecialchars($row['circuit_nom']) . '</td>
                    <td>' . htmlspecialchars($row['conditions'] ?? '-') . '</td>
                    <td class="table-actions">
                        <a href="' . url('sessions/view.php?id=' . $row['id']) . '" class="btn btn-sm btn-view">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . url('sessions/edit.php?id=' . $row['id']) . '" class="btn btn-sm btn-edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="' . url('sessions/delete.php?id=' . $row['id']) . '" class="btn btn-sm btn-delete">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>';
        }
        
        echo '</tbody>
            </table>';
    } else {
        // Aucune session trouvée
        echo '<div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Aucune session n\'a été trouvée. Commencez par en créer une !
              </div>';
    }
    ?>
</div>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
