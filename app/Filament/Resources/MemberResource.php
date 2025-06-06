<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Models\Member;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use App\Rules\UniqueFullName;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;

use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rule;



class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    //  protected static ?string $navigationIcon = 'heroicon-o-users';
    //protected static ?string $activeNavigationIcon = 'heroicon-o-document-text';



    public static function getNavigationLabel(): string
    {
        return 'Members';
    }
    //protected static ?string $navigationIcon = 'heroicon-o-users';


    public static function getNavigationIcon(): string | Htmlable | null
    {
        return new HtmlString(view('icons.users')->render());
    }





    public static function form(Form $form): Form
    {

        return $form
            ->schema([

                Section::make('Personal Information')
                    ->description('Enter the member’s name and contact details')
                    ->icon('heroicon-o-user')
                    ->columns(2) // Increased to 3 columns for better control
                    ->schema([
                        TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(255)
                            ->regex('/^[a-zA-Z\s\-]+$/')
                            ->validationMessages([
                                'required' => 'First name is required.',
                                'regex' => 'Only letters, spaces, and hyphens allowed.',
                            ])
                            ->columnSpan(1),


                        TextInput::make('middle_name')
                            ->label('Middle Name')
                            ->maxLength(255)
                            ->extraAttributes(['class' => 'w-20']),
                        TextInput::make('last_name')
                            ->required()
                            ->maxLength(255)
                            ->rule(function (Get $get, \Filament\Forms\Components\Component $component) {
                                $record = $component->getContainer()->getRecord();
                                return new UniqueFullName(
                                    $get('first_name'),
                                    $get('middle_name'), // <-- Pass middle name
                                    $record?->membersid
                                );
                            })
                            ->validationMessages([
                                'required' => 'Last name is required.',
                                'max' => 'Last name cannot exceed 255 characters.',
                            ]),

                        TextInput::make('suffix')
                            ->label('Suffix')
                            ->placeholder('Jr. / Sr.')
                            ->maxLength(10)
                            ->extraAttributes(['class' => 'w-20'])
                            ->columnSpan(1),

                        TextInput::make('phone')
                            ->label('Contact Number')
                            ->mask('9999-999-9999')
                            ->placeholder('0900-000-0000')
                            ->tel()
                            ->maxLength(20)
                            ->required(),
                        //->columnSpan(1),


                        Radio::make('gender')
                            ->label('Gender')
                            ->inline()
                            ->required()
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'others' => 'Others',
                            ]),
                        Grid::make(5)

                            ->schema([
                                // Label simulated as Placeholder
                                Placeholder::make('Birthday')
                                    ->content('Date of Birth*')
                                    ->extraAttributes(['class' => 'flex items-center font-medium text-sm text-gray-700']),

                                DatePicker::make('birthday')
                                    ->label('')
                                    ->maxDate(\Carbon\Carbon::today())
                                    ->required()
                                    ->native(false)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state) {
                                            $age = \Carbon\Carbon::parse($state)->age;
                                            $set('age', $age);
                                        }
                                    }),

                                TextInput::make('age')
                                    ->label('Age')
                                    ->readonly(),
                            ]),

                        FileUpload::make('photo')
                            ->avatar()
                            ->label('Upload Photo')
                            ->disk('public')
                            ->directory('images')
                            ->image()
                            ->previewable(true)
                            ->visibility('public')
                        // ->columnSpan(1),
                    ]),



                Section::make('Address Information')
                    ->description('Enter the member’s current address')
                    ->icon('heroicon-o-map')
                    ->columns(2)
                    ->schema([
                        Select::make('country')
                            ->label('Country')
                            ->options(
                                collect(json_decode(file_get_contents(storage_path('app/locations/countries.json')), true))
                                    ->mapWithKeys(fn($item) => [$item['id'] => $item['name']])
                                    ->toArray()
                            )
                            ->searchable()
                            ->reactive()
                            ->required(),


                        Select::make('province')
                            ->label('Province')
                            ->options(function (callable $get) {
                                $states = json_decode(file_get_contents(storage_path('app/locations/states.json')), true);

                                return collect($states)
                                    ->where('country_id', $get('country'))
                                    ->mapWithKeys(fn($state) => [$state['id'] => $state['name']])
                                    ->toArray();
                            })
                            ->reactive()
                            ->required(),


                        Select::make('city')
                            ->label('City')
                            ->options(function (callable $get) {
                                $cities = json_decode(file_get_contents(storage_path('app/locations/cities.json')), true);

                                return collect($cities)
                                    ->where('state_id', $get('province'))
                                    ->mapWithKeys(fn($city) => [$city['id'] => $city['name']])
                                    ->toArray();
                            })
                            ->required(),
                        Textarea::make('street')
                            ->label('Street Address')
                            ->extraAttributes(['style' => 'resize: none;']),
                        TextInput::make('postal_code')
                            ->label('Postal Code')
                            ->required(),
                    ]),

                Section::make('Membership')
                    ->description('Membership Details')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([


                        DatePicker::make('membership_date')
                            ->native(false)
                            ->required()
                            ->maxDate(Carbon::now()),


                        Radio::make('membership_status')
                            ->inline()
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])->default('active'),
                    ]),
                // Section::make('Account Information')
                //     ->description('Login credentials for this member')
                //     ->icon('heroicon-o-lock-closed')
                //     ->columns(2)
                //     // ->relationship('users')
                //     ->schema([
                //       TextInput::make('username')
                //             ->label('Username')
                //             ->required()
                //             ->minLength(4)
                //             ->maxLength(20)
                //             ->regex('/^[a-zA-Z0-9_]+$/')
                //             ->rules(function ($livewire) {
                //                 // Access the Member record
                //                 $member = $livewire->record;

                //                 // Get the related User ID
                //                 $userId = $member?->user?->userid;

                //                 return [
                //                     Rule::unique('users', 'username')
                //                         ->ignore($userId, 'userid'),
                //                 ];
                //             })
                //             ->validationMessages([
                //                 'regex' => 'Username may only contain letters, numbers, and underscores.',
                //                 'unique' => 'This username is already taken.',
                //             ]),


                //         TextInput::make('email')
                //             ->label('Email')
                //             ->email()
                //              ->rules(function ($livewire) {
                //                 // Access the Member record
                //                 $member = $livewire->record;

                //                 // Get the related User ID
                //                 $userId = $member?->user?->userid;

                //                 return [
                //                     Rule::unique('users', 'email')
                //                         ->ignore($userId, 'userid'),
                //                 ];
                //             })
                //             ->required()
                //             ->maxLength(255)
                //              ->validationMessages([

                //                 'unique' => 'This email is already taken.',
                //             ]),

                //         TextInput::make('password')
                //             ->label('Password')
                //             ->password()
                //             // ->required()
                //             ->maxLength(255),
                //     ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            // ->paginated(10)
            ->defaultSort('memberID', 'desc')
            ->modifyQueryUsing(
                fn($query) =>
                $query
                    ->with(['name', 'address']) 

            )
            ->columns([
                TextColumn::make('name.full_name')
                    ->label('Name')

                    ->sortable(['last_name', 'first_name', 'middle_name'])

                    ->searchable(
                        query: function ($query, string $search) {
                            $query->whereHas('name', function ($q) use ($search) {
                                $q->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%")
                                    ->orWhere('middle_name', 'like', "%{$search}%");
                            });
                        }
                    )
                    ->getStateUsing(function ($record) {
                        return optional($record->name)->last_name . ', ' . optional($record->name)->first_name
                            . '  ' . optional($record->name)->middle_name;
                    }),

                // TextColumn::make('contact_number')
                //     ->label('Contact Number'),


                TextColumn::make('address.full_address')
                    ->label('Address')
                    ->sortable(['country', 'province', 'city'])
                    ->searchable(['country', 'province', 'city']),


                ImageColumn::make('photo')
                    ->disk('public')
                    ->size(50)
                    ->circular()
                    ->label('Photo'),


                TextColumn::make('membership_date')
                    ->searchable()
                    ->date()
                    ->label('Member Since'),

                TextColumn::make('membership_status')
                    ->badge()
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                        'secondary' => 'deceased',
                    ]),



            ])
            ->filters([
                SelectFilter::make('membership_status')
                    ->label('Member Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'deceased' => 'Deceased',
                    ])
                    ->default('active'),
            ])


            //->filtersLayout(FiltersLayout::AboveContent)
            // 👈 separates it into its own dropdown
            // ->actions([

            //     Tables\Actions\ViewAction::make(),
            //     Tables\Actions\EditAction::make(),
            //     Tables\Actions\DeleteAction::make(),
            //     ViewAction::make()
            //     ->label('View')
            //     ->modalHeading('View Member Details')
            //     ->form(fn (ViewRecord $livewire) => (new ViewMember($livewire->getRecord()))->form(new Form()))
            //     // ->label('Action')
            // ])
            ->actions([
                // ViewAction::make()
                //     ->label('View')
                //     ->modalHeading('View Member Details')
                //     ->modalWidth('4xl')
                //     ->form(fn(ViewRecord $livewire) => (new ViewMember($livewire->getRecord()))->form(new Form())),

                EditAction::make(),
                DeleteAction::make(),
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
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
            //  'view' => Pages\ViewMember::route('/{record}')
        ];
    }


    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()
    //         ->with(['user.name', 'user.address']);
    // }
    // public static function modifyQueryUsing(Builder $query): Builder
    // {
    //     return $query->with(['user.name', 'user.address']);
    // }


    //  public static function query(): Builder
    // {
    //     return parent::query()->with(['user.name', 'user.address']);
    // }




}
