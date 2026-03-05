<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use Filament\Facades\Filament;
use App\Models\ProductionControlShift1;
use App\Observers\ProductionControlShift1Observer;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Components\Field;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Provide a no-op `persist()` macro for Filament form fields.
        // This prevents errors when templates call ->persist() but the method
        // is not available in the installed Filament version.
        try {
            if (method_exists(Field::class, 'macro')) {
                Field::macro('persist', function () {
                    return $this;
                });
            }
        } catch (\Throwable $e) {
            // ignore - macro registration is best-effort
        }

                     // register model observer to append CSV on save
                     ProductionControlShift1::observe(ProductionControlShift1Observer::class);
                     // untuk scroll halaman view data storing FCT
                     //  \Filament\Facades\Filament::serving(function () {
                     //       \Filament\Facades\Filament::registerViteTheme('resources/css/filament/admin/theme.css');
                     //  });

                    // Deny update/delete abilities to non-admin users application-wide
                    Gate::before(function ($user, $ability) {
                        if (! $user) {
                            return null;
                        }

                        // Admins are allowed to do everything
                        if ($user->hasRole('admin')) {
                            return null; // allow other gates to decide
                        }

                        // For update/delete abilities, deny for non-admins
                        if (in_array($ability, ['update', 'delete', 'edit'])) {
                            return false;
                        }

                        return null;
                    });
    }
}
