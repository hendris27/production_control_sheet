<?php

namespace App\Filament\Resources\IronSolderInspectionResource\Pages;

use App\Filament\Resources\IronSolderInspectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIronSolderInspection extends EditRecord
{
    protected static string $resource = IronSolderInspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
     protected function getRedirectUrl(): string
    {
        
        // Redirect setelah berhasil simpan ke halaman List
        return $this->getResource()::getUrl('index');
    }
}
