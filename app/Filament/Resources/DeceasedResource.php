<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeceasedResource\Pages;
use App\Filament\Resources\DeceasedResource\RelationManagers;
use App\Forms\Components\MemberPicker;
use App\Models\Deceased;
use App\Models\Member;
use Filament\Forms;
use App\Models\Contribution;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;

use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;


class DeceasedResource extends Resource
{
    protected static ?string $model = Deceased::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Select::make('memberID')
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

                                return [$member->id => $fullName];
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

                Textarea::make('cause_of_death')
                    ->label('Cause of Death')
                    ->rows(3)
                    ->required()
                     ->regex('/^[a-zA-Z\s\-]+$/')
                      ->validationMessages([
                                'regex' => 'Only letters are allowed.',
                            ]),
            ]);
    }




    public static function table(Table $table): Table
    {
        return $table

            ->paginated(10)
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(
                fn($query) =>
                $query
                    ->with(['member.name', 'member.address'])
                // ->whereHas('member', function ($q) {
                //     $q->where('role', 'member');
                // })
            )
            ->columns([

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
                TextColumn::make('cause_of_death')->label('Cause of Death'),
                TextColumn::make('created_at')
                    ->label('Created at')
                    ->since(),




            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
