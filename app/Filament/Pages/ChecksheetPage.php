<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ChecksheetPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.checksheet-page';

    /**
     * Breadcrumbs untuk Filament v3.3
     */
    public function getBreadcrumbs(): array
    {
        return [
                    route('filament.admin.pages.dashboard') => 'Dashboard',
            url()->current() => 'Checksheet',




            
        ];
    }
}
