<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IronSolderInspectionResource\Pages;
use App\Models\IronSolderInspection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Facades\Filament;

class IronSolderInspectionResource extends Resource
{
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // Isi 'pic' dengan user login jika belum ada
        if (empty($data['pic'])) {
            $data['pic'] = Filament::auth()->id();
        }
        // 'no' diisi sementara dengan null, nanti di mutateFormDataAfterCreate
        return $data;
    }

    public static function mutateFormDataAfterCreate($record, array $data): void
    {
        // Update field 'no' dengan id record setelah insert
        $record->no = $record->id;
        $record->save();
    }
    protected static ?string $model = IronSolderInspection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('pic')
                    ->default(fn () => Filament::auth()->id())
                    ->required(),
                Forms\Components\Select::make('shift')
                    ->label('Shift')
                    ->options([
                        1 => '1',
                        2 => '2',
                        3 => '3',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('line')
                    ->label('Line')
                    ->numeric()
                    ->required(),
                // Tanggal tidak perlu input manual, gunakan created_at
                Forms\Components\TextInput::make('actual_setting')->label('Actual Setting')->required(),
                Forms\Components\TextInput::make('esd_voltage')->label('ESD Voltage')->required(),
                Forms\Components\TextInput::make('eos_ground')->label('EOS Ground')->required(),
                Forms\Components\Radio::make('solder_tip_condition')
                    ->label('Solder Tip Condition')
                    ->options([
                        '✔️ OK' => '✔️ OK',
                        '❌ NG' => '❌ NG',
                        '🔄 Ganti' => '🔄 Ganti',
                    ])
                    ->inline()
                    ->required(),

                Forms\Components\Radio::make('solder_stand_condition')
                    ->label('Solder Stand Condition')
                    ->options([
                        '✔️ OK' => '✔️ OK',
                        '❌ NG' => '❌ NG',
                        '🔄 Ganti' => '🔄 Ganti',
                    ])
                    ->inline()
                    ->required(),
                Forms\Components\Radio::make('judgement')
                    ->label('Judgement')
                    ->options([
                        '✔️ OK' => '✔️ OK',
                        '❌ NG' => '❌ NG',
                        '🔄 Ganti' => '🔄 Ganti',
                    ])
                    ->inline()
                    ->required(),
                Forms\Components\Textarea::make('remarks')
                    ->label('Remarks')
                    ->helperText("Specification:\n- Solder Iron      : 350 ± 10°C\n- ESD Surface Voltage : < 10 Ω\n- EOS Surface ground  : < 10 mV AC"),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d-m-Y')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('shift')->label('Shift')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('line')->label('Line')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('esd_voltage')->label('ESD Voltage')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('eos_ground')->label('EOS Ground')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('solder_tip_condition')->label('Solder Tip Condition')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('solder_stand_condition')->label('Solder Stand Condition')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('judgement')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('remarks')->label('Remarks')->limit(30)->alignCenter(),
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






                    



            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-m-pencil-square'),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-m-trash'),
            ])

            ->actionsColumnLabel('Aksi')
            

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIronSolderInspections::route('/'),
            'create' => Pages\CreateIronSolderInspection::route('/create'),
            'edit' => Pages\EditIronSolderInspection::route('/{record}/edit'),
        ];
    }
}
