<?php

namespace App\Models;

use App\Database;
use PDO;

class Ingredient
{
    public static function search(string $query): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, name FROM ingredients WHERE name LIKE ? ORDER BY name LIMIT 20");
        $stmt->execute(["%{$query}%"]);
        return $stmt->fetchAll();
    }

    public static function findOrCreate(string $name): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM ingredients WHERE name = ?");
        $stmt->execute([$name]);
        $row = $stmt->fetch();

        if ($row) {
            return (int)$row['id'];
        }

        $stmt = $pdo->prepare("INSERT INTO ingredients (name) VALUES (?)");
        $stmt->execute([$name]);
        return (int)$pdo->lastInsertId();
    }

    public static function getForRecipe(int $recipeId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT ri.id, i.name, ri.quantity, ri.unit
             FROM recipe_ingredients ri
             JOIN ingredients i ON i.id = ri.ingredient_id
             WHERE ri.recipe_id = ?
             ORDER BY ri.id"
        );
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    public static function syncForRecipe(int $recipeId, array $ingredients): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare("DELETE FROM recipe_ingredients WHERE recipe_id = ?")->execute([$recipeId]);

        $stmt = $pdo->prepare(
            "INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit) VALUES (?, ?, ?, ?)"
        );
        foreach ($ingredients as $ing) {
            $ingredientId = self::findOrCreate($ing['name']);
            $stmt->execute([
                $recipeId,
                $ingredientId,
                $ing['quantity'] ?? null,
                $ing['unit'] ?? null,
            ]);
        }
    }
}
