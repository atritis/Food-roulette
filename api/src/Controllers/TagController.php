<?php

namespace App\Controllers;

use App\Models\Tag;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TagController
{
    public function index(Request $request, Response $response): Response
    {
        $tags = Tag::getAll();
        $response->getBody()->write(json_encode($tags, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
