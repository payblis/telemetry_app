<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Session;
use App\Models\Pilote;
use App\Models\Moto;
use App\Models\Circuit;

class DashboardController extends Controller
{
    private $session;
    private $pilote;
    private $moto;
    private $circuit;

    public function __construct()
    {
        parent::__construct();
        $this->session = new Session();
        $this->pilote = new Pilote();
        $this->moto = new Moto();
        $this->circuit = new Circuit();
    }

    public function index(Request $request): Response
    {
        $this->requireAuth();

        try {
            // Get recent sessions
            $recentSessions = $this->session->getRecent(5);

            // Get statistics
            $stats = [
                'total_sessions' => $this->session->count(),
                'total_pilots' => $this->pilote->count(),
                'total_motos' => $this->moto->count(),
                'total_circuits' => $this->circuit->count()
            ];

            // Get best lap times
            $bestLapTimes = $this->session->getBestLapTimes(5);

            // Get latest telemetry data
            $latestTelemetry = $this->session->getLatestTelemetryData();

            return $this->render('dashboard/index', [
                'currentPage' => 'dashboard',
                'stats' => $stats,
                'recentSessions' => $recentSessions,
                'bestLapTimes' => $bestLapTimes,
                'latestTelemetry' => $latestTelemetry,
                'user' => $this->getCurrentUser()
            ]);
        } catch (\Exception $e) {
            return $this->render('dashboard/index', [
                'currentPage' => 'dashboard',
                'error' => $e->getMessage(),
                'user' => $this->getCurrentUser()
            ]);
        }
    }

    public function getSessionStats(Request $request): Response
    {
        $this->requireAuth();

        try {
            $sessionId = $request->query->get('session_id');
            
            if (!$sessionId) {
                throw new \Exception('Session ID is required');
            }

            $stats = $this->session->getDetailedStats($sessionId);
            
            return $this->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getTelemetryGraph(Request $request): Response
    {
        $this->requireAuth();

        try {
            $sessionId = $request->query->get('session_id');
            $dataType = $request->query->get('data_type', 'speed'); // Default to speed
            
            if (!$sessionId) {
                throw new \Exception('Session ID is required');
            }

            $data = $this->session->getTelemetryGraphData($sessionId, $dataType);
            
            return $this->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getAIRecommendations(Request $request): Response
    {
        $this->requireAuth();

        try {
            $sessionId = $request->query->get('session_id');
            
            if (!$sessionId) {
                throw new \Exception('Session ID is required');
            }

            $recommendations = $this->session->getAIRecommendations($sessionId);
            
            return $this->json([
                'success' => true,
                'data' => $recommendations
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
} 