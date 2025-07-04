<?php

namespace App\Filament\Pages;

use App\Models\Contribution;
use App\Models\CoordinatorEarning;
use App\Models\Member;
use App\Models\SystemSetting;
use App\Models\User;
use Filament\Actions\Action;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;

use Filament\Forms\Contracts\HasForms;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

class CoordinatorContributions extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static string $view = 'filament.pages.coordinator-contributions';
    protected static ?string $title = 'Coordinator Contributions';
    protected static ?string $navigationGroup = 'Payables';
    protected static ?int $navigationSort = 2;

    #[Url(as: 'coordinator', keep: true)]
    public ?string $coordinator = null;
    public ?string $filterMonth = null;
public bool $showPaid = false;





   protected function getFormSchema(): array
{
    return [
        Forms\Components\Grid::make(2)
            ->schema([
                Forms\Components\Select::make('coordinator')
                    ->label('Select Coordinator')
                    ->options(
                        Member::with('name')
                            ->where('role', 'coordinator')
                            ->get()
                            ->mapWithKeys(fn($c) => [$c->memberID => $c->full_name])
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('coordinator', $state);
                    }),

                Forms\Components\Select::make('filterMonth')
                    ->label('Filter by Month')
                    ->options([
                        '01' => 'January',
                        '02' => 'February',
                        '03' => 'March',
                        '04' => 'April',
                        '05' => 'May',
                        '06' => 'June',
                        '07' => 'July',
                        '08' => 'August',
                        '09' => 'September',
                        '10' => 'October',
                        '11' => 'November',
                        '12' => 'December',
                    ])
                    ->placeholder('All Months')
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn($state) => $this->filterMonth = $state),
            ]),
    ];
}




    // protected function getTableQuery()
    // {
    //     if (!$this->coordinator) {
    //         return Member::whereRaw('0 = 1');
    //     }

    //     return Member::where('coordinator_id', $this->coordinator)
    //         ->withSum(['contributions as contributions_sum_amount' => function ($query) {
    //             $query->where('status', 0);
    //         }], 'amount');
    // }

    // protected function getTableQuery()
    // {
    //     if (!$this->coordinator) {
    //         return Member::whereRaw('0 = 1');
    //     }

    //     return Member::where('coordinator_id', $this->coordinator)
    //         ->whereHas('contributions', function ($query) {
    //             $query->where('status', 0);
    //         })
    //         ->withSum(['contributions as contributions_sum_amount' => function ($query) {
    //             $query->where('status', 0);
    //         }], 'amount');
    // }

    protected function getTableQuery()
{
    if (!$this->coordinator) {
        return Member::whereRaw('0 = 1');
    }

    return Member::where('coordinator_id', $this->coordinator)
        ->whereHas('contributions', function ($query) {
           // $query->where('status', $this->showPaid ? 1 : 0);

            if ($this->filterMonth) {
                $query->where('month', $this->filterMonth);
            }
        })
        ->withSum(['contributions as contributions_sum_amount' => function ($query) {
            //$query->where('status', $this->showPaid ? 1 : 0);

            if ($this->filterMonth) {
                $query->where('month', $this->filterMonth);
            }
        }], 'amount');
}





    public function getCoordinatorsProperty()
    {
        return Member::with('name')
            ->where('role', 'coordinator')
            ->get()
            ->mapWithKeys(fn($c) => [$c->memberID => $c->full_name]);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('full_name')->label('Member Name'),
            TextColumn::make('phone')->label('Contact'),
            TextColumn::make('membership_date')->date()->label('Member Since'),
            TextColumn::make('contributions_sum_amount')
                ->label('Total Contributions')
                ->money('PHP'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to Dashboard')
                ->url(route('filament.admin.pages.dashboard'))
                ->color('gray'),
        ];
    }

    public function updatedCoordinator()
    {
        $this->resetTable();
    }



    protected function getTableBulkActions(): array
    {
        return [
            BulkAction::make('bulkPay')
                ->label('Pay Selected')
                ->color('success')
                ->icon('heroicon-s-currency-dollar')
                ->requiresConfirmation()
                ->action(fn(Collection $records) => $this->processBulkPayments($records)),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            TableAction::make('pay')
                ->label('Pay')
                ->color('success')
                ->icon('heroicon-s-currency-dollar')
                ->requiresConfirmation()
                ->visible(fn($record) => $record->contributions_sum_amount > 0)
                ->action(function ($record) {
                    $this->processPayment($record);
                }),
        ];
    }

    public function processPayment($member)
    {
        DB::transaction(function () use ($member) {
            $sharePercentage = SystemSetting::where('key', 'coordinator_share_percentage')->value('value') ?? 12;
            $unpaidContributions = Contribution::where('payer_memberID', $member->memberID)
                ->where('status', 0)
                ->get();

            if ($unpaidContributions->isEmpty()) {
                return;
            }

            $total = $unpaidContributions->sum('amount');

            foreach ($unpaidContributions as $contribution) {
                $contribution->update([
                    'status' => 1,
                    'payment_date' => now(),
                    'coordinator_id' => $this->coordinator,
                ]);
            }

            CoordinatorEarning::create([
                'contribution_id' => $unpaidContributions->first()->consid,
                'coordinator_id' => $this->coordinator,
                'share_amount' => $total * ((float)$sharePercentage / 100),
            ]);
            User::logAudit(
                'Payment',
                "Processed payment of ₱{$contribution->amount} for member {$member->full_name} via coordinator ID {$this->coordinator}"
            );
        });

        Notification::make()
            ->title('Payment Complete')
            ->success()
            ->send();


        $this->resetTable();
    }



    public function processBulkPayments(Collection $members)
    {
        DB::transaction(function () use ($members) {
            $sharePercentage = SystemSetting::where('key', 'coordinator_share_percentage')->value('value') ?? 12;
            foreach ($members as $member) {
                $unpaidContributions = Contribution::where('payer_memberID', $member->memberID)
                    ->where('status', 0)
                    ->get();

                if ($unpaidContributions->isEmpty()) {
                    continue;
                }

                $total = $unpaidContributions->sum('amount');

                foreach ($unpaidContributions as $contribution) {
                    $contribution->update([
                        'status' => 1,
                        'payment_date' => now(),
                        'coordinator_id' => $this->coordinator,
                    ]);
                }

                CoordinatorEarning::create([
                    'contribution_id' => $unpaidContributions->first()->consid,
                    'coordinator_id' => $this->coordinator,
                    'share_amount' => $total * ((float)$sharePercentage / 100),
                ]);

                User::logAudit(
                    'Bulk Payment',
                    "Processed payment of ₱{$total} for member {$member->full_name} via coordinator ID {$this->coordinator}"
                );
            }
        });

        Notification::make()
            ->title('Bulk Payment Complete')
            ->success()
            ->send();

        $this->resetTable();
    }
}
