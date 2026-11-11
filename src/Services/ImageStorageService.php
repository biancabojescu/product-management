<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageStorageService
{
    private const UPLOAD_DIRECTORY = '/uploads/products';

    public function store(UploadedFile $image, string $extension): string {
        $directory = dirname(__DIR__, 2) . '/public' . self::UPLOAD_DIRECTORY;

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Could not create upload directory.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $image->move($directory, $filename);

        return self::UPLOAD_DIRECTORY . '/' . $filename;
    }

    public function delete(?string $imagePath): void
    {
        if ($imagePath === null) return;

        $directory = realpath(dirname(__DIR__, 2) . '/public' . self::UPLOAD_DIRECTORY);

        if ($directory === false) return;

        $filename = basename($imagePath);

        if ($filename === '.' || $filename === '..' || $imagePath !== self::UPLOAD_DIRECTORY . '/' . $filename) {
            return;
        }

        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        if (is_file($path) && !is_link($path) && !unlink($path)) {
            throw new RuntimeException('Could not delete image.');
        }
    }
}
