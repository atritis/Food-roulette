<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Middleware\CorsMiddleware;
use DI\Bridge\Slim\Bridge;
use Slim\Middleware\BodyParsingMiddleware;

$app = Bridge::create();

// Set base path if deployed in a subdirectory (e.g. APP_BASE_PATH=/food-roulette)
$basePath = getenv('APP_BASE_PATH') ?: '';
if ($basePath) {
    $app->setBasePath($basePath);
}

// Middleware (order matters: last added = first executed)
$app->addBodyParsingMiddleware();
$app->add(new CorsMiddleware());
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->getDefaultErrorHandler()->forceContentType('application/json');

// Routes
$routes = require __DIR__ . '/../config/routes.php';
$routes($app);

// Serve uploaded images as static files
$app->get('/api/uploads/{path:.*}', function ($request, $response, $args) {
    $settings = require __DIR__ . '/../config/settings.php';
    $filepath = $settings['upload_path'] . '/' . $args['path'];

    if (!file_exists($filepath) || !is_file($filepath)) {
        $response->getBody()->write(json_encode(['error' => 'File not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $mime = mime_content_type($filepath);
    $stream = fopen($filepath, 'rb');
    $body = new \Slim\Psr7\Stream($stream);

    return $response
        ->withHeader('Content-Type', $mime)
        ->withHeader('Cache-Control', 'public, max-age=604800')
        ->withBody($body);
});

$app->run();
