<?php
/**
 * Script de test pour vérifier l'intégration de l'IA et des recommandations
 */

// Définir le mode test
define('TEST_MODE', true);

// Charger les configurations
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/autoload.php';

// Classe de test pour l'intégration de l'IA
class AIIntegrationTester {
    private $results = [];
    private $totalTests = 0;
    private $passedTests = 0;
    
    /**
     * Exécute tous les tests d'intégration de l'IA
     */
    public function runAllTests() {
        echo "=== DÉBUT DES TESTS D'INTÉGRATION IA ===\n\n";
        
        // Tests d'intégration
        $this->testOpenAIService();
        $this->testRecommendationModel();
        $this->testCommunityRecommendations();
        $this->testFeedbackSystem();
        $this->testAnalyseController();
        
        // Afficher le résumé
        $this->displaySummary();
    }
    
    /**
     * Teste le service OpenAI
     */
    private function testOpenAIService() {
        $this->startTest('Service OpenAI');
        
        try {
            // Vérifier que la classe existe
            $this->assertTrue(class_exists('\App\Utils\OpenAIService'), 'Classe OpenAIService existe');
            
            // Vérifier les méthodes principales
            $service = new \ReflectionClass('\App\Utils\OpenAIService');
            $this->assertTrue($service->hasMethod('sendChatRequest'), 'Méthode sendChatRequest existe');
            $this->assertTrue($service->hasMethod('generateRecommendations'), 'Méthode generateRecommendations existe');
            $this->assertTrue($service->hasMethod('prepareContext'), 'Méthode prepareContext existe');
            $this->assertTrue($service->hasMethod('parseRecommendations'), 'Méthode parseRecommendations existe');
            
            // Tester la création d'une instance (sans clé API réelle)
            $openai = new \App\Utils\OpenAIService('dummy_api_key');
            $this->assertNotNull($openai, 'Instance OpenAIService créée');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur du service OpenAI: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste le modèle de recommandation
     */
    private function testRecommendationModel() {
        $this->startTest('Modèle de recommandation');
        
        try {
            // Vérifier que la classe existe
            $this->assertTrue(class_exists('\App\Models\RecommendationModel'), 'Classe RecommendationModel existe');
            
            // Vérifier les méthodes principales
            $model = new \ReflectionClass('\App\Models\RecommendationModel');
            $this->assertTrue($model->hasMethod('create'), 'Méthode create existe');
            $this->assertTrue($model->hasMethod('getBySessionId'), 'Méthode getBySessionId existe');
            $this->assertTrue($model->hasMethod('getRecentByUserId'), 'Méthode getRecentByUserId existe');
            $this->assertTrue($model->hasMethod('updateStatus'), 'Méthode updateStatus existe');
            $this->assertTrue($model->hasMethod('addFeedback'), 'Méthode addFeedback existe');
            $this->assertTrue($model->hasMethod('generateRecommendations'), 'Méthode generateRecommendations existe');
            
            // Tester la création d'une instance
            $recommendationModel = new \App\Models\RecommendationModel();
            $this->assertNotNull($recommendationModel, 'Instance RecommendationModel créée');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur du modèle de recommandation: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste les recommandations communautaires
     */
    private function testCommunityRecommendations() {
        $this->startTest('Recommandations communautaires');
        
        try {
            // Vérifier que la classe existe
            $this->assertTrue(class_exists('\App\Models\RecommendationModel'), 'Classe RecommendationModel existe');
            
            // Vérifier la méthode de recommandations communautaires
            $model = new \ReflectionClass('\App\Models\RecommendationModel');
            $this->assertTrue($model->hasMethod('generateCommunityRecommendations'), 'Méthode generateCommunityRecommendations existe');
            
            // Tester la création d'une instance
            $recommendationModel = new \App\Models\RecommendationModel();
            $this->assertNotNull($recommendationModel, 'Instance RecommendationModel créée');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur des recommandations communautaires: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste le système de feedback
     */
    private function testFeedbackSystem() {
        $this->startTest('Système de feedback');
        
        try {
            // Vérifier que la classe existe
            $this->assertTrue(class_exists('\App\Models\RecommendationModel'), 'Classe RecommendationModel existe');
            
            // Vérifier les méthodes de feedback
            $model = new \ReflectionClass('\App\Models\RecommendationModel');
            $this->assertTrue($model->hasMethod('addFeedback'), 'Méthode addFeedback existe');
            $this->assertTrue($model->hasMethod('updateStatus'), 'Méthode updateStatus existe');
            
            // Vérifier que le contrôleur d'analyse a une méthode de feedback
            $controller = new \ReflectionClass('\App\Controllers\AnalyseController');
            $this->assertTrue($controller->hasMethod('feedback'), 'Méthode feedback du contrôleur existe');
            
            // Vérifier que la vue d'analyse contient un formulaire de feedback
            $viewContent = file_get_contents(__DIR__ . '/../app/views/analyses/view.php');
            $this->assertTrue(strpos($viewContent, 'feedback-form') !== false, 'Formulaire de feedback présent dans la vue');
            $this->assertTrue(strpos($viewContent, 'rating-input') !== false, 'Système de notation présent dans la vue');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur du système de feedback: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste le contrôleur d'analyse
     */
    private function testAnalyseController() {
        $this->startTest('Contrôleur d\'analyse');
        
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
            
            // Vérifier que les vues existent
            $this->assertTrue(file_exists(__DIR__ . '/../app/views/analyses/index.php'), 'Vue index.php existe');
            $this->assertTrue(file_exists(__DIR__ . '/../app/views/analyses/view.php'), 'Vue view.php existe');
            $this->assertTrue(file_exists(__DIR__ . '/../app/views/analyses/compare.php'), 'Vue compare.php existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur du contrôleur d\'analyse: ' . $e->getMessage());
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
$tester = new AIIntegrationTester();
$tester->runAllTests();
