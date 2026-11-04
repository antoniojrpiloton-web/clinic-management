<?php

namespace App\Providers;

use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SettingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingService::class);
        $this->app->singleton(AuditLogger::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Vite::useBuildDirectory('assets');

        Blade::component('layouts.app', 'layouts.app');
        Blade::component('layouts.guest', 'layouts.guest');

        Model::preventLazyLoading(! $this->app->isProduction());

        Paginator::useBootstrapFive();

        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        Gate::before(fn (User $user): ?bool => $user->isSuperAdmin() ? true : null);

        View::composer(['layouts.app', 'layouts.guest'], function ($view): void {
            $view->with('appSettings', $this->app->make(SettingService::class)->all());
        });

        View::composer('components.topbar', function ($view): void {
            $user = auth()->user()?->loadMissing('roles:id,name');

            $view->with([
                'unreadNotifications' => $user?->unreadNotifications()->latest()->limit(5)->get() ?? collect(),
                'unreadNotificationsCount' => $user?->unreadNotifications()->count() ?? 0,
            ]);
        });
    }
}
