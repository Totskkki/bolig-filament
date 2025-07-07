<?php

namespace App\Filament\Resources;


use App\Filament\Resources\UsersResource\Pages;
use App\Models\Users;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\Support\Htmlable;

class UsersResource extends Resource
{
    protected static ?string $model = Users::class;

   // protected static ?string $navigationIcon = 'fas-user-friends';
    protected static ?string $navigationGroup = 'User';

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return new HtmlString(view('components.icons.users')->render());
    }


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
                        $q->whereIn('role', ['staff', 'admin']);
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


                TextColumn::make('user.role')
                    ->label('Role'),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ]),
                ImageColumn::make('user.photo')
                    ->disk('public')
                    ->size(50)
                    ->circular()
                    ->label('Photo'),



            ])

            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->disabled(fn($record) => $record->user?->role === 'admin'),

            ])
            ->actionsAlignment('center')
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUsers::route('/create'),
            'edit' => Pages\EditUsers::route('/{record}/edit'),

        ];
    }
}
