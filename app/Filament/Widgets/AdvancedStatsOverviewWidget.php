<?php

namespace App\Filament\Widgets;

use App\Models\Member;
use App\Models\Contribution;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        return [
            Stat::make('Total Members', Member::count())
                ->icon('heroicon-o-user-group')
                ->backgroundColor('info')
                ->iconBackgroundColor('white')
                ->iconColor('info')
                ->progress(100)
                ->progressBarColor('white')
                ->chartColor('white')
                ->iconPosition('start')
                ->description('All registered members')
                ->descriptionIcon('heroicon-o-users')
                ->descriptionColor('white'),

            Stat::make('Active Members', Member::where('membership_status', '0')->count())
                ->icon('heroicon-o-check-circle')
                ->backgroundColor('success')
                ->iconBackgroundColor('white')
                ->iconColor('success')
                ->progress(100)
                ->progressBarColor('white')
                ->chartColor('white')
                ->iconPosition('start')
                ->description('Currently active members')
                ->descriptionIcon('heroicon-o-check')
                ->descriptionColor('white'),

            Stat::make('Inactive Members', Member::where('membership_status', '1')->count())
                ->icon('heroicon-o-pause-circle')
                ->backgroundColor('warning')
                ->iconBackgroundColor('white')
                ->iconColor('warning')
                ->progress(100)
                ->progressBarColor('white')
                ->chartColor('white')
                ->iconPosition('start')
                ->description('No longer active')
                ->descriptionIcon('heroicon-o-pause')
                ->descriptionColor('white'),

            Stat::make('Deceased Members', Member::where('membership_status', '2')->count())
                ->icon('heroicon-o-x-circle')
                ->backgroundColor('danger')
                ->iconBackgroundColor('white')
                ->iconColor('danger')
                ->progress(100)
                ->progressBarColor('white')
                ->chartColor('white')
                ->iconPosition('start')
                ->description('Marked as deceased')
                ->descriptionIcon('heroicon-o-x-mark')
                ->descriptionColor('white'),

            Stat::make('Total Contributions', '₱' . number_format(Contribution::where('status', '1')->sum('amount'), 2))
                ->icon('heroicon-o-banknotes')
                ->backgroundColor('success')
                ->iconBackgroundColor('white')
                ->iconColor('success')
                ->progress(100)
                ->progressBarColor('white')
                ->chartColor('white')
                ->iconPosition('start')
                ->description('Paid Contributions')
                ->descriptionIcon('heroicon-o-check')
                ->descriptionColor('white'),

            Stat::make('Unpaid Contributions', '₱' . number_format(Contribution::where('status', '0')->sum('amount'), 2))
                ->icon('heroicon-o-clock')
                ->backgroundColor('warning')
                ->iconBackgroundColor('white')
                ->iconColor('warning')
                ->progress(100)
                ->progressBarColor('white')
                ->chartColor('white')
                ->iconPosition('start')
                ->description('Still to collect')
                ->descriptionIcon('heroicon-o-exclamation-circle')
                ->descriptionColor('white'),



        ];
    }
}
