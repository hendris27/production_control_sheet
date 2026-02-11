<?php

namespace App\Filament\Widgets;

use Filament\Widgets\AccountWidget as BaseAccountWidget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Actions;
use Filament\Facades\Filament;

class AccountWidget extends BaseAccountWidget
{
    public function getForms(): array
    {
        return [
            'manageAccount' => $this->makeForm()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Name')
                        ->required(),
                    Forms\Components\TextInput::make('nik')
                        ->label('NIK')
                        ->required(),
                ])
                ->model(Filament::getCurrentPanel()->getUser()),
        ];
    }

    public function getActions(): array
    {
        return array_merge(parent::getActions(), [
            Actions\Action::make('delete')
                ->label('Delete Account')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    $user = Filament::getCurrentPanel()->getUser();
                    $user->delete();
                    Filament::auth()->logout();
                    session()->invalidate();
                    session()->regenerateToken();
                    return redirect('/');
                }),
        ]);
    }
}
