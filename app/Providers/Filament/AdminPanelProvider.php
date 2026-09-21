<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Support\SiteSettings;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Northwestern\FilamentTheme\NorthwesternTheme;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            // The public site owns the root path, so staff sign in one level down.
            ->path('admin')
            ->login()
            ->spa()
            ->brandName(fn (): string => (string) app(SiteSettings::class)->get('business_name', config('app.name')))
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(Width::Full)
            ->databaseTransactions()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            // Listed on the Dashboard page itself, in a deliberate order.
            ->widgets([])
            ->navigationGroups([
                NavigationGroup::make()->label('Operations')->icon(Heroicon::OutlinedTruck),
                // Carriers sit in their own group so the whole area disappears for a
                // role without ViewCarrierDetails, rather than showing empty pages.
                NavigationGroup::make()->label('Carriers')->icon(Heroicon::OutlinedPaperAirplane),
                NavigationGroup::make()->label('Pricing')->icon(Heroicon::OutlinedBanknotes),
                NavigationGroup::make()->label('Content')->icon(Heroicon::OutlinedNewspaper),
                NavigationGroup::make()->label('System')->icon(Heroicon::OutlinedCog6Tooth),
            ])
            ->plugins([
                NorthwesternTheme::make()->withoutAssetRegistration(),
                FilamentShieldPlugin::make()
                    ->navigationGroup('System')
                    ->navigationLabel('Roles & permissions')
                    ->navigationIcon(Heroicon::ShieldCheck)
                    ->navigationSort(9),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css');
    }
}
