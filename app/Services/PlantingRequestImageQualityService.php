<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

/**
 * Local image quality gates for planting-request photos (no OCR credits burned).
 */
class PlantingRequestImageQualityService
{
    private const MIN_SHORT_SIDE = 800;

    /** Laplacian variance below this → treat as too blurry. */
    private const MIN_LAPLACIAN_VARIANCE = 80.0;

    private const MIN_BRIGHTNESS = 35.0;

    private const MAX_BRIGHTNESS = 230.0;

    /**
     * @return array{accepted: bool, score: float|null, reasons: list<string>}
     */
    public function assess(UploadedFile $file): array
    {
        if (! function_exists('imagecreatefromstring')) {
            return [
                'accepted' => false,
                'score' => null,
                'reasons' => ['Photo analysis requires PHP GD on the server.'],
            ];
        }

        $binary = @file_get_contents($file->getRealPath());
        if ($binary === false || $binary === '') {
            return [
                'accepted' => false,
                'score' => null,
                'reasons' => ['Could not read the uploaded photo.'],
            ];
        }

        $image = @imagecreatefromstring($binary);
        if ($image === false) {
            return [
                'accepted' => false,
                'score' => null,
                'reasons' => ['The file is not a readable image. Use JPG, PNG, or WEBP.'],
            ];
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $shortSide = min($width, $height);
        $reasons = [];

        if ($shortSide < self::MIN_SHORT_SIDE) {
            $reasons[] = 'Photo is too low resolution — use a clearer, closer shot (at least '
                .self::MIN_SHORT_SIDE.'px on the short side).';
        }

        // Work on a downscaled grayscale copy for speed.
        $maxDim = 640;
        $scale = min(1.0, $maxDim / max($width, $height));
        $w = max(1, (int) round($width * $scale));
        $h = max(1, (int) round($height * $scale));
        $sample = imagecreatetruecolor($w, $h);
        imagecopyresampled($sample, $image, 0, 0, 0, 0, $w, $h, $width, $height);
        imagedestroy($image);

        $brightness = $this->meanBrightness($sample);
        if ($brightness < self::MIN_BRIGHTNESS) {
            $reasons[] = 'Photo is too dark to read — retake in better lighting.';
        } elseif ($brightness > self::MAX_BRIGHTNESS) {
            $reasons[] = 'Photo is too bright/washed out to read — retake with less glare.';
        }

        $variance = $this->laplacianVariance($sample);
        imagedestroy($sample);

        if ($variance < self::MIN_LAPLACIAN_VARIANCE) {
            $reasons[] = 'Photo is too blurry — retake a clearer, focused picture of the form.';
        }

        return [
            'accepted' => $reasons === [],
            'score' => round($variance, 2),
            'reasons' => $reasons,
        ];
    }

    private function meanBrightness(\GdImage $image): float
    {
        $w = imagesx($image);
        $h = imagesy($image);
        $sum = 0.0;
        $count = 0;
        $step = max(1, (int) floor(min($w, $h) / 80));

        for ($y = 0; $y < $h; $y += $step) {
            for ($x = 0; $x < $w; $x += $step) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $sum += (0.299 * $r) + (0.587 * $g) + (0.114 * $b);
                $count++;
            }
        }

        return $count > 0 ? $sum / $count : 0.0;
    }

    private function laplacianVariance(\GdImage $image): float
    {
        $w = imagesx($image);
        $h = imagesy($image);

        // Convert to grayscale in-place on a copy.
        $gray = imagecreatetruecolor($w, $h);
        imagecopy($gray, $image, 0, 0, 0, 0, $w, $h);
        imagefilter($gray, IMG_FILTER_GRAYSCALE);

        $matrix = [
            [0, 1, 0],
            [1, -4, 1],
            [0, 1, 0],
        ];
        imageconvolution($gray, $matrix, 1, 128);

        $sum = 0.0;
        $sumSq = 0.0;
        $count = 0;
        $step = max(1, (int) floor(min($w, $h) / 100));

        for ($y = 1; $y < $h - 1; $y += $step) {
            for ($x = 1; $x < $w - 1; $x += $step) {
                $rgb = imagecolorat($gray, $x, $y);
                $v = (float) (($rgb >> 16) & 0xFF);
                $sum += $v;
                $sumSq += $v * $v;
                $count++;
            }
        }

        imagedestroy($gray);

        if ($count < 2) {
            return 0.0;
        }

        $mean = $sum / $count;

        return ($sumSq / $count) - ($mean * $mean);
    }
}
