<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChecksheetListResource\Pages;
use App\Filament\Resources\DetailLineResource;
use App\Models\ChecksheetList;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChecksheetListResource extends Resource
{
    protected static ?string $model = ChecksheetList::class;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama Line')
                ->required()
                ->unique(ignoreRecord: true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
    ->label('Line No')
    //->url(fn ($record) => \App\Filament\Resources\LineDetailChecksheetResource::getUrl('index', [
    //    'checksheet_list' => $record->id,
    //]))
    ->searchable(),

            ])
            ->defaultSort('id', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChecksheetLists::route('/'),
            'create' => Pages\CreateChecksheetList::route('/create'),
            'edit' => Pages\EditChecksheetList::route('/{record}/edit'),
        ];
    }
}
