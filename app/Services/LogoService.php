<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LogoService
{
    public const VARIANT_KEYS = [
        'logo_original',
        'logo_large',
        'logo_medium',
        'logo_small',
        'logo_favicon',
        'logo_transparent',
        'logo_seal',
        'logo_grayscale',
        'logo_watermark',
        'logo_primary',
        'logo_compact',
    ];

    public function generateVariants(UploadedFile|string $file): array
    {
        $rawBytes = $file instanceof UploadedFile ? $file->get() : file_get_contents($file);
        if (! $rawBytes) {
            return [];
        }

        $source = @imagecreatefromstring($rawBytes);
        if (! $source instanceof GdImage) {
            return [];
        }

        $srcW = imagesx($source);
        $srcH = imagesy($source);
        $token = Str::random(12);

        $results = [];

        $origPath = "logos/original_{$token}.png";
        $origContent = $this->renderPng($source);
        Storage::disk('public')->put($origPath, $origContent);
        $results['logo_original'] = $origPath;

        $large = $this->resizeKeepingRatio($source, $srcW, $srcH, 512, 512);
        $largePath = "logos/large_{$token}.png";
        Storage::disk('public')->put($largePath, $this->renderPng($large));
        $results['logo_large'] = $largePath;
        $results['logo_primary'] = $largePath;

        $medium = $this->resizeKeepingRatio($source, $srcW, $srcH, 256, 256);
        $mediumPath = "logos/medium_{$token}.png";
        Storage::disk('public')->put($mediumPath, $this->renderPng($medium));
        $results['logo_medium'] = $mediumPath;

        $small = $this->resizeKeepingRatio($source, $srcW, $srcH, 64, 64);
        $smallPath = "logos/small_{$token}.png";
        Storage::disk('public')->put($smallPath, $this->renderPng($small));
        $results['logo_small'] = $smallPath;
        $results['logo_compact'] = $smallPath;

        $favicon = $this->resizeKeepingRatio($source, $srcW, $srcH, 32, 32);
        $favPath = "logos/favicon_{$token}.png";
        Storage::disk('public')->put($favPath, $this->renderPng($favicon));
        $results['logo_favicon'] = $favPath;

        $transparent = $this->createTransparentVariant($source, $srcW, $srcH);
        $transPath = "logos/transparent_{$token}.png";
        Storage::disk('public')->put($transPath, $this->renderPng($transparent));
        $results['logo_transparent'] = $transPath;

        $seal = $this->createCircularSealVariant($source, $srcW, $srcH);
        $sealPath = "logos/seal_{$token}.png";
        Storage::disk('public')->put($sealPath, $this->renderPng($seal));
        $results['logo_seal'] = $sealPath;

        $gray = $this->createGrayscaleVariant($source, $srcW, $srcH);
        $grayPath = "logos/grayscale_{$token}.png";
        Storage::disk('public')->put($grayPath, $this->renderPng($gray));
        $results['logo_grayscale'] = $grayPath;

        $watermark = $this->createWatermarkVariant($source, $srcW, $srcH);
        $wmPath = "logos/watermark_{$token}.png";
        Storage::disk('public')->put($wmPath, $this->renderPng($watermark));
        $results['logo_watermark'] = $wmPath;

        imagedestroy($source);
        imagedestroy($large);
        imagedestroy($medium);
        imagedestroy($small);
        imagedestroy($favicon);
        imagedestroy($transparent);
        imagedestroy($seal);
        imagedestroy($gray);
        imagedestroy($watermark);

        return $results;
    }

    public function deleteExistingVariants(array $settings): void
    {
        foreach (self::VARIANT_KEYS as $key) {
            $path = $settings[$key] ?? null;
            if ($path && is_string($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    protected function resizeKeepingRatio(GdImage $src, int $srcW, int $srcH, int $maxW, int $maxH): GdImage
    {
        $ratio = min($maxW / $srcW, $maxH / $srcH);
        $targetW = max(1, (int) round($srcW * $ratio));
        $targetH = max(1, (int) round($srcH * $ratio));

        $dest = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
        imagefilledrectangle($dest, 0, 0, $targetW, $targetH, $transparent);
        imagealphablending($dest, true);

        imagecopyresampled($dest, $src, 0, 0, 0, 0, $targetW, $targetH, $srcW, $srcH);

        return $dest;
    }

    protected function createTransparentVariant(GdImage $src, int $srcW, int $srcH): GdImage
    {
        $dest = $this->resizeKeepingRatio($src, $srcW, $srcH, 512, 512);
        $w = imagesx($dest);
        $h = imagesy($dest);

        imagealphablending($dest, false);
        imagesavealpha($dest, true);

        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgba = imagecolorat($dest, $x, $y);
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;
                $alpha = ($rgba >> 24) & 0x7F;

                if ($alpha < 100 && $r >= 242 && $g >= 242 && $b >= 242) {
                    $newColor = imagecolorallocatealpha($dest, 255, 255, 255, 127);
                    imagesetpixel($dest, $x, $y, $newColor);
                }
            }
        }

        return $dest;
    }

    protected function createCircularSealVariant(GdImage $src, int $srcW, int $srcH): GdImage
    {
        return $this->resizeKeepingRatio($src, $srcW, $srcH, 512, 512);
    }

    protected function createGrayscaleVariant(GdImage $src, int $srcW, int $srcH): GdImage
    {
        $dest = $this->resizeKeepingRatio($src, $srcW, $srcH, 512, 512);
        imagefilter($dest, IMG_FILTER_GRAYSCALE);
        imagefilter($dest, IMG_FILTER_CONTRAST, -5);

        return $dest;
    }

    protected function createWatermarkVariant(GdImage $src, int $srcW, int $srcH): GdImage
    {
        $dest = $this->resizeKeepingRatio($src, $srcW, $srcH, 512, 512);
        $w = imagesx($dest);
        $h = imagesy($dest);

        $wm = imagecreatetruecolor($w, $h);
        imagealphablending($wm, false);
        imagesavealpha($wm, true);
        $trans = imagecolorallocatealpha($wm, 255, 255, 255, 127);
        imagefilledrectangle($wm, 0, 0, $w, $h, $trans);

        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgba = imagecolorat($dest, $x, $y);
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;
                $origAlpha = ($rgba >> 24) & 0x7F;

                if ($origAlpha < 127) {
                    $newAlpha = min(127, (int) round($origAlpha + (127 - $origAlpha) * 0.85));
                    $color = imagecolorallocatealpha($wm, $r, $g, $b, $newAlpha);
                    imagesetpixel($wm, $x, $y, $color);
                }
            }
        }

        imagedestroy($dest);

        return $wm;
    }

    protected function renderPng(GdImage $image): string
    {
        ob_start();
        imagepng($image, null, 8);

        return (string) ob_get_clean();
    }
}
