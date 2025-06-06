<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\UsersResource\Pages;
use App\Filament\Staff\Resources\UsersResource\RelationManagers;
use App\Models\Users;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UsersResource extends Resource
{
    protected static ?string $model = Users::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
       return $table
            ->defaultSort('userid', 'desc')
            ->modifyQueryUsing(function ($query) {
                $query
                    ->with(['user.name', 'user.address'])
                    ->whereHas('user', function ($q) {
                        $q->whereIn('role', ['staff']);
                    });
            })

            ->columns([
                TextColumn::make('user.name.first_name')
                    ->label('Name')
                    ->getStateUsing(function ($record) {
                        return $record->user?->name?->first_name . ' ' . $record->user?->name?->last_name;
                    })
                    ->searchable()
                    ->sortable(),


                // TextColumn::make('user.role')
                //     ->label('Role'),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ]),
                TextColumn::make('action_label')
                    ->label('Action')
                    ->extraAttributes(['class' => 'text-center'])
                    ->state('') // prevents showing anything in the row
                    ->alignCenter(),


            ])

            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                ->disabled(fn ($record) => $record->user?->role === 'staff'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUsers::route('/create'),
            'edit' => Pages\EditUsers::route('/{record}/edit'),
        ];
    }
}
