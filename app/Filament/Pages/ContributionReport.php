<?php

namespace App\Filament\Pages;

use App\Models\Contribution;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Barryvdh\DomPDF\Facade\Pdf;

class ContributionReport extends Page implements HasForms
{
    use \Filament\Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static string $view = 'filament.pages.contribution-report';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $title = 'Contribution Report';
    protected static ?int $navigationSort = 1;

    public ?string $fromDate = null;
    public ?string $toDate = null;
    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('fromDate')
                ->label('From')
                ->required(),
            DatePicker::make('toDate')
                ->label('To')
                ->required(),
        ];
    }

    public function getContributions()
    {
        return Contribution::with(['payer.name'])
            ->where('status', 1)
            ->when($this->fromDate, fn($q) => $q->where('payment_date', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->where('payment_date', '<=', $this->toDate))
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    public function getTotalAmount()
    {
        return $this->getContributions()->sum('amount');
    }
    public function getDailyTotals(): array
    {
        return $this->getContributions()
            ->groupBy(fn($item) => \Carbon\Carbon::parse($item->payment_date)->format('Y-m-d'))
            ->map(fn($group) => $group->sum('amount'))
            ->toArray();
    }
}
