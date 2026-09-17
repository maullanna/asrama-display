<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Membuat thumbnail kecil (JPEG) dari foto agar ringan di layar TV.
 * Foto asli bisa ~2 MB; thumbnail ~220px cukup untuk kotak foto kiosk.
 */
class Thumbnailer
{
    /** Sisi terpanjang thumbnail (px). */
    public const SIZE = 220;

    /** Sub-folder thumbnail di dalam disk public. */
    public const DIR = 'thumbs';

    /**
     * Path relatif thumbnail (disk public) untuk sebuah foto asli.
     * mis. "students/abc.png" -> "students/thumbs/abc.jpg"
     */
    public static function thumbPathFor(string $photoPath): string
    {
        $dir = trim(pathinfo($photoPath, PATHINFO_DIRNAME), '.');
        $base = pathinfo($photoPath, PATHINFO_FILENAME);
        $prefix = $dir !== '' ? $dir.'/' : '';

        return $prefix.self::DIR.'/'.$base.'.jpg';
    }

    /**
     * Buat thumbnail JPEG dari foto asli (disk public).
     *
     * @return string|null path relatif thumbnail, atau null bila gagal.
     */
    public static function generate(string $photoPath, bool $force = false): ?string
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($photoPath)) {
            return null;
        }

        $thumbPath = self::thumbPathFor($photoPath);
        if (! $force && $disk->exists($thumbPath)) {
            return $thumbPath;
        }

        $srcAbs = $disk->path($photoPath);
        $info = @getimagesize($srcAbs);
        if (! $info) {
            return null;
        }

        [$w, $h] = $info;
        $src = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($srcAbs),
            IMAGETYPE_PNG => @imagecreatefrompng($srcAbs),
            IMAGETYPE_WEBP => @imagecreatefromwebp($srcAbs),
            IMAGETYPE_GIF => @imagecreatefromgif($srcAbs),
            default => null,
        };
        if (! $src) {
            return null;
        }

        $scale = min(1, self::SIZE / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));

        $dst = imagecreatetruecolor($nw, $nh);
        $white = imagecolorallocate($dst, 255, 255, 255); // latar putih untuk PNG transparan
        imagefilledrectangle($dst, 0, 0, $nw, $nh, $white);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $disk->makeDirectory(pathinfo($thumbPath, PATHINFO_DIRNAME));
        imagejpeg($dst, $disk->path($thumbPath), 72);

        imagedestroy($src);
        imagedestroy($dst);

        return $thumbPath;
    }
}
