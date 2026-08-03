<?php

namespace App\Providers;

use App\Services\DiscountService;
use App\Services\MediaOptimizer;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(DiscountService::class, fn () => new DiscountService);
        $this->app->singleton(MediaOptimizer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FileUpload::configureUsing(function (FileUpload $upload): void {
            $upload->saveUploadedFileUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file): ?string {
                $optimizer = app(MediaOptimizer::class);

                if (! $optimizer->supports($file)) {
                    return $component->saveUploadedFile($file);
                }

                try {
                    return $optimizer->optimizeAndStore(
                        $file,
                        $component->getDiskName(),
                        $component->getDirectory(),
                        $component->getVisibility(),
                    );
                } catch (Throwable $exception) {
                    report($exception);

                    throw ValidationException::withMessages([
                        $component->getStatePath() => $exception->getMessage(),
                    ]);
                } finally {
                    $file->delete();
                }
            });
        });
    }
}
