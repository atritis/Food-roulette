<?php

namespace App\Models;

use App\Database;
use PDO;

class Tag
{
    public static function getAll(): array
    {
        $pdo = Database::getConnection();
        return $pdo->query("SELECT id, name, color FROM tags ORDER BY name")->fetchAll();
    }

    public static function search(string $query): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, name, color FROM tags WHERE name LIKE ? ORDER BY name LIMIT 20");
        $stmt->execute(["%{$query}%"]);
        return $stmt->fetchAll();
    }

    public static function findOrCreate(string $name, string $color = '#6366f1'): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, color FROM tags WHERE name = ?");
        $stmt->execute([$name]);
        $tag = $stmt->fetch();

        if ($tag) {
            // Update color if different
            if ($tag['color'] !== $color) {
                $pdo->prepare("UPDATE tags SET color = ? WHERE id = ?")->execute([$color, $tag['id']]);
            }
            return (int)$tag['id'];
        }

        $stmt = $pdo->prepare("INSERT INTO tags (name, color) VALUES (?, ?)");
        $stmt->execute([$name, $color]);
        return (int)$pdo->lastInsertId();
    }

    public static function getForRecipe(int $recipeId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT t.id, t.name, t.color FROM tags t
             JOIN recipe_tags rt ON rt.tag_id = t.id
             WHERE rt.recipe_id = ?
             ORDER BY t.name"
        );
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    public static function syncForRecipe(int $recipeId, array $tags): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare("DELETE FROM recipe_tags WHERE recipe_id = ?")->execute([$recipeId]);

        $stmt = $pdo->prepare("INSERT INTO recipe_tags (recipe_id, tag_id) VALUES (?, ?)");
        foreach ($tags as $tag) {
            $tagId = self::findOrCreate($tag['name'], $tag['color'] ?? '#6366f1');
            $stmt->execute([$recipeId, $tagId]);
        }
    }
}
