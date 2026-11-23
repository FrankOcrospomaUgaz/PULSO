<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class LandingAssetImage
{
    public const RELATIVE_DIR = 'LP-SUBUZ/dist/assets';

    public static function store(UploadedFile $image): string
    {
        $destinationPath = public_path(self::RELATIVE_DIR);

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $extension = strtolower($image->getClientOriginalExtension());
        $baseName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $baseName = Str::slug($baseName, '_') ?: 'imagen';

        $imageName = $baseName . ($extension !== '' ? '.' . $extension : '');
        $candidate = $imageName;
        $counter = 2;

        while (file_exists($destinationPath . DIRECTORY_SEPARATOR . $candidate)) {
            $candidate = $baseName . '_' . $counter . ($extension !== '' ? '.' . $extension : '');
            $counter++;
        }

        $image->move($destinationPath, $candidate);

        return self::RELATIVE_DIR . '/' . $candidate;
    }

    public static function fromExistingPath(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }

        $normalized = str_replace('\\', '/', $path);
        $relativeDir = self::RELATIVE_DIR . '/';

        if (str_starts_with($normalized, $relativeDir)) {
            return $normalized;
        }

        $filename = basename($normalized);
        $filename = preg_replace('/^\d+_/', '', $filename);

        return $relativeDir . $filename;
    }
}
