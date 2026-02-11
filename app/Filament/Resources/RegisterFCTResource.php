<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegisterFCTResource\Pages;
use App\Filament\Resources\RegisterFCTResource\RelationManagers;
use App\Models\RegisterFCT;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\BadgeColumn;

class RegisterFCTResource extends Resource
{
    protected static ?string $model = RegisterFCT::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationLabel = 'FCT Registration';
    
    protected static ?string $modelLabel = 'FCT Registration';

    public static function getPluralLabel(): string
    {
        return 'FCT Registration';
    }

    public static function getLabel(): string
    {
        return 'FCT Registration';
    }

    protected static ?string $createButtonLabel = 'New Registration';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    protected static ?string $pluralModelLabel = 'FCT Registration';
    
    protected static ?string $breadcrumb = 'FCT Registration';
    
    protected static bool $shouldRegisterNavigation = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Registration Information')
                    ->schema([
                        Forms\Components\DatePicker::make('date_registration')
                            ->required()
                            ->label('Registration Date')
                            ->default(now()),
                            
                        Forms\Components\TextInput::make('registration_no')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->label('Registration Number')
                            ->placeholder('Enter registration number'),
                    ])->columns(2),

                Forms\Components\Section::make('Product Details')
                    ->schema([
                        Forms\Components\Select::make('customer_by')
                            ->required()
                            ->label('Customer')
                            ->options(fn () => Customer::query()
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->placeholder('Select customer')
                            ->preload(),


                        Forms\Components\TextInput::make('fabrication_by')
                            ->required()    
                            ->label('Fabrication By')
                            ->placeholder('Enter fabrication name'),

                        Forms\Components\TextInput::make('product_model')
                            ->required()
                            ->label('Product Model')
                            ->placeholder('Enter product model'),

                        Forms\Components\Hidden::make('status_fct')
                            ->default('registered')
                            ->required()
                            ->label('Status FCT'),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_no')
                    ->label('Registration No')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('date_registration')
                    ->label('Registration Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fabrication_by')
                    ->label('Fabrication By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product_model')
                    ->label('Product Model')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status_fct')
                    ->label('Status')
                    ->state('registered')
                    ->color('primary')
                    ->searchable()
                    ->sortable(),


                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Modified')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
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
            'index' => Pages\ListRegisterFCTS::route('/'),
            'create' => Pages\CreateRegisterFCT::route('/create'),
            'edit' => Pages\EditRegisterFCT::route('/{record}/edit'),
        ];
    }
}
