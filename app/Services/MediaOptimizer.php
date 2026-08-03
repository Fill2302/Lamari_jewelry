<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class MediaOptimizer
{
    public const IMAGE_MAX_KILOBYTES = 8192;

    public const VIDEO_MAX_KILOBYTES = 51200;

    private const IMAGE_MAX_DIMENSION = 2400;

    private const IMAGE_WEBP_QUALITY = 84;

    public function supports(UploadedFile $file): bool
    {
        $mime = (string) $file->getMimeType();

        return str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/');
    }

    public function optimizeAndStore(
        UploadedFile $file,
        string $disk,
        ?string $directory,
        string $visibility = 'public',
    ): string {
        $mime = (string) $file->getMimeType();

        if (str_starts_with($mime, 'image/')) {
            return $this->optimizeImage($file, $disk, $directory, $visibility);
        }

        if (str_starts_with($mime, 'video/')) {
            return $this->optimizeVideo($file, $disk, $directory, $visibility);
        }

        throw new RuntimeException('Цей тип медіафайлу не підтримується.');
    }

    private function optimizeImage(UploadedFile $file, string $disk, ?string $directory, string $visibility): string
    {
        $this->assertSize($file, self::IMAGE_MAX_KILOBYTES, 'Фото перевищує дозволені 8 МБ.');

        $contents = file_get_contents($file->getRealPath());
        $image = $contents === false ? false : @imagecreatefromstring($contents);
        if ($image === false) {
            throw new RuntimeException('Не вдалося прочитати фотографію. Завантажте JPG, PNG або WebP.');
        }

        $output = $this->temporaryPath('webp');

        try {
            $image = $this->orientImage($image, $file);
            $width = imagesx($image);
            $height = imagesy($image);
            $scale = min(1, self::IMAGE_MAX_DIMENSION / max($width, $height));

            if ($scale < 1) {
                $resized = imagescale(
                    $image,
                    max(1, (int) round($width * $scale)),
                    max(1, (int) round($height * $scale)),
                    IMG_BICUBIC_FIXED,
                );
                if ($resized === false) {
                    throw new RuntimeException('Не вдалося зменшити фотографію.');
                }
                imagedestroy($image);
                $image = $resized;
            }

            imagealphablending($image, true);
            imagesavealpha($image, true);
            if (! imagewebp($image, $output, self::IMAGE_WEBP_QUALITY)) {
                throw new RuntimeException('Не вдалося створити оптимізовану WebP-копію.');
            }

            return $this->storeFile($output, $disk, $directory, 'webp', $visibility);
        } finally {
            imagedestroy($image);
            @unlink($output);
        }
    }

    private function optimizeVideo(UploadedFile $file, string $disk, ?string $directory, string $visibility): string
    {
        $this->assertSize($file, self::VIDEO_MAX_KILOBYTES, 'Відео перевищує дозволені 50 МБ.');

        $output = $this->temporaryPath('mp4');
        $process = new Process([
            '/usr/bin/ffmpeg',
            '-hide_banner',
            '-loglevel', 'error',
            '-y',
            '-i', $file->getRealPath(),
            '-map', '0:v:0',
            '-map', '0:a:0?',
            '-vf', "scale='min(1920,iw)':'min(1920,ih)':force_original_aspect_ratio=decrease:force_divisible_by=2,fps=30",
            '-c:v', 'libx264',
            '-preset', 'medium',
            '-crf', '23',
            '-maxrate', '4M',
            '-bufsize', '8M',
            '-pix_fmt', 'yuv420p',
            '-movflags', '+faststart',
            '-c:a', 'aac',
            '-b:a', '96k',
            $output,
        ]);
        $process->setTimeout(240);

        try {
            $process->mustRun();
            if (! is_file($output) || filesize($output) === 0) {
                throw new RuntimeException('Не вдалося створити оптимізоване відео.');
            }

            return $this->storeFile($output, $disk, $directory, 'mp4', $visibility);
        } catch (\Throwable $exception) {
            throw new RuntimeException('Не вдалося обробити відео. Перевірте файл і спробуйте ще раз.', previous: $exception);
        } finally {
            @unlink($output);
        }
    }

    private function assertSize(UploadedFile $file, int $maxKilobytes, string $message): void
    {
        if (((int) $file->getSize() / 1024) > $maxKilobytes) {
            throw new RuntimeException($message);
        }
    }

    private function orientImage(\GdImage $image, UploadedFile $file): \GdImage
    {
        if ((string) $file->getMimeType() !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = @exif_read_data($file->getRealPath())['Orientation'] ?? 1;
        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        if ($rotated === false) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    private function storeFile(string $source, string $disk, ?string $directory, string $extension, string $visibility): string
    {
        $path = trim(($directory ? trim($directory, '/').'/' : '').Str::ulid().'.'.$extension, '/');
        $stream = fopen($source, 'rb');
        if ($stream === false || ! Storage::disk($disk)->put($path, $stream, ['visibility' => $visibility])) {
            if (is_resource($stream)) {
                fclose($stream);
            }
            throw new RuntimeException('Не вдалося зберегти оптимізований файл.');
        }
        fclose($stream);

        return $path;
    }

    private function temporaryPath(string $extension): string
    {
        $path = tempnam(sys_get_temp_dir(), 'lamari-media-');
        if ($path === false) {
            throw new RuntimeException('Не вдалося створити тимчасовий файл.');
        }
        @unlink($path);

        return $path.'.'.$extension;
    }
}
