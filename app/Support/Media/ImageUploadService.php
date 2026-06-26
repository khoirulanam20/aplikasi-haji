<?php

namespace App\Support\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

class ImageUploadService
{
    public function store(UploadedFile $file, string $directory, array $options = []): string
    {
        $maxWidth = (int) ($options['max_width'] ?? 1024);
        $maxHeight = (int) ($options['max_height'] ?? 1024);
        $quality = (int) ($options['quality'] ?? 80);
        $oldPath = $options['old_path'] ?? null;

        $manager = new ImageManager(Driver::class);
        $image = $manager->decodePath($file->getPathname());
        $image->scaleDown(width: $maxWidth, height: $maxHeight);

        $filename = Str::uuid()->toString().'.webp';
        $relativePath = trim($directory, '/').'/'.$filename;

        $encoded = $image->encodeUsingFormat(Format::WEBP, quality: $quality);
        Storage::disk('public')->put($relativePath, (string) $encoded);

        if (is_string($oldPath) && $oldPath !== '' && $oldPath !== $relativePath) {
            $this->deleteIfExists($oldPath);
        }

        return $relativePath;
    }

    public function deleteIfExists(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return;
        }

        $normalized = ltrim($path, '/');

        if (Storage::disk('public')->exists($normalized)) {
            Storage::disk('public')->delete($normalized);
        }
    }
}
