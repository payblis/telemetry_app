<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class Controller
{
    protected $request;
    protected $response;
    protected $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../Config/config.php';
    }

    protected function render(string $view, array $data = []): Response
    {
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View {$view} not found");
        }

        ob_start();
        extract($data);
        require $viewPath;
        $content = ob_get_clean();

        $response = new Response();
        $response->setContent($content);
        return $response;
    }

    protected function json($data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }

    protected function redirect(string $route): Response
    {
        $response = new Response();
        $response->headers->set('Location', $route);
        $response->setStatusCode(302);
        return $response;
    }

    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
            exit;
        }
    }

    protected function getCurrentUser(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        // TODO: Implement user retrieval from database
        return [
            'id' => $_SESSION['user_id'],
            'role' => $_SESSION['user_role'] ?? 'user'
        ];
    }

    protected function hasRole(string $role): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }

    protected function validateCSRF(): void
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new \Exception('CSRF token validation failed');
        }
    }

    protected function generateCSRFToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
} 