<?php

namespace App\Filament\Resources\RegisterFCTResource\Pages;

use App\Filament\Resources\RegisterFCTResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRegisterFCTS extends ListRecords
{
    protected static string $resource = RegisterFCTResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
