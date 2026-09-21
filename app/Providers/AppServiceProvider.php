<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\SiteSettings;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // One cached read of the settings row per request, shared by the layout, the
        // theme component and the panel's brand name.
        $this->app->singleton(SiteSettings::class);
    }

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        // Shield's own Role model sits outside Laravel's policy discovery, so its
        // policy has to be pointed at explicitly.
        FilamentShield::enforcePolicies();
    }
}
