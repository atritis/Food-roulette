<?php

namespace App\Models;

use App\Database;
use PDO;

class Recipe
{
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = ?");
        $stmt->execute([$id]);
        $recipe = $stmt->fetch();

        if (!$recipe) {
            return null;
        }

        return self::loadRelations($recipe);
    }

    public static function random(int $count = 2): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM recipes ORDER BY RAND() LIMIT ?");
        $stmt->bindValue(1, $count, PDO::PARAM_INT);
        $stmt->execute();
        $recipes = $stmt->fetchAll();

        return array_map([self::class, 'loadRelations'], $recipes);
    }

    public static function search(string $query, bool $inTitle = true, bool $inIngredients = true, bool $inTags = true): array
    {
        $pdo = Database::getConnection();
        $results = [];
        $seen = [];

        // Priority 1: Exact title matches, then partial title matches
        if ($inTitle) {
            // Exact
            $stmt = $pdo->prepare(
                "SELECT r.*, 1 as priority, 1 as is_exact FROM recipes r WHERE r.title = ? ORDER BY r.created_at DESC"
            );
            $stmt->execute([$query]);
            foreach ($stmt->fetchAll() as $row) {
                if (!isset($seen[$row['id']])) {
                    $results[] = $row;
                    $seen[$row['id']] = true;
                }
            }

            // Partial
            $stmt = $pdo->prepare(
                "SELECT r.*, 1 as priority, 0 as is_exact FROM recipes r WHERE r.title LIKE ? AND r.title != ? ORDER BY r.created_at DESC"
            );
            $stmt->execute(["%{$query}%", $query]);
            foreach ($stmt->fetchAll() as $row) {
                if (!isset($seen[$row['id']])) {
                    $results[] = $row;
                    $seen[$row['id']] = true;
                }
            }
        }

        // Priority 2: Exact tag matches, then partial tag matches
        if ($inTags) {
            // Exact
            $stmt = $pdo->prepare(
                "SELECT DISTINCT r.*, 2 as priority, 1 as is_exact FROM recipes r
                 JOIN recipe_tags rt ON rt.recipe_id = r.id
                 JOIN tags t ON t.id = rt.tag_id
                 WHERE t.name = ?
                 ORDER BY r.created_at DESC"
            );
            $stmt->execute([$query]);
            foreach ($stmt->fetchAll() as $row) {
                if (!isset($seen[$row['id']])) {
                    $results[] = $row;
                    $seen[$row['id']] = true;
                }
            }

            // Partial
            $stmt = $pdo->prepare(
                "SELECT DISTINCT r.*, 2 as priority, 0 as is_exact FROM recipes r
                 JOIN recipe_tags rt ON rt.recipe_id = r.id
                 JOIN tags t ON t.id = rt.tag_id
                 WHERE t.name LIKE ? AND t.name != ?
                 ORDER BY r.created_at DESC"
            );
            $stmt->execute(["%{$query}%", $query]);
            foreach ($stmt->fetchAll() as $row) {
                if (!isset($seen[$row['id']])) {
                    $results[] = $row;
                    $seen[$row['id']] = true;
                }
            }
        }

        // Priority 3: Exact ingredient matches, then partial ingredient matches
        if ($inIngredients) {
            // Exact
            $stmt = $pdo->prepare(
                "SELECT DISTINCT r.*, 3 as priority, 1 as is_exact FROM recipes r
                 JOIN recipe_ingredients ri ON ri.recipe_id = r.id
                 JOIN ingredients i ON i.id = ri.ingredient_id
                 WHERE i.name = ?
                 ORDER BY r.created_at DESC"
            );
            $stmt->execute([$query]);
            foreach ($stmt->fetchAll() as $row) {
                if (!isset($seen[$row['id']])) {
                    $results[] = $row;
                    $seen[$row['id']] = true;
                }
            }

            // Partial
            $stmt = $pdo->prepare(
                "SELECT DISTINCT r.*, 3 as priority, 0 as is_exact FROM recipes r
                 JOIN recipe_ingredients ri ON ri.recipe_id = r.id
                 JOIN ingredients i ON i.id = ri.ingredient_id
                 WHERE i.name LIKE ? AND i.name != ?
                 ORDER BY r.created_at DESC"
            );
            $stmt->execute(["%{$query}%", $query]);
            foreach ($stmt->fetchAll() as $row) {
                if (!isset($seen[$row['id']])) {
                    $results[] = $row;
                    $seen[$row['id']] = true;
                }
            }
        }

        // Load relations for each result
        return array_map(function ($row) {
            unset($row['priority'], $row['is_exact']);
            return self::loadRelations($row);
        }, $results);
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO recipes (title, instructions, prep_time, cook_time, servings)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['title'],
            $data['instructions'] ?? '',
            $data['prep_time'] ?? 0,
            $data['cook_time'] ?? 0,
            $data['servings'] ?? 4,
        ]);

        $recipeId = (int)$pdo->lastInsertId();

        if (!empty($data['tags'])) {
            Tag::syncForRecipe($recipeId, $data['tags']);
        }

        if (!empty($data['ingredients'])) {
            Ingredient::syncForRecipe($recipeId, $data['ingredients']);
        }

        return $recipeId;
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();

        $fields = [];
        $values = [];

        foreach (['title', 'instructions', 'prep_time', 'cook_time', 'servings'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $values[] = $id;
            $stmt = $pdo->prepare("UPDATE recipes SET " . implode(', ', $fields) . " WHERE id = ?");
            $stmt->execute($values);
        }

        if (array_key_exists('tags', $data)) {
            Tag::syncForRecipe($id, $data['tags']);
        }

        if (array_key_exists('ingredients', $data)) {
            Ingredient::syncForRecipe($id, $data['ingredients']);
        }

        return true;
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();

        // Get images to delete files
        $images = RecipeImage::deleteAllForRecipe($id);

        $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            // Clean up image files
            $settings = require __DIR__ . '/../../config/settings.php';
            $uploadPath = $settings['upload_path'];
            $recipeDir = $uploadPath . '/' . $id;
            if (is_dir($recipeDir)) {
                $files = glob($recipeDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                rmdir($recipeDir);
            }
            return true;
        }

        return false;
    }

    private static function loadRelations(array $recipe): array
    {
        $id = (int)$recipe['id'];
        $recipe['images'] = RecipeImage::getForRecipe($id);
        $recipe['tags'] = Tag::getForRecipe($id);
        $recipe['ingredients'] = Ingredient::getForRecipe($id);
        return $recipe;
    }
}
