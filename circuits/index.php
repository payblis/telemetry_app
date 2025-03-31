<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/database.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2 class="card-title">Liste des Circuits</h2>
    
    <div class="mb-3">
        <a href="<?php echo url('circuits/create.php'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter un circuit
        </a>
    </div>
    
    <?php
    // Récupérer la liste des circuits
    $sql = "SELECT * FROM circuits ORDER BY nom";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        // Afficher les circuits dans un tableau
        echo '<table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Pays</th>
                        <th>Longueur</th>
                        <th>Largeur</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';
        
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                    <td>' . htmlspecialchars($row['nom']) . '</td>
                    <td>' . htmlspecialchars($row['pays']) . '</td>
                    <td>' . ($row['longueur'] ? htmlspecialchars($row['longueur']) . ' km' : '-') . '</td>
                    <td>' . ($row['largeur'] ? htmlspecialchars($row['largeur']) . ' m' : '-') . '</td>
                    <td class="table-actions">
                        <a href="' . url('circuits/view.php?id=' . $row['id']) . '" class="btn btn-sm btn-view">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . url('circuits/edit.php?id=' . $row['id']) . '" class="btn btn-sm btn-edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="' . url('circuits/delete.php?id=' . $row['id']) . '" class="btn btn-sm btn-delete">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>';
        }
        
        echo '</tbody>
            </table>';
    } else {
        // Aucun circuit trouvé
        echo '<div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Aucun circuit n\'a été trouvé. Commencez par en ajouter un !
              </div>';
    }
    ?>
</div>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
