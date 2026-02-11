<?php

namespace App\Filament\Resources\IronSolderInspectionResource\Pages;

use App\Filament\Resources\IronSolderInspectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIronSolderInspections extends ListRecords
{
    protected static string $resource = IronSolderInspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
