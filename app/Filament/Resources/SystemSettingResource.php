<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingResource\Pages;

use App\Models\SystemSetting;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;

class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 5;

  public static function form(Form $form): Form
{
    return $form
        ->schema([
            Grid::make(1)->schema([
                TextInput::make('key')
                    ->required()
                    ->disabled(fn($record) => $record !== null),

                TextInput::make('value')
                    ->numeric()
                    ->label(fn ($get) => match ($get('key')) {
                        'coordinator_share_percentage' => 'Share Percentage (%)',
                        'mortuary_contribution' => 'Contribution Amount (₱)',
                        default => 'Value',
                    })
                    ->required(),

                RichEditor::make('description')
                    ->nullable(),
            ]),
        ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([


                Tables\Columns\TextColumn::make('key'),
                Tables\Columns\TextColumn::make('value')
    ->label('Amount')
    ->formatStateUsing(function ($state, $record) {
        return match ($record->key) {
            'coordinator_share_percentage' => $state . '%',
            default => '₱' . number_format($state, 2),
        };
    }),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->html() // Render HTML instead of plain text
                    ->wrap(), // Optional: wraps long content


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth('md')
                    ->slideover(),
                Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListSystemSettings::route('/'),
            // 'create' => Pages\CreateSystemSetting::route('/create'),
            // 'edit' => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }
}
