<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class BearerAuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');

        // No auth header → allow through (SPA usage)
        if (empty($authHeader)) {
            return $handler->handle($request);
        }

        // Has auth header → must be valid Bearer token
        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $this->unauthorized('Invalid authorization header format');
        }

        $settings = require __DIR__ . '/../../config/settings.php';
        $token = $matches[1];

        if (!hash_equals($settings['bearer_token'], $token)) {
            return $this->unauthorized('Invalid bearer token');
        }

        return $handler->handle($request);
    }

    private function unauthorized(string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
