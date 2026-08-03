<?php

namespace Tests\Unit;

use App\Services\MediaOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class MediaOptimizerTest extends TestCase
{
    public function test_it_converts_and_resizes_images_to_webp(): void
    {
        Storage::fake('public');
        $upload = UploadedFile::fake()->image('large.jpg', 4000, 3000)->size(5000);

        $path = app(MediaOptimizer::class)->optimizeAndStore($upload, 'public', 'products');

        Storage::disk('public')->assertExists($path);
        $this->assertStringEndsWith('.webp', $path);
        [$width, $height] = getimagesize(Storage::disk('public')->path($path));
        $this->assertLessThanOrEqual(2400, max($width, $height));
    }

    public function test_it_rejects_images_larger_than_eight_megabytes(): void
    {
        Storage::fake('public');
        $upload = UploadedFile::fake()->image('too-large.jpg')->size(8193);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('8 МБ');

        app(MediaOptimizer::class)->optimizeAndStore($upload, 'public', 'products');
    }

    public function test_it_converts_video_to_web_optimized_mp4(): void
    {
        Storage::fake('public');
        $input = tempnam(sys_get_temp_dir(), 'lamari-video-test-').'.mp4';
        $process = new Process([
            '/usr/bin/ffmpeg', '-hide_banner', '-loglevel', 'error', '-y',
            '-f', 'lavfi', '-i', 'color=c=white:s=640x360:d=1',
            '-c:v', 'libx264', '-pix_fmt', 'yuv420p', $input,
        ]);
        $process->mustRun();

        try {
            $upload = new UploadedFile($input, 'source.mp4', 'video/mp4', null, true);
            $path = app(MediaOptimizer::class)->optimizeAndStore($upload, 'public', 'products');

            Storage::disk('public')->assertExists($path);
            $this->assertStringEndsWith('.mp4', $path);
            $this->assertGreaterThan(0, Storage::disk('public')->size($path));
        } finally {
            @unlink($input);
        }
    }
}
