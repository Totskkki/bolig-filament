<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Carbon;

class ViewMember extends ViewRecord
{
    protected static string $resource = MemberResource::class;

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Personal Information')
                ->icon('heroicon-o-user')
                ->columns(2)
                ->schema([
                    TextInput::make('first_name')
                        ->label('First Name')
                        ->disabled(),

                    TextInput::make('last_name')
                        ->label('Last Name')
                        ->disabled(),

                    TextInput::make('middle_name')
                        ->label('Middle Name')
                        ->disabled(),

                    TextInput::make('suffix')
                        ->label('Suffix')
                        ->disabled(),

                    TextInput::make('contact_number')
                        ->label('Contact Number')
                        ->tel()
                        ->disabled(),
                ]),

            Section::make('Address Information')
                ->icon('heroicon-o-map')
                ->columns(2)
                ->schema([
                    Select::make('country')
                        ->label('Country')
                        ->options(
                            collect(json_decode(file_get_contents(storage_path('app/locations/countries.json')), true))
                                ->mapWithKeys(fn ($item) => [$item['id'] => $item['name']])
                                ->toArray()
                        )
                        ->disabled(),

                    Select::make('province')
                        ->label('Province')
                        ->options(function (callable $get) {
                            $states = json_decode(file_get_contents(storage_path('app/locations/states.json')), true);
                            return collect($states)
                                ->where('country_id', $get('country'))
                                ->mapWithKeys(fn ($state) => [$state['id'] => $state['name']])
                                ->toArray();
                        })
                        ->disabled(),

                    Select::make('city')
                        ->label('City')
                        ->options(function (callable $get) {
                            $cities = json_decode(file_get_contents(storage_path('app/locations/cities.json')), true);
                            return collect($cities)
                                ->where('state_id', $get('province'))
                                ->mapWithKeys(fn ($city) => [$city['id'] => $city['name']])
                                ->toArray();
                        })
                        ->disabled(),

                    Textarea::make('street')
                        ->label('Street Address')
                        ->disabled()
                        ->extraAttributes(['style' => 'resize: none;']),

                    TextInput::make('postal_code')
                        ->label('Postal Code')
                        ->disabled(),
                ]),

            Section::make('Membership')
                ->icon('heroicon-o-user')
                ->columns(2)
                ->schema([
                    DatePicker::make('membership_date')
                        ->native(false)
                        ->disabled(),

                    Select::make('status')
                        ->label('Membership Status')
                        ->options([
                            'active' => 'Active',
                            'inactive' => 'Inactive',
                        ])
                        ->disabled(),
                ]),

            Section::make('Account Information')
                ->icon('heroicon-o-lock-closed')
                ->columns(2)
                ->schema([
                    TextInput::make('username')
                        ->label('Username')
                        ->disabled(),

                    TextInput::make('email')
                        ->label('Email')
                        ->disabled(),

                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->disabled(),
                ]),
        ]);
    }
}
