<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\ActivityPolicy;
use App\Policies\RolePolicy;
use App\Services\DiscountService;
use App\Services\MediaOptimizer;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
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
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        $this->registerAdminActivityLogging();

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

    private function registerAdminActivityLogging(): void
    {
        foreach (['created', 'updated', 'deleted', 'restored'] as $event) {
            Event::listen("eloquent.{$event}: *", function (string $eventName, array $models) use ($event): void {
                $model = $models[0] ?? null;
                $user = auth()->user();

                if (! $model instanceof Model || $model instanceof Activity || ! $user instanceof User) {
                    return;
                }

                if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                    return;
                }

                $hidden = ['password', 'remember_token', 'api_key', 'token', 'secret'];
                $changes = collect($model->getChanges())->except($hidden)->all();
                $old = collect($model->getOriginal())->only(array_keys($changes))->except($hidden)->all();

                activity('admin')
                    ->causedBy($user)
                    ->performedOn($model)
                    ->event($event)
                    ->withProperties(['old' => $old, 'attributes' => $changes])
                    ->log(class_basename($model).' '.$event);
            });
        }
    }
}
