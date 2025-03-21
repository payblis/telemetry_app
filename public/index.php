<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize request
$request = Request::createFromGlobals();
$response = new Response();

// Load configuration
$config = require __DIR__ . '/../app/Config/config.php';

// Set up error handling
if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Initialize routing
require __DIR__ . '/../app/routes.php';

$context = new RequestContext();
$context->fromRequest($request);
$matcher = new UrlMatcher($routes, $context);

try {
    $parameters = $matcher->match($request->getPathInfo());
    $controller = $parameters['_controller'];
    $response = $controller($request, $response, $parameters);
} catch (ResourceNotFoundException $e) {
    $response->setStatusCode(404);
    $response->setContent('Page not found');
} catch (\Exception $e) {
    if ($config['app']['debug']) {
        $response->setStatusCode(500);
        $response->setContent($e->getMessage());
    } else {
        $response->setStatusCode(500);
        $response->setContent('An error occurred');
    }
}

// Send response
$response->send(); 