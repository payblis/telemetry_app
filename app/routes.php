<?php

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PiloteController;
use App\Controllers\MotoController;
use App\Controllers\CircuitController;
use App\Controllers\SessionController;
use App\Controllers\TelemetrieController;
use App\Controllers\IAController;

$routes = new RouteCollection();

// Auth routes
$routes->add('login', new Route('/login', [
    '_controller' => [new AuthController(), 'login']
]));
$routes->add('register', new Route('/register', [
    '_controller' => [new AuthController(), 'register']
]));
$routes->add('logout', new Route('/logout', [
    '_controller' => [new AuthController(), 'logout']
]));

// Dashboard routes
$routes->add('dashboard', new Route('/', [
    '_controller' => [new DashboardController(), 'index']
]));

// Pilotes routes
$routes->add('pilotes', new Route('/pilotes', [
    '_controller' => [new PiloteController(), 'index']
]));
$routes->add('pilote_create', new Route('/pilotes/create', [
    '_controller' => [new PiloteController(), 'create']
]));
$routes->add('pilote_edit', new Route('/pilotes/{id}/edit', [
    '_controller' => [new PiloteController(), 'edit']
]));

// Motos routes
$routes->add('motos', new Route('/motos', [
    '_controller' => [new MotoController(), 'index']
]));
$routes->add('moto_create', new Route('/motos/create', [
    '_controller' => [new MotoController(), 'create']
]));
$routes->add('moto_edit', new Route('/motos/{id}/edit', [
    '_controller' => [new MotoController(), 'edit']
]));

// Circuits routes
$routes->add('circuits', new Route('/circuits', [
    '_controller' => [new CircuitController(), 'index']
]));
$routes->add('circuit_create', new Route('/circuits/create', [
    '_controller' => [new CircuitController(), 'create']
]));
$routes->add('circuit_edit', new Route('/circuits/{id}/edit', [
    '_controller' => [new CircuitController(), 'edit']
]));

// Sessions routes
$routes->add('sessions', new Route('/sessions', [
    '_controller' => [new SessionController(), 'index']
]));
$routes->add('session_create', new Route('/sessions/create', [
    '_controller' => [new SessionController(), 'create']
]));
$routes->add('session_view', new Route('/sessions/{id}', [
    '_controller' => [new SessionController(), 'view']
]));

// Telemetry routes
$routes->add('telemetrie_import', new Route('/telemetrie/import', [
    '_controller' => [new TelemetrieController(), 'import']
]));
$routes->add('telemetrie_analyse', new Route('/telemetrie/{session_id}/analyse', [
    '_controller' => [new TelemetrieController(), 'analyse']
]));

// AI routes
$routes->add('ia_analyse', new Route('/ia/analyse', [
    '_controller' => [new IAController(), 'analyse']
], [], [], '', [], ['POST']));
$routes->add('ia_feedback', new Route('/ia/feedback', [
    '_controller' => [new IAController(), 'feedback']
], [], [], '', [], ['POST'])); 