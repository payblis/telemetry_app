<?php
/**
 * Contrôleur pour l'importation des détails d'un circuit via ChatGPT
 */

// Inclure les fichiers nécessaires
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/chatgpt.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'classes/Database.php';
require_once 'classes/ChatGPT.php';
require_once 'classes/Circuit.php';

// Vérifier si l'utilisateur est connecté
checkLogin();

// Initialiser les objets
$chatGPT = new ChatGPT();
$circuitObj = new Circuit();

// Initialiser les variables
$success = false;
$message = '';
$circuitDetails = null;

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['circuit_name']) && isset($_POST['country'])) {
    $circuitName = $_POST['circuit_name'];
    $country = $_POST['country'];
    
    // Importer les détails du circuit via ChatGPT
    $circuitDetails = $chatGPT->importCircuitDetails($circuitName, $country);
    
    if ($circuitDetails) {
        // Créer le circuit dans la base de données
        $circuitId = $circuitObj->create([
            'name' => $circuitDetails['name'],
            'country' => $circuitDetails['country'],
            'length' => isset($circuitDetails['length']) ? $circuitDetails['length'] : 0,
            'details' => $circuitDetails['details']
        ]);
        
        // Ajouter les virages si disponibles
        if (isset($circuitDetails['corners']) && !empty($circuitDetails['corners'])) {
            foreach ($circuitDetails['corners'] as $corner) {
                $circuitObj->addCorner($circuitId, $corner['number'], $corner['type'], $corner['description']);
            }
        }
        
        $success = true;
        $message = 'Circuit importé avec succès !';
    } else {
        $message = 'Erreur lors de l\'importation du circuit. Veuillez réessayer.';
    }
}

// Inclure la vue
include 'pages/circuit_import.php';
