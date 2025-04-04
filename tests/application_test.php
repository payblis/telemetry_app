<?php
/**
 * Script de test pour vérifier le bon fonctionnement de l'application
 */

// Définir le mode test
define('TEST_MODE', true);

// Charger les configurations
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/autoload.php';

// Classe de test
class ApplicationTester {
    private $results = [];
    private $totalTests = 0;
    private $passedTests = 0;
    
    /**
     * Exécute tous les tests
     */
    public function runAllTests() {
        echo "=== DÉBUT DES TESTS DE L'APPLICATION ===\n\n";
        
        // Tests de base
        $this->testDatabaseConnection();
        $this->testAutoloader();
        
        // Tests des modèles
        $this->testUserModel();
        $this->testPiloteModel();
        $this->testMotoModel();
        $this->testCircuitModel();
        $this->testTelemetrieModel();
        $this->testRecommendationModel();
        
        // Tests des contrôleurs
        $this->testAuthController();
        $this->testPiloteController();
        $this->testMotoController();
        $this->testCircuitController();
        $this->testTelemetrieController();
        $this->testAnalyseController();
        
        // Tests des utilitaires
        $this->testViewUtility();
        $this->testValidatorUtility();
        $this->testFileManagerUtility();
        
        // Afficher le résumé
        $this->displaySummary();
    }
    
    /**
     * Teste la connexion à la base de données
     */
    private function testDatabaseConnection() {
        $this->startTest('Connexion à la base de données');
        
        try {
            $db = \App\Utils\Database::getInstance();
            $this->assertNotNull($db, 'L\'instance de base de données est créée');
            
            $testQuery = $db->query('SELECT 1');
            $this->assertTrue($testQuery !== false, 'Exécution d\'une requête simple');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur de connexion à la base de données: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste l'autoloader
     */
    private function testAutoloader() {
        $this->startTest('Autoloader');
        
        try {
            $this->assertTrue(class_exists('\App\Utils\Database'), 'Classe Database chargée');
            $this->assertTrue(class_exists('\App\Utils\View'), 'Classe View chargée');
            $this->assertTrue(class_exists('\App\Utils\Validator'), 'Classe Validator chargée');
            $this->assertTrue(class_exists('\App\Utils\FileManager'), 'Classe FileManager chargée');
            $this->assertTrue(class_exists('\App\Utils\OpenAIService'), 'Classe OpenAIService chargée');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur de l\'autoloader: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste le modèle utilisateur
     */
    private function testUserModel() {
        $this->startTest('UserModel');
        
        try {
            $userModel = new \App\Models\UserModel();
            $this->assertNotNull($userModel, 'Instance UserModel créée');
            
            // Tester les méthodes principales
            $this->assertTrue(method_exists($userModel, 'create'), 'Méthode create existe');
            $this->assertTrue(method_exists($userModel, 'getById'), 'Méthode getById existe');
            $this->assertTrue(method_exists($userModel, 'getByEmail'), 'Méthode getByEmail existe');
            $this->assertTrue(method_exists($userModel, 'update'), 'Méthode update existe');
            $this->assertTrue(method_exists($userModel, 'delete'), 'Méthode delete existe');
            $this->assertTrue(method_exists($userModel, 'authenticate'), 'Méthode authenticate existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur du modèle utilisateur: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste le modèle pilote
     */
    private function testPiloteModel() {
        $this->startTest('PiloteModel');
        
        try {
            $piloteModel = new \App\Models\PiloteModel();
            $this->assertNotNull($piloteModel, 'Instance PiloteModel créée');
            
            // Tester les méthodes principales
            $this->assertTrue(method_exists($piloteModel, 'create'), 'Méthode create existe');
            $this->assertTrue(method_exists($piloteModel, 'getById'), 'Méthode getById existe');
            $this->assertTrue(method_exists($piloteModel, 'getByUserId'), 'Méthode getByUserId existe');
            $this->assertTrue(method_exists($piloteModel, 'update'), 'Méthode update existe');
            $this->assertTrue(method_exists($piloteModel, 'delete'), 'Méthode delete existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur du modèle pilote: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste le modèle moto
     */
    private function testMotoModel() {
        $this->startTest('MotoModel');
        
        try {
            $motoModel = new \App\Models\MotoModel();
            $this->assertNotNull($motoModel, 'Instance MotoModel créée');
            
            // Tester les méthodes principales
            $this->assertTrue(method_exists($motoModel, 'create'), 'Méthode create existe');
            $this->assertTrue(method_exists($motoModel, 'getById'), 'Méthode getById existe');
            $this->assertTrue(method_exists($motoModel, 'getByUserId'), 'Méthode getByUserId existe');
            $this->assertTrue(method_exists($motoModel, 'update'), 'Méthode update existe');
            $this->assertTrue(method_exists($motoModel, 'delete'), 'Méthode delete existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur du modèle moto: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste le modèle circuit
     */
    private function testCircuitModel() {
        $this->startTest('CircuitModel');
        
        try {
            $circuitModel = new \App\Models\CircuitModel();
            $this->assertNotNull($circuitModel, 'Instance CircuitModel créée');
            
            // Tester les méthodes principales
            $this->assertTrue(method_exists($circuitModel, 'create'), 'Méthode create existe');
            $this->assertTrue(method_exists($circuitModel, 'getById'), 'Méthode getById existe');
            $this->assertTrue(method_exists($circuitModel, 'getAll'), 'Méthode getAll existe');
            $this->assertTrue(method_exists($circuitModel, 'update'), 'Méthode update existe');
            $this->assertTrue(method_exists($circuitModel, 'delete'), 'Méthode delete existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur du modèle circuit: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste le modèle télémétrie
     */
    private function testTelemetrieModel() {
        $this->startTest('TelemetrieModel');
        
        try {
            $telemetrieModel = new \App\Models\TelemetrieModel();
            $this->assertNotNull($telemetrieModel, 'Instance TelemetrieModel créée');
            
            // Tester les méthodes principales
            $this->assertTrue(method_exists($telemetrieModel, 'create'), 'Méthode create existe');
            $this->assertTrue(method_exists($telemetrieModel, 'getById'), 'Méthode getById existe');
            $this->assertTrue(method_exists($telemetrieModel, 'getBySessionId'), 'Méthode getBySessionId existe');
            $this->assertTrue(method_exists($telemetrieModel, 'importSensorLoggerData'), 'Méthode importSensorLoggerData existe');
            $this->assertTrue(method_exists($telemetrieModel, 'detectLaps'), 'Méthode detectLaps existe');
            $this->assertTrue(method_exists($telemetrieModel, 'getAggregatedDataBySessionId'), 'Méthode getAggregatedDataBySessionId existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur du modèle télémétrie: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste le modèle recommandation
     */
    private function testRecommendationModel() {
        $this->startTest('RecommendationModel');
        
        try {
            $recommendationModel = new \App\Models\RecommendationModel();
            $this->assertNotNull($recommendationModel, 'Instance RecommendationModel créée');
            
            // Tester les méthodes principales
            $this->assertTrue(method_exists($recommendationModel, 'create'), 'Méthode create existe');
            $this->assertTrue(method_exists($recommendationModel, 'getBySessionId'), 'Méthode getBySessionId existe');
            $this->assertTrue(method_exists($recommendationModel, 'getRecentByUserId'), 'Méthode getRecentByUserId existe');
            $this->assertTrue(method_exists($recommendationModel, 'updateStatus'), 'Méthode updateStatus existe');
            $this->assertTrue(method_exists($recommendationModel, 'addFeedback'), 'Méthode addFeedback existe');
            $this->assertTrue(method_exists($recommendationModel, 'generateRecommendations'), 'Méthode generateRecommendations existe');
            $this->assertTrue(method_exists($recommendationModel, 'generateCommunityRecommendations'), 'Méthode generateCommunityRecommendations existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur du modèle recommandation: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste le contrôleur d'authentification
     */
    private function testAuthController() {
        $this->startTest('AuthController');
        
        try {
            // Vérifier que la classe existe
            $this->assertTrue(class_exists('\App\Controllers\AuthController'), 'Classe AuthController existe');
            
            // Vérifier les méthodes principales
            $controller = new \ReflectionClass('\App\Controllers\AuthController');
            $this->assertTrue($controller->hasMethod('login'), 'Méthode login existe');
            $this->assertTrue($controller->hasMethod('register'), 'Méthode register existe');
            $this->assertTrue($controller->hasMethod('logout'), 'Méthode logout existe');
            $this->assertTrue($controller->hasMethod('forgotPassword'), 'Méthode forgotPassword existe');
            $this->assertTrue($controller->hasMethod('resetPassword'), 'Méthode resetPassword existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur du contrôleur d\'authentification: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste le contrôleur pilote
     */
    private function testPiloteController() {
        $this->startTest('PiloteController');
        
        try {
            // Vérifier que la classe existe
            $this->assertTrue(class_exists('\App\Controllers\PiloteController'), 'Classe PiloteController existe');
            
            // Vérifier les méthodes principales
            $controller = new \ReflectionClass('\App\Controllers\PiloteController');
            $this->assertTrue($controller->hasMethod('index'), 'Méthode index existe');
            $this->assertTrue($controller->hasMethod('create'), 'Méthode create existe');
            $this->assertTrue($controller->hasMethod('edit'), 'Méthode edit existe');
            $this->assertTrue($controller->hasMethod('delete'), 'Méthode delete existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur du contrôleur pilote: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste le contrôleur moto
     */
    private function testMotoController() {
        $this->startTest('MotoController');
        
        try {
            // Vérifier que la classe existe
            $this->assertTrue(class_exists('\App\Controllers\MotoController'), 'Classe MotoController existe');
            
            // Vérifier les méthodes principales
            $controller = new \ReflectionClass('\App\Controllers\MotoController');
            $this->assertTrue($controller->hasMethod('index'), 'Méthode index existe');
            $this->assertTrue($controller->hasMethod('create'), 'Méthode create existe');
            $this->assertTrue($controller->hasMethod('edit'), 'Méthode edit existe');
            $this->assertTrue($controller->hasMethod('delete'), 'Méthode delete existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur du contrôleur moto: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste le contrôleur circuit
     */
    private function testCircuitController() {
        $this->startTest('CircuitController');
        
        try {
            // Vérifier que la classe existe
            $this->assertTrue(class_exists('\App\Controllers\CircuitController'), 'Classe CircuitController existe');
            
            // Vérifier les méthodes principales
            $controller = new \ReflectionClass('\App\Controllers\CircuitController');
            $this->assertTrue($controller->hasMethod('index'), 'Méthode index existe');
            $this->assertTrue($controller->hasMethod('create'), 'Méthode create existe');
            $this->assertTrue($controller->hasMethod('edit'), 'Méthode edit existe');
            $this->assertTrue($controller->hasMethod('delete'), 'Méthode delete existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur du contrôleur circuit: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste le contrôleur télémétrie
     */
    private function testTelemetrieController() {
        $this->startTest('TelemetrieController');
        
        try {
            // Vérifier que la classe existe
            $this->assertTrue(class_exists('\App\Controllers\TelemetrieController'), 'Classe TelemetrieController existe');
            
            // Vérifier les méthodes principales
            $controller = new \ReflectionClass('\App\Controllers\TelemetrieController');
            $this->assertTrue($controller->hasMethod('index'), 'Méthode index existe');
            $this->assertTrue($controller->hasMethod('create'), 'Méthode create existe');
            $this->assertTrue($controller->hasMethod('view'), 'Méthode view existe');
            $this->assertTrue($controller->hasMethod('import'), 'Méthode import existe');
            $this->assertTrue($controller->hasMethod('graph'), 'Méthode graph existe');
            $this->assertTrue($controller->hasMethod('compare'), 'Méthode compare existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur du contrôleur télémétrie: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste le contrôleur analyse
     */
    private function testAnalyseController() {
        $this->startTest('AnalyseController');
        
        try {
            // Vérifier que la classe existe
            $this->assertTrue(class_exists('\App\Controllers\AnalyseController'), 'Classe AnalyseController existe');
            
            // Vérifier les méthodes principales
            $controller = new \ReflectionClass('\App\Controllers\AnalyseController');
            $this->assertTrue($controller->hasMethod('index'), 'Méthode index existe');
            $this->assertTrue($controller->hasMethod('view'), 'Méthode view existe');
            $this->assertTrue($controller->hasMethod('generate'), 'Méthode generate existe');
            $this->assertTrue($controller->hasMethod('feedback'), 'Méthode feedback existe');
            $this->assertTrue($controller->hasMethod('compare'), 'Méthode compare existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur du contrôleur analyse: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste l'utilitaire View
     */
    private function testViewUtility() {
        $this->startTest('View Utility');
        
        try {
            // Vérifier que la classe existe
            $this->assertTrue(class_exists('\App\Utils\View'), 'Classe View existe');
            
            // Vérifier les méthodes principales
            $utility = new \ReflectionClass('\App\Utils\View');
            $this->assertTrue($utility->hasMethod('render'), 'Méthode render existe');
            $this->assertTrue($utility->hasMethod('escape'), 'Méthode escape existe');
            $this->assertTrue($utility->hasMethod('url'), 'Méthode url existe');
            $this->assertTrue($utility->hasMethod('setNotification'), 'Méthode setNotification existe');
            $this->assertTrue($utility->hasMethod('showNotifications'), 'Méthode showNotifications existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur de l\'utilitaire View: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste l'utilitaire Validator
     */
    private function testValidatorUtility() {
        $this->startTest('Validator Utility');
        
        try {
            // Vérifier que la classe existe
            $this->assertTrue(class_exists('\App\Utils\Validator'), 'Classe Validator existe');
            
            // Vérifier les méthodes principales
            $utility = new \ReflectionClass('\App\Utils\Validator');
            $this->assertTrue($utility->hasMethod('email'), 'Méthode email existe');
            $this->assertTrue($utility->hasMethod('required'), 'Méthode required existe');
            $this->assertTrue($utility->hasMethod('minLength'), 'Méthode minLength existe');
            $this->assertTrue($utility->hasMethod('maxLength'), 'Méthode maxLength existe');
            $this->assertTrue($utility->hasMethod('numeric'), 'Méthode numeric existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur de l\'utilitaire Validator: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste l'utilitaire FileManager
     */
    private function testFileManagerUtility() {
        $this->startTest('FileManager Utility');
        
        try {
            // Vérifier que la classe existe
            $this->assertTrue(class_exists('\App\Utils\FileManager'), 'Classe FileManager existe');
            
            // Vérifier les méthodes principales
            $utility = new \ReflectionClass('\App\Utils\FileManager');
            $this->assertTrue($utility->hasMethod('uploadFile'), 'Méthode uploadFile existe');
            $this->assertTrue($utility->hasMethod('deleteFile'), 'Méthode deleteFile existe');
            $this->assertTrue($utility->hasMethod('getFileExtension'), 'Méthode getFileExtension existe');
            $this->assertTrue($utility->hasMethod('generateUniqueFilename'), 'Méthode generateUniqueFilename existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur de l\'utilitaire FileManager: ' . $e->getMessage());
        }
    }
    
    /**
     * Démarre un test
     * 
     * @param string $testName Nom du test
     */
    private function startTest($testName) {
        $this->totalTests++;
        echo "Test: $testName... ";
    }
    
    /**
     * Marque un test comme réussi
     */
    private function passTest() {
        $this->passedTests++;
        echo "RÉUSSI\n";
    }
    
    /**
     * Marque un test comme échoué
     * 
     * @param string $message Message d'erreur
     */
    private function failTest($message) {
        echo "ÉCHOUÉ\n";
        echo "  Erreur: $message\n";
    }
    
    /**
     * Vérifie qu'une condition est vraie
     * 
     * @param bool $condition Condition à vérifier
     * @param string $message Message en cas d'échec
     */
    private function assertTrue($condition, $message = '') {
        if (!$condition) {
            throw new \Exception("Assertion échouée: $message");
        }
    }
    
    /**
     * Vérifie qu'une valeur n'est pas null
     * 
     * @param mixed $value Valeur à vérifier
     * @param string $message Message en cas d'échec
     */
    private function assertNotNull($value, $message = '') {
        if ($value === null) {
            throw new \Exception("Assertion échouée (valeur null): $message");
        }
    }
    
    /**
     * Affiche le résumé des tests
     */
    private function displaySummary() {
        echo "\n=== RÉSUMÉ DES TESTS ===\n";
        echo "Tests exécutés: {$this->totalTests}\n";
        echo "Tests réussis: {$this->passedTests}\n";
        echo "Tests échoués: " . ($this->totalTests - $this->passedTests) . "\n";
        
        $successRate = ($this->passedTests / $this->totalTests) * 100;
        echo "Taux de réussite: " . round($successRate, 2) . "%\n";
        
        echo "\n=== FIN DES TESTS ===\n";
    }
}

// Exécuter les tests
$tester = new ApplicationTester();
$tester->runAllTests();
