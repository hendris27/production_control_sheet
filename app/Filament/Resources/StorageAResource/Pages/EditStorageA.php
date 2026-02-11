<?php

namespace App\Filament\Resources\StorageAResource\Pages;

use App\Filament\Resources\StorageAResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStorageA extends EditRecord
{
    protected static string $resource = StorageAResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Set shift otomatis sesuai jam server saat edit/update
        $hour = (int)date('H');
        if ($hour >= 7 && $hour < 15) {
            $data['shift'] = 1;
        } elseif ($hour >= 15 && $hour < 23) {
            $data['shift'] = 2;
        } else {
            $data['shift'] = 3;
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
           // Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        // Redirect setelah berhasil simpan ke Halaman View Data
        return $this->getResource()::getUrl('index');
    }


    
}
