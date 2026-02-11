<?php

namespace App\Filament\Resources\RegisterFCTResource\Pages;

use App\Filament\Resources\RegisterFCTResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateRegisterFCT extends CreateRecord
{
    protected static string $resource = RegisterFCTResource::class;
     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Save')
            ->icon('heroicon-o-check');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Save & Create Another')
            ->icon('heroicon-o-plus');
    }

    protected static ?string $title = 'FCT Registration';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back to Dashboard')
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => route('filament.admin.pages.dashboard'))
                ->color('gray'),
        ];
    }
}
