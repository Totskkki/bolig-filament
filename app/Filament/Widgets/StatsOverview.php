<?php

namespace App\Filament\Widgets;

use App\Models\Member;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getCards(): array
    {
        return [
            Card::make('Total Members', Member::count())
                ->description('All registered members')
                ->descriptionIcon('heroicon-m-users')
                ->chart([10, 12, 15, 18, 19, 20, 21]) // Mock data or calculate dynamically
                ->color('primary'),

            Card::make('Active Members', Member::where('membership_status', 'active')->count())
                ->description('Currently active')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart([5, 7, 8, 10, 12, 13, 15]) // Sample trend
                ->color('success'),

            Card::make('Inactive Members', Member::where('membership_status', 'inactive')->count())
                ->description('No longer active')
                ->descriptionIcon('heroicon-m-pause-circle')
                ->chart([4, 3, 2, 3, 4, 3, 2])
                ->color('warning'),

            Card::make('Deceased Members', Member::where('membership_status', 'deceased')->count())
                ->description('Marked as deceased')
                ->descriptionIcon('heroicon-m-x-circle')
                ->chart([1, 1, 1, 2, 2, 3, 3])
                ->color('danger'),
        ];
    }

    protected function getColumns(): int
    {
        return 2; // 2 or 3 depending on layout space
    }
}
