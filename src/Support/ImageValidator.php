<?php

namespace App\Support;

use App\Exceptions\ValidationException;
use finfo;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageValidator
{
    private const MAX_SIZE = 2 * 1024 * 1024;

    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public static function validate(?UploadedFile $image): ?string
    {
        if ($image === null) return null;

        $size = $image->isValid() ? $image->getSize() : null;

        if ($size === null || $size <= 0 || $size > self::MAX_SIZE) {
            throw new ValidationException(['image' => 'validation.product.image']);
        }

        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($image->getPathname());

        $extension = self::ALLOWED_TYPES[$mimeType] ?? null;

        if ($extension === null) {
            throw new ValidationException(['image' => 'validation.product.image']);
        }

        return $extension;
    }
}
