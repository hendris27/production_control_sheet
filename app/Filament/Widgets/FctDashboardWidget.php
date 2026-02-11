<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Notifications\Notification;

class FctDashboardWidget extends Widget
{
    protected static string $view = 'filament.widgets.fct-dashboard-widget';

    public function selectOption(string $option): void
    {
        Notification::make()
            ->title("You selected: {$option}")
            ->success()
            ->send();
    }
}
