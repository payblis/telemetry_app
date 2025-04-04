<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/database.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2 class="card-title">Liste des Motos</h2>
    
    <div class="mb-3">
        <a href="/telemoto/motos/create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter une moto
        </a>
    </div>
    
    <?php
    // Récupérer la liste des motos
    $sql = "SELECT * FROM motos ORDER BY marque, modele";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        // Afficher les motos dans un tableau
        echo '<table>
                <thead>
                    <tr>
                        <th>Marque</th>
                        <th>Modèle</th>
                        <th>Cylindrée</th>
                        <th>Année</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';
        
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                    <td>' . htmlspecialchars($row['marque']) . '</td>
                    <td>' . htmlspecialchars($row['modele']) . '</td>
                    <td>' . ($row['cylindree'] ? htmlspecialchars($row['cylindree']) . ' cc' : '-') . '</td>
                    <td>' . ($row['annee'] ? htmlspecialchars($row['annee']) : '-') . '</td>
                    <td class="table-actions">
                        <a href="/telemoto/motos/view.php?id=' . $row['id'] . '" class="btn btn-sm btn-view">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="/telemoto/motos/edit.php?id=' . $row['id'] . '" class="btn btn-sm btn-edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="/telemoto/motos/delete.php?id=' . $row['id'] . '" class="btn btn-sm btn-delete">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>';
        }
        
        echo '</tbody>
            </table>';
    } else {
        // Aucune moto trouvée
        echo '<div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Aucune moto n\'a été trouvée. Commencez par en ajouter une !
              </div>';
    }
    ?>
</div>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
