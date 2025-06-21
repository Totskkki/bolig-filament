<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContributionResource\Pages;
use App\Filament\Resources\ContributionResource\RelationManagers;
use App\Models\Contribution;
use App\Models\Deceased;
use App\Models\Member;
use Filament\Forms;


use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;

use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;

use Filament\Tables\Filters\SelectFilter;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;


class ContributionResource extends Resource
{
    protected static ?string $model = Contribution::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 4;

    // public static function getTableQuery(): Builder
    // {
    //     return Contribution::query()
    //         ->with(['payer.deceased']); // Eager load payer and their deceased
    // }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            //->defaultSort('consid', 'desc')
            ->modifyQueryUsing(function ($query) {
                $filters = request()->input('tableFilters', []);
                $year = $filters['year'] ?? null;
                $month = $filters['month'] ?? null;
                $status = $filters['status'] ?? null;

                $query = Member::query()
                    ->with([
                        'name',
                        'contributions.deceased.member.name',
                    ])
                    ->addSelect([
                        'payment_date' => \App\Models\Contribution::select('payment_date')
                            ->whereColumn('payer_memberID', 'members.id') // ✅ correct join
                            ->where('status', 1)
                            ->latest('payment_date')
                            ->limit(1),
                    ]);

                // ✅ Always filter through contributions
                $query->whereHas('contributions', function ($q) use ($status, $month, $year) {
                    if (!is_null($status) && $status !== '') {
                        $q->where('status', (int) $status);
                    }

                    if (!empty($month)) {
                        $month = is_array($month) ? $month[0] : $month; // 🛡️ Safe guard
                        $q->where('month', str_pad((string)$month, 2, '0', STR_PAD_LEFT));
                    }

                    if (!empty($year)) {
                        $q->where('year', $year);
                    }
                });

                return $query;
            })




            ->columns([
                // TextColumn::make('memberID')
                // ->label('#'),
                TextColumn::make('name')
                    ->label('Payer')
                    ->getStateUsing(fn($record) => optional($record->name)?->last_name . ', ' . optional($record->name)?->first_name . ' ' . optional($record->name)?->middle_name)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('name', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('middle_name', 'like', "%{$search}%");
                        });
                    }),

                BadgeColumn::make('grouped_deceased_names')
                    ->label('Deceased')
                    ->colors([
                        'success' => fn($state): bool => filled($state),
                    ])
                    ->html()
                    ->wrap()
                    ->extraAttributes([
                        'style' => 'max-width: 150px; white-space: normal; word-break: break-word;',
                    ]),

                TextColumn::make('total_unpaid_amount')
                    ->label('Unpaid Amount')

                    ->money('PHP', true),
                TextColumn::make('payment_date')
                    ->label('Last Payment Date')
                    ->date()
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('F j, Y') : '—'),

                TextColumn::make('latest_unpaid_status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => static fn($state): bool => $state === 1,
                        'danger' => static fn($state): bool => $state === 0,
                    ])
                    ->formatStateUsing(fn($state): string => match ($state) {
                        0 => 'Unpaid',
                        1 => 'Paid',
                        default => 'Unknown',
                    }),

                TextColumn::make('contributions.remarks')
                    ->label('Remarks')
                    ->wrap()
                    ->limit(50)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('contributions', function ($q) use ($search) {
                            $q->where('remarks', 'like', "%{$search}%");
                        });
                    }),



            ])
            ->filters([

                SelectFilter::make('month')
                    ->label('Month')
                    ->options([
                        1 => 'January',
                        2 => 'February',
                        3 => 'March',
                        4 => 'April',
                        5 => 'May',
                        6 => 'June',
                        7 => 'July',
                        8 => 'August',
                        9 => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December',
                    ])
                    ->placeholder('Show All')
                    ->query(fn($query) => $query),

                SelectFilter::make('year')
                    ->label('Year')
                    ->options(collect(range(2023, now()->year + 1))
                        ->mapWithKeys(fn($year) => [$year => $year])
                        ->toArray())
                    ->default(now()->year)
                    ->placeholder('Show All')
                    ->query(fn($query) => $query),

                SelectFilter::make('status')
                    ->label('Status')
                    ->default(0)
                    ->options([
                        1 => 'Paid',
                        0 => 'Unpaid',
                    ])


                    ->placeholder('Show All')
                    ->query(fn($query) => $query),
            ], layout: FiltersLayout::AboveContent)

            ->headerActions([
                Action::make('summary')
                    ->label(fn() => 'Paid: ' . \App\Models\Contribution::where('status', 1)->count()
                        . ' | Unpaid: ' . \App\Models\Contribution::where('status', 0)->count())
                    ->disabled()
                    ->color('gray')
                    ->extraAttributes(['style' => 'cursor: default']),

                ExportAction::make('export')
                    ->label('Export Records')
                    ->color('primary'),
            ])

            ->actions([
                Tables\Actions\Action::make('payContribution')
                    ->label('Pay Contribution')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->action(function ($record) {
                        foreach ($record->unpaidContributions as $contribution) {
                            $contribution->update([
                                'status' => 1,
                                'payment_date' => now(),
                            ]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Payment Successful')
                            ->body('All unpaid contributions have been marked as paid.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->unpaidContributions->isNotEmpty()),

                Tables\Actions\EditAction::make(),
            ])



            ->bulkActions([
                Tables\Actions\BulkAction::make('markAsPaid')
                    ->label('Mark as Paid')
                    ->action(function ($records) {
                        foreach ($records as $member) {
                            foreach ($member->unpaidContributions as $contribution) {
                                $contribution->update([
                                    'payment_date' => now(),
                                    'status' => 1,
                                ]);
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Bulk Payment Processed')
                            ->body('All selected members\' unpaid contributions are now marked as paid.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('heroicon-o-currency-dollar'),
            ])

        ;
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
            'index' => Pages\ListContributions::route('/'),
            'create' => Pages\CreateContribution::route('/create'),
            'edit' => Pages\EditContribution::route('/{record}/edit'),
        ];
    }
}
