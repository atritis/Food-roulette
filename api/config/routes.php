<?php

use App\Controllers\RecipeController;
use App\Controllers\ImageController;
use App\Controllers\TagController;
use App\Controllers\IngredientController;
use App\Middleware\BearerAuthMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->group('/api', function (RouteCollectorProxy $group) {
        // Recipes
        $group->get('/recipes/random', [RecipeController::class, 'random']);
        $group->get('/recipes/search', [RecipeController::class, 'search']);
        $group->get('/recipes/{id:[0-9]+}', [RecipeController::class, 'show']);
        $group->post('/recipes', [RecipeController::class, 'create'])
            ->add(BearerAuthMiddleware::class);
        $group->put('/recipes/{id:[0-9]+}', [RecipeController::class, 'update']);
        $group->delete('/recipes/{id:[0-9]+}', [RecipeController::class, 'delete']);

        // Images
        $group->post('/recipes/{id:[0-9]+}/images', [ImageController::class, 'upload']);
        $group->delete('/images/{id:[0-9]+}', [ImageController::class, 'delete']);

        // Tags
        $group->get('/tags', [TagController::class, 'index']);

        // Ingredients
        $group->get('/ingredients', [IngredientController::class, 'search']);
    });
};
