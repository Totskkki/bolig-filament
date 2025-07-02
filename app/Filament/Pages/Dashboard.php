<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Forms\Components\Select;
use App\Models\Member;

class Dashboard extends BaseDashboard
{
    public function filterForm(): array
    {
        return [
            Select::make('coordinator_id')
                ->label('Coordinator')
                ->searchable()
                ->options(
                    Member::with('name')
                        ->where('role', 'coordinator')
                        ->get()
                        ->pluck('full_name', 'memberID')
                )
                ->placeholder('All Coordinators'),
        ];
    }

    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         \App\Filament\Widgets\AdvancedStatsOverviewWidget::class,
    //         \App\Filament\Widgets\MonthlyContributionsChart::class,
    //     ];
    // }
}
