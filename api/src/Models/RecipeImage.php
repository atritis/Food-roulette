<?php

namespace App\Models;

use App\Database;
use PDO;

class RecipeImage
{
    public static function getForRecipe(int $recipeId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT id, image_path, sort_order FROM recipe_images WHERE recipe_id = ? ORDER BY sort_order, id"
        );
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    public static function create(int $recipeId, string $imagePath, int $sortOrder = 0): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO recipe_images (recipe_id, image_path, sort_order) VALUES (?, ?, ?)"
        );
        $stmt->execute([$recipeId, $imagePath, $sortOrder]);
        return (int)$pdo->lastInsertId();
    }

    public static function delete(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, recipe_id, image_path FROM recipe_images WHERE id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetch();

        if (!$image) {
            return null;
        }

        $pdo->prepare("DELETE FROM recipe_images WHERE id = ?")->execute([$id]);
        return $image;
    }

    public static function deleteAllForRecipe(int $recipeId): array
    {
        $images = self::getForRecipe($recipeId);
        $pdo = Database::getConnection();
        $pdo->prepare("DELETE FROM recipe_images WHERE recipe_id = ?")->execute([$recipeId]);
        return $images;
    }
}
