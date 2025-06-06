<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContributionResource\Pages;
use App\Filament\Resources\ContributionResource\RelationManagers;
use App\Models\Contribution;
use App\Models\Deceased;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
            ->defaultSort('consid', 'desc')
            ->modifyQueryUsing(function ($query) {
                return Contribution::groupByPayer($query)
                    ->with([
                        'payer.user',
                        'payer.contributions.deceased.member.user',
                    ]);
            })


            ->columns([
                TextColumn::make('payer.user.name')
                    //->sortable(['last_name', 'first_name', 'middle_name'])
                    ->label('Payer')
                    ->getStateUsing(fn($record) => optional($record->payer?->user?->name)
                        ?->last_name . ', ' . optional($record->payer?->user?->name)->first_name . ' ' . optional($record->payer?->user?->name)->middle_name)
                    ->searchable(
                        query: function ($query, string $search) {
                            $query->whereHas('payer.user.name', function ($q) use ($search) {
                                $q->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%")
                                    ->orWhere('middle_name', 'like', "%{$search}%");
                            });
                        }
                    ),
                BadgeColumn::make('payer.grouped_deceased_names')
                    ->label('Deceased')
                    ->wrap()
                    ->limit(100)
                    ->colors([
                        'danger' => true,
                    ]),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP', true),

                TextColumn::make('payment_date')->label('Payment Date')->date(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => static fn($state): bool => $state === 1, // 'success' when status is 1 (paid)
                        'danger' => static fn($state): bool => $state === 0,  // 'danger' when status is 0 (pending)
                    ])
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        0 => 'unpaid',
                        1 => 'Paid',
                        default => 'Unknown',
                    }),

                TextColumn::make('remarks')->wrap(),
            ])
         ->filters([
    Tables\Filters\SelectFilter::make('status')
        ->label('Payment Status')
        ->options([
            '' => 'All',
            '0' => 'Unpaid',
            '1' => 'Paid',
        ])
        ->query(function ($query, array $data) {
            if (!isset($data['value']) || $data['value'] === '') {
                return $query;
            }

            return $query->where('status', $data['value']);
        }),
])



            ->actions([
                Tables\Actions\EditAction::make(),
            ])


            ->bulkActions([
                BulkAction::make('markAsPaid')
                    ->label('Mark as Paid')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            // Since $record is a grouped row, get all contributions by this payer
                            Contribution::where('payer_memberID', $record->payer_memberID)
                                ->update([
                                    'payment_date' => now(),
                                    'status' => 1,
                                ]);
                        }
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
