<?php

namespace App\Filament\Resources\IronSolderInspectionResource\Pages;

use App\Filament\Resources\IronSolderInspectionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateIronSolderInspection extends CreateRecord
{
    protected static string $resource = IronSolderInspectionResource::class;

    protected function getRedirectUrl(): string
    {
        // Redirect setelah berhasil simpan ke halaman List
        return $this->getResource()::getUrl('index');
    }
}
