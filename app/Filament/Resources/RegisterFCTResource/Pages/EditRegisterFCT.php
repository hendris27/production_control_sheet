<?php

namespace App\Filament\Resources\RegisterFCTResource\Pages;

use App\Filament\Resources\RegisterFCTResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegisterFCT extends EditRecord
{
    protected static string $resource = RegisterFCTResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
