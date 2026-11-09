<?php

namespace App\Providers;

use App\Models\User;
use App\Services\AuditLogger;
use App\Services\FeatureControlService;
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
    public function register(): void
    {
        $this->app->singleton(SettingService::class);
        $this->app->singleton(FeatureControlService::class);
        $this->app->singleton(AuditLogger::class);
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Vite::useBuildDirectory('assets');

        Blade::component('layouts.app', 'layouts.app');
        Blade::component('layouts.guest', 'layouts.guest');
        Blade::component('layouts.pos', 'layouts.pos');
        Blade::component('layouts.calendar', 'layouts.calendar');

        Model::preventLazyLoading(! $this->app->isProduction());

        Paginator::useBootstrapFive();

        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        Gate::before(fn (User $user): ?bool => $user->isSuperAdmin() ? true : null);

        View::composer(['layouts.app', 'layouts.guest', 'layouts.pos', 'components.sidebar'], function ($view): void {
            $view->with('appSettings', $this->app->make(SettingService::class)->all());
            $view->with('featureControl', $this->app->make(FeatureControlService::class));
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
