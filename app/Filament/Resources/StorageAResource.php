<?php

namespace App\Filament\Resources;


use App\Filament\Resources\StorageAResource\Pages;
use App\Models\StorageA;
use Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class StorageAResource extends Resource
{
    protected static ?string $model = StorageA::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $label = 'Storage Building A';
    protected static ?string $pluralLabel = 'Storage Building A';
    protected static ?string $navigationLabel = 'Storage Building A';

    public static function form(Form $form): Form
     {
        return $form
            ->schema([
                // Shift otomatis terisi sesuai jam, user tidak bisa input
                Forms\Components\Hidden::make('shift')
                    ->default(function () {
                        $hour = (int)date('H');
                        if ($hour >= 7 && $hour < 15) {
                            return 1;
                        } elseif ($hour >= 15 && $hour < 23) {
                            return 2;
                        } else {
                            return 3;
                        }
                    }),
                Forms\Components\Hidden::make('pic')
                    ->default(fn () => \Filament\Facades\Filament::auth()?->id())
                    ->required(),

                Forms\Components\Select::make('category')
                    ->label('Category')
                    ->options([
                        'FCT Machine' => 'FCT Machine',
                        'TU jig' => 'TU jig',
                        'PC' => 'PC',
                        'Master Sample' => 'Master Sample',
                    ]),

                Forms\Components\TextInput::make('model_name')
                    ->label('Model Name')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $get, $set) {
                        if ($get('status') === 'Free Space') {
                            $set('model_name', '-');
                        }
                    }),

                Forms\Components\TextInput::make('customer')
                    ->label('Customer')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $get, $set) {
                        if ($get('status') === 'Free Space') {
                            $set('customer', '-');
                        }
                    }),

                Forms\Components\TextInput::make('location')
                    ->label('Location')
                    ->required()
                    ->visible()
                    ->reactive(),
                   
                Forms\Components\Radio::make('status')
                    ->label('Status')
                    ->options([
                        'Running' => 'Running',
                        'Storing' => 'Storing',
                        'On PM' => 'On PM',
                    ])
                    ->inline()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $get, $set, $livewire) {
                        if ($state === 'On PM') {
                            $set('location', 'MTC Room');
                        }
                        if ($state === 'Running') {
                         $livewire->dispatch('openLineSelector');
                    }
                        if ($state === 'Free Space') {
                            $set('model_name', '-');
                            $set('customer', '-');
                            $set('remark', '-');
                        }
                    }),

                Forms\Components\TextInput::make('remark')
                    ->label('Remarks')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $get, $set) {
                        if ($get('status') === 'Free Space') {
                            $set('remark', '-');
                        }
                    }),     
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->searchable()
                    ->color('black')
                    ->weight('bold')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Date')
                    ->date('d-m-Y')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('category')->label('Category')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('model_name')->label('Model Name')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('customer')->label('Costumer')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->searchable()
                    ->alignCenter()
                    ->formatStateUsing(function ($state) {
                        switch ($state) {
    case 'Storing':
        return '<span style="
            display:inline-block;
            padding:4px 10px;
            border-radius:6px;
            background:#3b82f6; 
            color:#fff;
            font-weight:600;
            font-size:0.875rem;
        ">Storing</span>';

    case 'On PM':
        return '<span style="
            display:inline-block;
            padding:4px 10px;
            border-radius:6px;
            background:#f59e0b;
            color:#fff;
            font-weight:600;
            font-size:0.875rem;
        ">On PM</span>';

    case 'Running':
        return '<span style="
            display:inline-block;
            padding:4px 10px;
            border-radius:6px;
            background:#16a34a; 
            color:#fff;
            font-weight:600;
            font-size:0.875rem;
        ">Running</span>';


    default:
        return '<span style="
            display:inline-block;
            padding:4px 10px;
            border-radius:6px;
            background:#9ca3af;
            color:#fff;
            font-weight:600;
            font-size:0.875rem;
        ">'.e($state).'</span>';
}

                    })
                    ->html(),
                Tables\Columns\TextColumn::make('location')->label('Location')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('remark')->label('Remarks')->limit(30)->alignCenter(),
                Tables\Columns\TextColumn::make('pic')
                    ->label('PIC')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->pic) {
                            $user = \App\Models\User::where('nik', $record->pic)->first();
                            if ($user) {
                                $name = $user->name;
                                return '<span style="display:inline-flex;align-items:center;gap:8px;">'
                                    .'<span style="color:#222;font-weight:500;">'.$name.'</span>'
                                    .'</span>';
                            }
                        }
                        return '-';
                    })
                    ->html()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('shift')->label('Shift')->limit(30)->alignCenter()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Category')
                    ->options([
                        'FCT Machine' => 'FCT Machine',
                        'TU jig' => 'TU jig',
                        'PC' => 'PC',
                        'Master Sample' => 'Master Sample',
                    ]),
                Tables\Filters\SelectFilter::make('customer')
                    ->label('Customer')
                    ->options(fn () => \App\Models\StorageA::query()->distinct()->pluck('customer', 'customer')->toArray()),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Running' => 'Running',
                        'Storing' => 'Storing',
                        'On PM' => 'On PM',
                    ]),
                
            ])

            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-m-pencil-square'),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-m-trash'),
            ])

            ->actionsColumnLabel('Action')

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
       
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStorageAS::route('/'),
            'create' => Pages\CreateStorageA::route('/create'),
            'edit' => Pages\EditStorageA::route('/{record}/edit'),
        ];
    }
}
