<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class ContributionsRelationManager extends RelationManager
{
    protected static string $relationship = 'contributions'; // relationship method name in Member model

    protected static ?string $title = 'Contributions';

    public  function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state == 1 ? 'Paid' : 'Unpaid')
                    ->colors([
                        'success' => fn($state) => $state == 1,
                        'danger' => fn($state) => $state == 0,
                    ]),

                // Optional: Show deceased name
                TextColumn::make('deceased.member.name.full_name')
                    ->label('Deceased')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('payment_date', 'desc')
            ->modifyQueryUsing(fn($query) => $query->with('deceased.member.name'));
    }
}
