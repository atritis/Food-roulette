<?php

namespace App\Controllers;

use App\Models\Recipe;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RecipeController
{
    public function random(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $count = min((int)($params['count'] ?? 2), 10);
        $recipes = Recipe::random($count);

        return $this->json($response, $recipes);
    }

    public function search(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query = trim($params['q'] ?? '');

        if (strlen($query) < 3) {
            return $this->json($response, []);
        }

        $inTitle = ($params['in_title'] ?? '1') === '1';
        $inIngredients = ($params['in_ingredients'] ?? '1') === '1';
        $inTags = ($params['in_tags'] ?? '1') === '1';

        $recipes = Recipe::search($query, $inTitle, $inIngredients, $inTags);

        return $this->json($response, $recipes);
    }

    public function show(Request $request, Response $response, string $id): Response
    {
        $recipe = Recipe::findById((int)$id);

        if (!$recipe) {
            return $this->json($response, ['error' => 'Recipe not found'], 404);
        }

        return $this->json($response, $recipe);
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        if (empty($data['title'])) {
            return $this->json($response, ['error' => 'Title is required'], 422);
        }

        $recipeId = Recipe::create($data);
        $recipe = Recipe::findById($recipeId);

        return $this->json($response, $recipe, 201);
    }

    public function update(Request $request, Response $response, string $id): Response
    {
        $id = (int)$id;
        $existing = Recipe::findById($id);

        if (!$existing) {
            return $this->json($response, ['error' => 'Recipe not found'], 404);
        }

        $data = $request->getParsedBody();
        Recipe::update($id, $data);
        $recipe = Recipe::findById($id);

        return $this->json($response, $recipe);
    }

    public function delete(Request $request, Response $response, string $id): Response
    {
        $id = (int)$id;

        if (!Recipe::findById($id)) {
            return $this->json($response, ['error' => 'Recipe not found'], 404);
        }

        Recipe::delete($id);

        return $this->json($response, ['message' => 'Recipe deleted']);
    }

    private function json(Response $response, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
