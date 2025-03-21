<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\OpenAIService;
use App\Models\Session;

class IAController extends Controller
{
    private $openai;
    private $session;

    public function __construct()
    {
        parent::__construct();
        $this->openai = new OpenAIService();
        $this->session = new Session();
    }

    public function analyse(Request $request): Response
    {
        $this->requireAuth();

        try {
            $sessionId = $request->request->get('session_id');
            $specificFocus = $request->request->get('focus'); // Optional focus area

            if (!$sessionId) {
                throw new \Exception('Session ID is required');
            }

            // Get session data
            $sessionData = $this->session->getDetailedStats($sessionId);
            $telemetryData = $this->session->getTelemetryGraphData($sessionId);

            // Get AI analysis
            $analysis = $this->openai->analyzeTelemetry($sessionData, $telemetryData);

            return $this->json([
                'success' => true,
                'data' => $analysis
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function feedback(Request $request): Response
    {
        $this->requireAuth();
        
        if (!$this->hasRole('telemetrist')) {
            return $this->json([
                'success' => false,
                'error' => 'Unauthorized. Only telemetrists can provide feedback.'
            ], 403);
        }

        try {
            $this->validateCSRF();

            $data = [
                'session_id' => $request->request->get('session_id'),
                'recommendation_id' => $request->request->get('recommendation_id'),
                'feedback' => $request->request->get('feedback'),
                'success_rate' => $request->request->get('success_rate'),
                'expert_notes' => $request->request->get('expert_notes')
            ];

            // Validate required fields
            foreach (['session_id', 'recommendation_id', 'feedback'] as $field) {
                if (empty($data[$field])) {
                    throw new \Exception("Field {$field} is required");
                }
            }

            // Get session and recommendation data for context
            $sessionData = $this->session->getDetailedStats($data['session_id']);
            
            // Enrich internal knowledge with expert feedback
            $this->openai->enrichInternalKnowledge([
                'session_data' => $sessionData,
                'feedback' => $data
            ]);

            // Store feedback in database
            $stmt = $this->db->prepare("
                INSERT INTO expert_responses (
                    expert_id,
                    question_id,
                    reponse,
                    validee
                ) VALUES (?, ?, ?, ?)
            ");

            $stmt->execute([
                $_SESSION['user_id'],
                $data['recommendation_id'],
                json_encode($data),
                true
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Feedback recorded and knowledge base enriched'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getKnowledgeBase(Request $request): Response
    {
        $this->requireAuth();

        try {
            $category = $request->query->get('category');
            $query = $request->query->get('query');

            $sql = "
                SELECT *
                FROM ia_internal_knowledge
                WHERE 1=1
            ";
            $params = [];

            if ($category) {
                $sql .= " AND categorie = ?";
                $params[] = $category;
            }

            if ($query) {
                $sql .= " AND (question LIKE ? OR reponse LIKE ?)";
                $params[] = "%{$query}%";
                $params[] = "%{$query}%";
            }

            $sql .= " ORDER BY confiance DESC, created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $knowledge = $stmt->fetchAll();

            return $this->json([
                'success' => true,
                'data' => $knowledge
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function enrichKnowledge(Request $request): Response
    {
        $this->requireAuth();
        
        if (!$this->hasRole('admin')) {
            return $this->json([
                'success' => false,
                'error' => 'Unauthorized. Only administrators can manually enrich knowledge.'
            ], 403);
        }

        try {
            $this->validateCSRF();

            $data = [
                'category' => $request->request->get('category'),
                'question' => $request->request->get('question'),
                'answer' => $request->request->get('answer'),
                'confidence' => $request->request->get('confidence', 0.8)
            ];

            // Validate required fields
            foreach (['category', 'question', 'answer'] as $field) {
                if (empty($data[$field])) {
                    throw new \Exception("Field {$field} is required");
                }
            }

            // Store in database
            $stmt = $this->db->prepare("
                INSERT INTO ia_internal_knowledge (
                    categorie,
                    question,
                    reponse,
                    confiance
                ) VALUES (?, ?, ?, ?)
            ");

            $stmt->execute([
                $data['category'],
                $data['question'],
                $data['answer'],
                $data['confidence']
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Knowledge base enriched successfully'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
} 