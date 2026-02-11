<?php

namespace App\Filament\Resources\StorageAResource\Pages;

use App\Filament\Resources\StorageAResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms;
use Livewire\Attributes\On;

class CreateStorageA extends CreateRecord
{
    protected static string $resource = StorageAResource::class;
    protected static ?string $label = 'Storage Bulding A';
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
     protected function mutateFormDataBeforeSave(array $data): array
    {
        // Set shift otomatis sesuai jam server saat create
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
 // State untuk line yang dipilih
    public ?string $selectedLine = null;
    // ✅ Event dari StorageAResource
    #[On('openLineSelector')]
    public function openLineSelector(): void
    {
        $this->dispatch('open-modal', id: 'select-line-modal');
    }

    // ✅ Definisi modal popup
    protected function getModals(): array
    {
        return [
            'select-line-modal' => [
                'heading' => 'select line name',
                'form' => [
                    Forms\Components\Select::make('selectedLine')
                        ->label('select line')
                        ->options([
                            'Line 1' => 'Line 1',
                            'Line 2' => 'Line 2',
                            'Line 3' => 'Line 3',
                        ])
                        ->native(false)
                        ->required(),
                ],
                'actions' => [
                    Actions\Action::make('save')
                        ->label('save')
                        ->button()
                        ->color('primary')
                        ->action(function ($data, $livewire) {
                            // ✅ Set field location sesuai line yang dipilih
                            $livewire->form->fill([
                                'location' => $data['selectedLine'],
                            ]);
                            $livewire->dispatch('close-modal', id: 'select-line-modal');
                        }),
                ],
            ],
        ];
    }

}
