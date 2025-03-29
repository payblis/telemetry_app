<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2 class="card-title">Liste des Pilotes</h2>
    
    <div class="mb-3">
        <a href="/telemoto/pilotes/create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter un pilote
        </a>
    </div>
    
    <?php
    // Récupérer la liste des pilotes
    $sql = "SELECT * FROM pilotes ORDER BY nom, prenom";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        // Afficher les pilotes dans un tableau
        echo '<table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Taille</th>
                        <th>Poids</th>
                        <th>Championnat</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';
        
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                    <td>' . htmlspecialchars($row['nom']) . '</td>
                    <td>' . htmlspecialchars($row['prenom']) . '</td>
                    <td>' . ($row['taille'] ? htmlspecialchars($row['taille']) . ' m' : '-') . '</td>
                    <td>' . ($row['poids'] ? htmlspecialchars($row['poids']) . ' kg' : '-') . '</td>
                    <td>' . htmlspecialchars($row['championnat']) . '</td>
                    <td class="table-actions">
                        <a href="/telemoto/pilotes/view.php?id=' . $row['id'] . '" class="btn btn-sm btn-view">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="/telemoto/pilotes/edit.php?id=' . $row['id'] . '" class="btn btn-sm btn-edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="/telemoto/pilotes/delete.php?id=' . $row['id'] . '" class="btn btn-sm btn-delete">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>';
        }
        
        echo '</tbody>
            </table>';
    } else {
        // Aucun pilote trouvé
        echo '<div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Aucun pilote n\'a été trouvé. Commencez par en ajouter un !
              </div>';
    }
    ?>
</div>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
