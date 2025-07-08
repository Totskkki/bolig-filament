<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeceasedResource\Pages;

use App\Models\Deceased;


use App\Models\Member;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class DeceasedResource extends Resource
{
    protected static ?string $model = Deceased::class;

    // protected static ?string $navigationIcon = 'fas-skull';

    // protected static ?string $navigationLabel = 'Deceased';
    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return new HtmlString(view('components.icons.deceased-icon')->render());
    }


    public static function getModelLabel(): string
    {
        return 'Deceased';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Deceased';
    }


    public static function form(Form $form): Form
    {
        return $form

            ->schema([
                Grid::make(1)
                    ->schema([

                        Select::make('member_id')
                            ->label('Member Name')
                            ->searchable()
                            ->required()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\Member::with('name')
                                    ->where('membership_status', '0')
                                    ->whereHas('name', function ($query) use ($search) {
                                        $query->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name, COALESCE(suffix, '')) LIKE ?", ["%{$search}%"]);
                                    })
                                    ->limit(20)
                                    ->get()
                                    ->mapWithKeys(function ($member) {
                                        $name = $member->name;
                                        if (!$name) return [];

                                        $fullName = implode(' ', array_filter([
                                            $name->first_name,
                                            $name->middle_name,
                                            $name->last_name,
                                            $name->suffix,
                                        ]));

                                        return [$member->memberID => $fullName];
                                    });
                            })

                            ->getOptionLabelUsing(function ($value): ?string {
                                $member = \App\Models\Member::with('name')->find($value);
                                $name = $member?->name;

                                if (!$name) return null;

                                return implode(' ', array_filter([
                                    $name->first_name,
                                    $name->middle_name,
                                    $name->last_name,
                                    $name->suffix,
                                ]));
                            })
                            ->required(),
                        DatePicker::make('date_of_death')
                            ->label('Date of Death')
                            ->maxDate(\Carbon\Carbon::today())
                            ->native(false)
                            ->required(),

                        // Textarea::make('cause_of_death')
                        //     ->label('Cause of Death')
                        //     ->rows(3)
                        //     ->required()
                        //     ->regex('/^[a-zA-Z\s\-]+$/')
                        //     ->validationMessages([
                        //         'regex' => 'Only letters are allowed.',

                        //     ]),
                        RichEditor::make('cause_of_death')

                            ->fileAttachmentsDisk('s3')
                            ->fileAttachmentsDirectory('attachments')
                            ->fileAttachmentsVisibility('private'),

                    ]),
            ]);
    }




    public static function table(Table $table): Table
    {
        return $table

            ->paginated(10)
            ->defaultSort('deceasedID', 'desc')
            ->modifyQueryUsing(
                fn($query) =>
                $query
                    ->with(['member.name', 'member.address', 'contributions'])


            )
            ->columns([
                TextColumn::make('member.boligid')
                    ->label('Member ID'),
                TextColumn::make('member.name')
                    ->label('Name')
                    ->sortable(['last_name', 'first_name', 'middle_name'])
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('member.name', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('middle_name', 'like', "%{$search}%");
                        });
                    })
                    ->getStateUsing(function ($record) {
                        $name = optional($record->member?->name);
                        return $name->last_name . ', ' . $name->first_name . ' ' . $name->middle_name;
                    }),

                TextColumn::make('date_of_death')->date()->label('Date of Death'),
                TextColumn::make('cause_of_death')->label('Cause of Death')
                    ->html()
                    ->wrap(),
                TextColumn::make('contributions.0.release_status')
                    ->label('Payment Status')
                    ->badge()
                    ->colors([
                        'warning' => static fn($state): bool => $state === 0,
                        'success' => static fn($state): bool => $state === 1,
                        'danger' => static fn($state): bool => $state === 2,
                    ])
                    ->formatStateUsing(fn($state): string => match ($state) {
                        0 => 'Pending',
                        1 => 'Paid',
                        2 => 'Partial Payment',
                        default => 'Unknown',
                    }),






            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth('md'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDeceaseds::route('/'),
        ];
    }

    //    public static function mutateFormDataUsing(array $data): array
    // {
    //     $date = \Carbon\Carbon::parse($data['date_of_death']);
    //     $data['month'] = $date->format('m');
    //     $data['year'] = $date->format('Y');

    //     return $data;
    // }

}
