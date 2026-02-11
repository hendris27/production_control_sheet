<?php

namespace App\Filament\Resources\ChecksheetListResource\Pages;

use App\Filament\Resources\ChecksheetListResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChecksheetList extends EditRecord
{
    protected static string $resource = ChecksheetListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
