<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource\Pages\EditMember;

use App\Filament\Resources\MemberResource\Pages\ListMembers;
use App\Filament\Staff\Resources\MemberResource\Pages\CreateMember;
use App\Models\Member;
use Dotenv\Exception\ValidationException;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use App\Rules\UniqueFullName;


use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        // $exists = \App\Models\Member::where('first_name', $data['first_name'])
        //     ->where('last_name', $data['last_name'])
        //     ->exists();
        // if ($exists) {
        //     throw \Filament\Forms\ValidationException::withMessages([
        //         'last_name' => 'A member with this first and last name already exists.',
        //     ]);
        // }

        // Calculate age etc.
        if (!empty($data['birthdate'])) {
            $data['age'] = Carbon::parse($data['birthdate'])->age;
        }
        return $data;
    }

    protected static function mutateFormDataBeforeUpdate(array $data): array
    {
        if (!empty($data['birthdate'])) {
            $data['age'] = Carbon::parse($data['birthdate'])->age;
        }
        return $data;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Membership Form')
                    ->columns(2)
                    ->schema([
                        TextInput::make('first_name')
                            ->required()
                            ->maxLength(255)
                            ->rules(['regex:/^[a-zA-Z\s\-]+$/'])
                            ->validationMessages([
                                'required' => 'First name is required.',
                                'max' => 'First name cannot exceed 255 characters.',
                                'regex' => 'First name can only contain letters, spaces, and hyphens.',
                            ]),

                        TextInput::make('last_name')
                            ->required()
                            ->maxLength(255)
                            ->rule(function (\Filament\Forms\Get $get) {
                                return new UniqueFullName($get('first_name'));
                            }),


                        DatePicker::make('birthdate')
                            ->native(false)
                            ->maxDate(Carbon::today())
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $age = Carbon::parse($state)->age;
                                    $set('age', $age);
                                }
                            }),
                        TextInput::make('age')
                            // ->disabled()
                            ->numeric()
                            ->maxLength(3),
                        TextInput::make('address')
                            ->maxLength(255),
                        TextInput::make('contact_number')
                            ->tel()
                            ->maxLength(20),
                        Select::make('status')
                            ->required()
                            ->options([
                                'active' => 'Active',
                                'deceased' => 'Deceased',
                            ])
                            ->default('active'),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')->searchable()->sortable(),
                TextColumn::make('last_name')->searchable()->sortable(),
                TextColumn::make('birthdate')->date()->sortable(),
                TextColumn::make('address')->limit(30),
                TextColumn::make('contact_number'),
                BadgeColumn::make('status')
                    ->colors([
                        'primary' => 'active',
                        'danger' => 'deceased',
                    ]),
                TextColumn::make('created_at')->dateTime()->label('Created'),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
