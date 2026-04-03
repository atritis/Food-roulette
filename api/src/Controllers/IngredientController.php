<?php

namespace App\Controllers;

use App\Models\Ingredient;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IngredientController
{
    public function search(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query = trim($params['q'] ?? '');

        if (strlen($query) < 1) {
            $response->getBody()->write(json_encode([]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $ingredients = Ingredient::search($query);
        $response->getBody()->write(json_encode($ingredients, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
