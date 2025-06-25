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


            // ->modifyQueryUsing(function ($query) {
            //     $filters = request()->input('tableFilters', []);
            //     $query = \App\Models\Contribution::query()->filteredGrouped($filters);

            //     //dd($query->toSql(), $query->getBindings());

            //     return $query;
            // })

            ->modifyQueryUsing(function () {
                $filters = request()->input('tableFilters', []);

                return \App\Models\Contribution::query()->filteredGrouped($filters);
            })


            ->columns([
                TextColumn::make('payer.name')
                    ->label('Payer')
                    ->getStateUsing(fn($record) => optional($record->payer->name)?->last_name . ', ' . optional($record->payer->name)?->first_name . ' ' . optional($record->payer->name)?->middle_name)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('payer.name', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('middle_name', 'like', "%{$search}%");
                        });
                    }),

                BadgeColumn::make('grouped_deceased_names')
                    ->label('Deceased')
                    ->html()
                    ->wrap(),

                TextColumn::make('total_unpaid_amount')
                    ->label('Unpaid Amount')
                    ->money('PHP', true),

                TextColumn::make('payment_date')
                    ->label('Last Payment Date')
                    ->date()
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        return $record->latest_unpaid_status == 1 && $state
                            ? \Carbon\Carbon::parse($state)->format('F j, Y')
                            : '—';
                    }),


                TextColumn::make('latest_unpaid_status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => fn($state): bool => (int) $state === 1,
                        'danger' => fn($state): bool => (int) $state === 0,
                    ])
                    ->formatStateUsing(fn($state): string => match ((int) $state) {
                        0 => 'Unpaid',
                        1 => 'Paid',
                        default => 'Unknown',
                    }),



                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->html()
                    ->formatStateUsing(function ($state) {
                        $parts = explode(';', $state);
                        $truncated = array_slice($parts, 0, 3);
                        $display = implode(';', $truncated);
                        return '<div style="font-size: 12px;" title="' . e($state) . '">' . e($display) . (count($parts) > 3 ? '...' : '') . '</div>';
                    })

                    ->wrap()
                    ->extraAttributes([
                        'style' => 'max-width: 400px; white-space: normal; word-wrap: break-word;',
                    ])
                    ->searchable(),

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
                    ->placeholder('Show All'),

                SelectFilter::make('year')
                    ->label('Year')
                    ->options(collect(range(2023, now()->year + 1))
                        ->mapWithKeys(fn($year) => [$year => $year])
                        ->toArray())
                    ->default(now()->year)
                    ->placeholder('Show All'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->default(0)
                    ->options([
                        1 => 'Paid',
                        0 => 'Unpaid',
                    ])
                    ->placeholder('Show All'),
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
                        // Update all unpaid contributions for this payer
                        \App\Models\Contribution::where('payer_memberID', $record->payer_memberID)
                            ->where('status', 0)
                            ->update([
                                'status' => 1,
                                'payment_date' => now(),
                            ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Payment Successful')
                            ->body('All unpaid contributions for this payer have been marked as paid.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn($record) => (int) ($record->latest_unpaid_status ?? 0) === 0),
            ])


            ->bulkActions([
                Tables\Actions\BulkAction::make('markAsPaid')
                    ->label('Mark as Paid')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            \App\Models\Contribution::where('payer_memberID', $record->payer_memberID)
                                ->where('status', 0)
                                ->update([
                                    'payment_date' => now(),
                                    'status' => 1,
                                ]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Bulk Payment Processed')
                            ->body('All selected payers\' unpaid contributions have been marked as paid.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('heroicon-o-currency-dollar'),
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
            'index' => Pages\ListContributions::route('/'),
            'create' => Pages\CreateContribution::route('/create'),
            'edit' => Pages\EditContribution::route('/{record}/edit'),
        ];
    }
}
