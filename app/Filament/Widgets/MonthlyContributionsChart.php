<?php
namespace App\Filament\Widgets;

use App\Models\Contribution;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MonthlyContributionsChart extends ChartWidget
{
    protected static ?string $heading = '📊 Monthly Collection Trend';
    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = collect([]);
        $labels = collect([]);

        for ($i = 1; $i <= 12; $i++) {
            $month = Carbon::create()->month($i)->format('F');
            $labels->push($month);

            $monthlyTotal = Contribution::where('status', '1')
                ->whereMonth('payment_date', $i)
                ->whereYear('payment_date', now()->year)
                ->sum('amount');

            $data->push($monthlyTotal);
        }

        return [
            'datasets' => [
                [
                    'label' => '₱ Collected',
                    'data' => $data->toArray(),
                    'backgroundColor' => '#22c55e',
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
