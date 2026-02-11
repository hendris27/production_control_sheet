<?php

namespace App\Providers\Filament;
use Illuminate\Support\Facades\Auth;
use App\Filament\Pages\ChecksheetPage;
use App\Models\User;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationItem;
use App\Filament\Resources\ProductionControlShift1Resource;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Http\Controllers\Auth\LoginController::class . '@showLoginForm')
            ->sidebarCollapsibleOnDesktop()

            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([
                Pages\Dashboard::class,
                ChecksheetPage::class,
            ])
            ->widgets([
                \App\Filament\Widgets\FctDashboardWidget::class,
                \App\Filament\Widgets\CustomerPcbWidget::class,
            ])
            ->navigation(fn (NavigationBuilder $builder) =>
                $builder
                    ->items([
                        NavigationItem::make('Dashboard')
                            //->url(Pages\Dashboard::getUrl())
                            ->url(ProductionControlShift1Resource::getUrl('index'))
                            ->icon('heroicon-o-home')
                            ->sort(0),
                    ])
                    ->groups([
                        \Filament\Navigation\NavigationGroup::make('Reports Production')
                                    ->icon('heroicon-o-clipboard-document-list')

                            ->items([
                                 NavigationItem::make('Form PCS')
                                    ->url(route('filament.admin.resources.production-control-shift1s.create')),
                            ]),
                    ])

                     ->groups([
                        \Filament\Navigation\NavigationGroup::make('Checksheet Machine')
                                    ->icon('heroicon-o-wrench-screwdriver')

                            ->items([
                                 NavigationItem::make('Selbo')
                                    ->url('#'),
                                      NavigationItem::make('Tekorobo')
                                    ->url('#'),
                                      NavigationItem::make('Robotic')
                                    ->url('#'),

                            ]),
                    ])


                        ->items([
                                NavigationItem::make('Manage Account')
                            ->url(route('filament.admin.resources.users.index'))
                            ->icon('heroicon-o-user-group')
                            ->sort(99)
                            ->visible(function () {
                                /** @var \App\Models\User&\Spatie\Permission\Traits\HasRoles|null $user */
                                $user = Auth::user();

                                return $user && $user->hasRole('admin');
                            })
])

)

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
            ]);
    }
}
