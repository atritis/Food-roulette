<?php

namespace App\Controllers;

use App\Models\Recipe;
use App\Models\RecipeImage;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ImageController
{
    public function upload(Request $request, Response $response, array $args): Response
    {
        $recipeId = (int)$args['id'];

        if (!Recipe::findById($recipeId)) {
            return $this->json($response, ['error' => 'Recipe not found'], 404);
        }

        $settings = require __DIR__ . '/../../config/settings.php';
        $uploadPath = $settings['upload_path'] . '/' . $recipeId;

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $uploadedFiles = $request->getUploadedFiles();
        $images = $uploadedFiles['images'] ?? [];

        if (!is_array($images)) {
            $images = [$images];
        }

        $created = [];
        $sortOrder = 0;

        foreach ($images as $file) {
            if ($file->getError() !== UPLOAD_ERR_OK) {
                continue;
            }

            $ext = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array(strtolower($ext), $allowed)) {
                continue;
            }

            $filename = bin2hex(random_bytes(8)) . '.' . strtolower($ext);
            $filepath = $uploadPath . '/' . $filename;
            $file->moveTo($filepath);

            // Resize if needed
            $this->resizeImage($filepath, $settings['max_image_width']);

            $relativePath = $recipeId . '/' . $filename;
            $imageId = RecipeImage::create($recipeId, $relativePath, $sortOrder++);
            $created[] = [
                'id' => $imageId,
                'image_path' => $relativePath,
                'sort_order' => $sortOrder - 1,
            ];
        }

        return $this->json($response, $created, 201);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $imageId = (int)$args['id'];
        $image = RecipeImage::delete($imageId);

        if (!$image) {
            return $this->json($response, ['error' => 'Image not found'], 404);
        }

        // Delete file from disk
        $settings = require __DIR__ . '/../../config/settings.php';
        $filepath = $settings['upload_path'] . '/' . $image['image_path'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        return $this->json($response, ['message' => 'Image deleted']);
    }

    private function resizeImage(string $filepath, int $maxWidth): void
    {
        $info = getimagesize($filepath);
        if (!$info || $info[0] <= $maxWidth) {
            return;
        }

        $mime = $info['mime'];
        $src = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($filepath),
            'image/png' => imagecreatefrompng($filepath),
            'image/webp' => imagecreatefromwebp($filepath),
            'image/gif' => imagecreatefromgif($filepath),
            default => null,
        };

        if (!$src) {
            return;
        }

        $origW = $info[0];
        $origH = $info[1];
        $newW = $maxWidth;
        $newH = (int)round($origH * ($maxWidth / $origW));

        $dst = imagecreatetruecolor($newW, $newH);

        // Preserve transparency for PNG and WebP
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        match ($mime) {
            'image/jpeg' => imagejpeg($dst, $filepath, 85),
            'image/png' => imagepng($dst, $filepath, 8),
            'image/webp' => imagewebp($dst, $filepath, 85),
            'image/gif' => imagegif($dst, $filepath),
            default => null,
        };

        imagedestroy($src);
        imagedestroy($dst);
    }

    private function json(Response $response, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
