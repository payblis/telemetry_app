<?php

namespace App\Services;

use OpenAI\Client;

class OpenAIService
{
    private $client;
    private $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../Config/config.php';
        $this->client = \OpenAI::client($this->config['openai']['api_key']);
    }

    public function analyzeTelemetry(array $sessionData, array $telemetryData): array
    {
        $prompt = $this->buildTelemetryPrompt($sessionData, $telemetryData);

        try {
            $response = $this->client->chat()->create([
                'model' => $this->config['openai']['model'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert motorcycle telemetry analyst. Analyze the provided data and give specific recommendations for improvement.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => $this->config['openai']['max_tokens']
            ]);

            return $this->parseAIResponse($response->choices[0]->message->content);
        } catch (\Exception $e) {
            // Log error and return default recommendations
            error_log("OpenAI API Error: " . $e->getMessage());
            return $this->getDefaultRecommendations();
        }
    }

    private function buildTelemetryPrompt(array $sessionData, array $telemetryData): string
    {
        $prompt = "Analyze the following motorcycle telemetry data:\n\n";
        
        // Add session context
        $prompt .= "Session Details:\n";
        $prompt .= "- Circuit: {$sessionData['circuit_name']}\n";
        $prompt .= "- Pilot: {$sessionData['pilot_name']}\n";
        $prompt .= "- Best Lap: {$sessionData['best_lap']}\n";
        $prompt .= "- Average Lap: {$sessionData['avg_lap']}\n";
        $prompt .= "- Max Speed: {$sessionData['max_speed']} km/h\n";
        $prompt .= "- Average Speed: {$sessionData['avg_speed']} km/h\n\n";

        // Add telemetry data summary
        $prompt .= "Telemetry Data Points:\n";
        foreach ($telemetryData as $index => $point) {
            if ($index < 5) { // Only include first 5 points as example
                $prompt .= "- Time: {$point['timestamp']}, Speed: {$point['valeur']} km/h\n";
            }
        }

        $prompt .= "\nBased on this data, provide 3 specific recommendations for improvement focusing on:\n";
        $prompt .= "1. Braking patterns\n";
        $prompt .= "2. Speed optimization\n";
        $prompt .= "3. Racing line\n";

        return $prompt;
    }

    private function parseAIResponse(string $response): array
    {
        $recommendations = [];
        $lines = explode("\n", $response);
        
        $currentRec = null;
        foreach ($lines as $line) {
            if (preg_match('/^\d+\.\s+(.+):\s*(.+)$/', $line, $matches)) {
                if ($currentRec) {
                    $recommendations[] = $currentRec;
                }
                $currentRec = [
                    'title' => trim($matches[1]),
                    'description' => trim($matches[2])
                ];
            } elseif ($currentRec && trim($line)) {
                $currentRec['description'] .= " " . trim($line);
            }
        }
        
        if ($currentRec) {
            $recommendations[] = $currentRec;
        }

        return $recommendations;
    }

    private function getDefaultRecommendations(): array
    {
        return [
            [
                'title' => 'Braking Analysis',
                'description' => 'Consider optimizing your braking points based on historical data patterns.'
            ],
            [
                'title' => 'Speed Management',
                'description' => 'Focus on maintaining consistent speed through technical sections.'
            ],
            [
                'title' => 'General Improvement',
                'description' => 'Review your overall racing line for potential optimization opportunities.'
            ]
        ];
    }

    public function enrichInternalKnowledge(array $data): void
    {
        $prompt = "Based on the following telemetry data and expert feedback, generate additional insights:\n\n";
        $prompt .= json_encode($data, JSON_PRETTY_PRINT);

        try {
            $response = $this->client->chat()->create([
                'model' => $this->config['openai']['model'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an AI system designed to enhance motorcycle racing telemetry knowledge. Generate insights that can be stored for future reference.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => $this->config['openai']['max_tokens']
            ]);

            // Store the enriched knowledge in the database
            $this->storeEnrichedKnowledge($response->choices[0]->message->content);
        } catch (\Exception $e) {
            error_log("OpenAI API Error during knowledge enrichment: " . $e->getMessage());
        }
    }

    private function storeEnrichedKnowledge(string $insights): void
    {
        try {
            $db = new \PDO(
                "mysql:host={$this->config['database']['host']};dbname={$this->config['database']['database']}",
                $this->config['database']['username'],
                $this->config['database']['password']
            );

            $stmt = $db->prepare("
                INSERT INTO ia_internal_knowledge (categorie, question, reponse, confiance)
                VALUES (?, ?, ?, ?)
            ");

            // Parse insights and store them
            $insights = json_decode($insights, true);
            foreach ($insights as $insight) {
                $stmt->execute([
                    $insight['category'] ?? 'general',
                    $insight['question'] ?? '',
                    $insight['answer'] ?? '',
                    $insight['confidence'] ?? 0.8
                ]);
            }
        } catch (\Exception $e) {
            error_log("Database Error during knowledge storage: " . $e->getMessage());
        }
    }
} 