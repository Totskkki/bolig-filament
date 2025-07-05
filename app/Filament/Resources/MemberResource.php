<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\RelationManagers\ContributionsRelationManager;
use App\Filament\Resources\MemberResource\Pages\MemberUnpaidContributions;

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
use Illuminate\Database\Eloquent\Builder;

use Filament\Forms\Form;
use App\Models\Users\Name;
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

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    //protected static ?string $activeNavigationIcon = 'heroicon-o-document-text';





    public static function getNavigationLabel(): string
    {
        return 'Members';
    }
    //protected static ?string $navigationIcon = 'heroicon-o-users';


    // public static function getNavigationIcon(): string | Htmlable | null
    // {
    //     return new HtmlString(view('icons.fa-users')->render());
    // }


    public static function form(Form $form): Form
    {

        return $form
            ->schema([

                Section::make('Personal Information')
                    ->description('Enter the member’s name and contact details')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->collapsible()
                    ->schema([

                        TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(255)
                            ->regex('/^[a-zA-Z\s\-]+$/')
                            ->afterStateUpdated(
                                fn(\Filament\Forms\Set $set, $state) =>
                                $set('first_name', ucwords(strtolower($state)))
                            )
                            ->dehydrateStateUsing(fn($state) => ucwords(strtolower($state)))
                            ->validationMessages([
                                'required' => 'First name is required.',
                                'regex' => 'Only letters, spaces, and hyphens allowed.',
                            ])
                            ->columnSpan(1),

                        TextInput::make('middle_name')
                            ->label('Middle Name')
                            ->maxLength(255)
                            ->extraAttributes(['class' => 'w-20'])
                            ->afterStateUpdated(
                                fn(\Filament\Forms\Set $set, $state) =>
                                $set('middle_name', ucwords(strtolower($state)))
                            )
                            ->dehydrateStateUsing(fn($state) => ucwords(strtolower($state))),

                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(255)
                            ->regex('/^[a-zA-Z\s\-]+$/')
                            ->afterStateUpdated(
                                fn(\Filament\Forms\Set $set, $state) =>
                                $set('last_name', ucwords(strtolower($state)))
                            )
                            ->dehydrateStateUsing(fn($state) => ucwords(strtolower($state)))
                            ->reactive()
                            ->rule(function (Get $get, \Filament\Forms\Components\Component $component) {
                                $firstName = $get('first_name');
                                $middleName = $get('middle_name');
                                $lastName = $get('last_name'); // Needed if you want to guard further
                                $record = $component->getContainer()->getRecord();

                                if (! $firstName) {
                                    return null;
                                } elseif (! $middleName) {
                                    return null;
                                } elseif (! $lastName) {
                                    return null;
                                }

                                return new UniqueFullName(
                                    $firstName,
                                    $middleName,
                                    $record?->name?->namesid
                                );
                            })

                            ->validationMessages([
                                'required' => 'Last name is required.',
                                'regex' => 'Only letters, spaces, and hyphens allowed.',
                            ])
                            ->columnSpan(1),


                        TextInput::make('suffix')
                            ->label('Suffix')
                            ->placeholder('Jr. / Sr.')
                            ->maxLength(10)
                            ->extraAttributes(['class' => 'w-20'])
                            ->columnSpan(1)
                            ->afterStateUpdated(function (\Filament\Forms\Set $set, $state) {
                                $set('suffix', ucwords(strtolower($state)));
                            })
                            ->dehydrateStateUsing(fn($state) => ucwords(strtolower($state))),



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


                        FileUpload::make('image_photo')
                            ->avatar()
                            ->label('Upload Photo')
                            ->disk('public')
                            ->directory('images')
                            ->image()
                            ->previewable(true)
                            ->visibility('public'),

                        Select::make('role')
                            ->label('Role')
                            ->options([
                                'member' => 'Member',
                                'coordinator' => 'Coordinator',

                            ])
                            ->required()
                            ->default('member')
                            ->columnSpan(1),

                        // ->columnSpan(1),
                        Select::make('coordinator_id')
                            ->label('Assign Coordinator')
                            ->options(
                                fn() =>
                                \App\Models\Member::with('name')
                                    ->where('role', 'coordinator')
                                    ->get()
                                    ->pluck('full_name', 'memberID')
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->visible(fn(Get $get) => $get('role') === 'member'),


                    ]),



                Section::make('Address Information')
                    ->description('Enter the member’s current address')
                    ->icon('heroicon-o-map')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Select::make('region')
                            ->label('Region')
                            ->options(
                                collect(json_decode(file_get_contents(storage_path('app/locations/region.json')), true))
                                    ->mapWithKeys(fn($item) => [$item['region_code'] => $item['region_name']])
                                    ->toArray()
                            )
                            ->searchable()
                            ->reactive()
                            ->required(),


                        Select::make('province')
                            ->label('Province')
                            ->options(function (callable $get) {
                                $provinces = json_decode(file_get_contents(storage_path('app/locations/province.json')), true);

                                return collect($provinces)
                                    ->where('region_code', $get('region'))
                                    ->mapWithKeys(fn($province) => [
                                        $province['province_code'] => $province['province_name']
                                    ])
                                    ->toArray();
                            })
                            ->reactive()
                            ->required(),



                        Select::make('city')
                            ->label('City')
                            ->options(function (callable $get) {
                                $cities = json_decode(file_get_contents(storage_path('app/locations/city.json')), true);

                                return collect($cities)
                                    ->where('province_code', $get('province'))
                                    ->mapWithKeys(fn($city) => [$city['city_code'] => $city['city_name']])
                                    ->toArray();
                            })
                            ->reactive()
                            ->required(),


                        Select::make('barangay')
                            ->label('Barangay')
                            ->options(function (callable $get) {
                                $barangays = json_decode(file_get_contents(storage_path('app/locations/barangay.json')), true);

                                return collect($barangays)
                                    ->where('city_code', $get('city'))
                                    ->mapWithKeys(fn($brgy) => [$brgy['brgy_code'] => $brgy['brgy_name']])
                                    ->toArray();
                            })
                            ->reactive()


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
                    ->collapsible()
                    //->collapsed()
                    ->columns(2)
                    ->schema([


                        DatePicker::make('membership_date')
                            ->native(false)
                            ->required()
                            ->maxDate(Carbon::now()),


                        Radio::make('membership_status')
                            ->inline()
                            ->options([
                                '0' => 'Active',
                                '1' => 'Inactive',

                            ])->default('0'),

                    ]),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table


            ->defaultSort('memberID', 'desc')
            ->paginated(10)
            ->modifyQueryUsing(
                fn($query) =>
                $query->with(['name', 'address', 'coordinator.name'])


            )
            ->columns([
                TextColumn::make('boligid')
                    ->label('#')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('profile')
                    ->label('Profile')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $name = optional($record->name)?->last_name . ', ' .
                            optional($record->name)?->first_name . ' ' .
                            optional($record->name)?->middle_name;

                        $address = optional($record->address)?->full_address;
                        $formattedAddress = str_replace(', ', '<br>', $address);

                        $phone = $record->phone;

                        return "
                        <div class='space-y-1 max-w-[250px] overflow-hidden text-ellipsis'>
                            <div class='truncate'>
                                <span class='font-semibold text-primary'>
                                    <i class='mr-1 fas fa-user'></i> 👤 {$name}
                                </span>
                            </div>
                            <div class='text-sm text-gray-600'>
                                <i class='mr-1 fas fa-map-marker-alt'></i>
                                <span class='inline-block bg-gray-100 text-gray-800 px-2 py-0.5 rounded'>
                                  📍  {$formattedAddress}
                                </span>
                            </div>
                            <div class='text-sm text-gray-600 truncate'> 📞
                                <i class='mr-1 fas fa-phone'></i>
                                <span class='inline-block bg-green-100 text-green-800 px-2 py-0.5 rounded'>
                                    {$phone}
                                </span>
                            </div>
                        </div>
                    ";
                    })
                    ->searchable(
                        query: function ($query, string $search) {
                            $query->whereHas('name', function ($q) use ($search) {
                                $q->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%")
                                    ->orWhere('middle_name', 'like', "%{$search}%");
                            })
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhereHas('address', function ($q) use ($search) {
                                    $q->where('street', 'like', "%{$search}%")
                                        ->orWhere('barangay', 'like', "%{$search}%")
                                        ->orWhere('city', 'like', "%{$search}%")
                                        ->orWhere('province', 'like', "%{$search}%")
                                        ->orWhere('region', 'like', "%{$search}%");
                                });
                        }
                    ),





                // TextColumn::make('address.full_address')
                //     ->label('Address')
                //     ->sortable()
                //     ->searchable()
                //     ->wrap(),


                ImageColumn::make('image_photo')

                    ->label('Photo')
                    ->disk('public')
                    ->circular()
                    ->size(50),

                TextColumn::make('membership_date')
                    ->searchable()
                    ->date()
                    ->label('Member Since'),



                TextColumn::make('coordinator.name.full_name')
                    ->label('Coordinator')
                    ->badge()
                    // ->searchable()
                    ->color('gray')
                    ->getStateUsing(fn($record) => optional($record->coordinator?->name)->full_name ?? '—'),
                TextColumn::make('membership_status')
                    ->badge()

                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                        'secondary' => 'deceased',
                    ]),
                TextColumn::make('membership_status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => static fn($state): bool => $state === 0,
                        'warning' => static fn($state): bool => $state === 1,
                        'danger' => static fn($state): bool => $state === 2,
                    ])
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        0 => 'Active',
                        1 => 'Inactive',
                        2 => 'Deceased',
                        default => 'Unknown',
                    }),

            ])
            ->filters([
                SelectFilter::make('membership_status')
                    ->label('Member Status')
                    ->options([
                        '0' => 'Active',
                        '1' => 'Inactive',
                        '2' => 'Deceased',
                    ])
                    ->default('0'),
            ])


            ->actions([

                Tables\Actions\Action::make('view_unpaid')
                    ->label('Unpaid Contributions')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => route('filament.admin.resources.members.unpaid', [
                        'record' => $record->getKey(),
                    ]))
                    ->openUrlInNewTab(),





                EditAction::make(),
                DeleteAction::make(),
            ])
            //  ->actionsAlignment('center')
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
            ContributionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
            'unpaid' => Pages\MemberUnpaidContributions::route('/{record}/unpaid'),
        ];
    }




    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('name');
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
