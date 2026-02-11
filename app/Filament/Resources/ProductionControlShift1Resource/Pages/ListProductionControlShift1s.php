<?php

namespace App\Filament\Resources\ProductionControlShift1Resource\Pages;

use App\Filament\Resources\ProductionControlShift1Resource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductionControlShift1s extends ListRecords
{
    protected static string $resource = ProductionControlShift1Resource::class;
    protected static ?string $title = 'Production Control Sheets';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Report')
                ->icon('heroicon-o-plus'),
        ];
    }
}
