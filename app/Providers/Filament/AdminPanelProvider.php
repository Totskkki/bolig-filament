<?php

namespace App\Providers\Filament;

use App\Filament\Resources\ContributionResource;
use App\Filament\Resources\MemberResource;
use App\Models\Contribution;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;

use Filament\Widgets;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Slate,
            ])
            ->font('Poppins')
            ->databaseNotifications()
            ->favicon('images/favicon.png')
            ->spa()

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])


            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ]);
    }

    //         ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
    //             return $builder
    //                 ->items([
    //                     NavigationItem::make('Dashboard')
    //                         ->icon('heroicon-o-home')
    //                         ->url(Dashboard::getUrl())
    //                         ->isActiveWhen(fn() => request()->routeIs('filament.admin.pages.dashboard')),

    //                     NavigationItem::make('Members')
    //                         ->icon('heroicon-o-users')
    //                         ->url(MemberResource::getUrl())
    //                         ->isActiveWhen(fn() => request()->routeIs('filament.admin.resources.members.*')),

    //                     NavigationItem::make('Contribution')
    //                         ->icon('heroicon-o-users')
    //                         ->url(ContributionResource::getUrl())
    //                         ->isActiveWhen(fn() => request()->routeIs('filament.admin.resources.contributions.*')),
    //                 ]);
    //         });
    // }
}
