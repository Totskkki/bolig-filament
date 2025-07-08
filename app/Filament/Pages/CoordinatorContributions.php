<?php

namespace App\Filament\Pages;

use App\Models\Contribution;
use App\Models\CoordinatorEarning;
use App\Models\Member;
use App\Models\SystemSetting;
use App\Models\User;


use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;

use Filament\Forms\Contracts\HasForms;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CoordinatorContributions extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static string $view = 'filament.pages.coordinator-contributions';
    protected static ?string $title = 'Coordinator Contributions';
    protected static ?string $navigationGroup = 'Payables';
    protected static ?int $navigationSort = 2;



    public static function getNavigationIcon(): string | Htmlable | null
    {
        return new HtmlString(view('components.icons.coordinators-icon')->render());
    }


    #[Url(as: 'coordinator', keep: true)]
    public ?string $coordinator = null;
    public ?string $filterMonth = null;
    public bool $showPaid = false;

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('coordinator')
                ->label('Select Coordinator')
                ->options(
                    Member::with('name')
                        ->where('role', 'coordinator')
                        ->get()
                        ->mapWithKeys(fn($c) => [$c->memberID => $c->full_name])
                        ->toArray()
                )
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    $set('coordinator', $state);
                }),
        ];
    }



    // protected function getTableQuery()
    // {
    //     if (!$this->coordinator) {
    //         return Member::whereRaw('0 = 1');
    //     }

    //     return Member::where('coordinator_id', $this->coordinator)
    //         ->whereHas('contributions', function ($query) {
    //             $query->where('status', 0); // only unpaid
    //             if ($this->filterMonth) {
    //                 $query->where('month', $this->filterMonth);
    //             }
    //         })
    //         ->withSum(['contributions as contributions_sum_amount' => function ($query) {
    //             $query->where('status', 0); // unpaid only
    //             if ($this->filterMonth) {
    //                 $query->where('month', $this->filterMonth);
    //             }
    //         }], 'amount');
    // }

    protected function getTableQuery()
    {
        if (!$this->coordinator) {
            return Member::whereRaw('0 = 1');
        }

        return Member::where(function ($query) {
            $query->where('coordinator_id', $this->coordinator)
                ->orWhere('memberID', $this->coordinator); // 👈 include the coordinator themself
        })
            ->whereHas('contributions', function ($query) {
                $query->where('status', 0); // unpaid only
                if ($this->filterMonth) {
                    $query->where('month', $this->filterMonth);
                }
            })
            ->with('coordinator')
            ->withSum(['contributions as contributions_sum_amount' => function ($query) {
                $query->where('status', 0);
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
            TextColumn::make('full_name')
                ->label('Member Name')
                ->searchable(query: function ($query, $search) {
                    $query->whereHas('name', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%");
                    });
                }),

            TextColumn::make('phone')->label('Contact'),
            TextColumn::make('membership_date')->date()->label('Member Since'),
            TextColumn::make('contributions_sum_amount')
                ->label('Total Contributions')
                ->money('PHP'),
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
            BulkAction::make('printBulkReceipt')
                ->label('Print Receipts')
                ->icon('heroicon-o-printer')
                ->action(function (\Illuminate\Support\Collection $records) {
                    $ids = $records->pluck('memberID')->implode(',');
                    return redirect()->route('coordinator.print-receipt', ['members' => $ids]);
                }),
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
                ->after(function ($record) {
                    $url = route('coordinator.print-receipt', ['members' => $record->memberID]);

                    // Inject a browser redirect via notification or JS hook
                    Notification::make()
                        ->title('Redirecting to print receipt...')
                        ->success()
                        ->body("<script>window.open('{$url}', '_blank');</script>")
                        ->send();
                })
                ->action(fn($record) => $this->processPayment($record)),
            // TableAction::make('pay')
            //     ->label('Pay')
            //     ->color('success')
            //     ->icon('heroicon-s-currency-dollar')
            //     ->requiresConfirmation()
            //     ->visible(fn($record) => $record->contributions_sum_amount > 0)
            //     ->action(function ($record) {
            //         $this->processPayment($record);
            //     }),
            // Action::make('printReceipt')
            //     ->label('Print Receipt')
            //     ->icon('heroicon-o-printer')
            //     ->url(fn($record) => route('coordinator.print-receipt', ['members' => $record->memberID]))
            //     ->openUrlInNewTab(),
        ];
    }

    // public function processPayment($member)
    // {
    //     DB::transaction(function () use ($member) {
    //         $sharePercentage = SystemSetting::where('key', 'coordinator_share_percentage')->value('value') ?? 12;
    //         $unpaidContributions = Contribution::where('payer_memberID', $member->memberID)
    //             ->where('status', 0)
    //             ->get();

    //         if ($unpaidContributions->isEmpty()) {
    //             return;
    //         }

    //         $total = $unpaidContributions->sum('amount');

    //         foreach ($unpaidContributions as $contribution) {
    //             $contribution->update([
    //                 'status' => 1,
    //                 'payment_date' => now(),
    //                 'coordinator_id' => $this->coordinator,
    //             ]);
    //         }

    //         CoordinatorEarning::create([
    //             'contribution_id' => $unpaidContributions->first()->consid,
    //             'coordinator_id' => $this->coordinator,
    //             'share_amount' => $total * ((float)$sharePercentage / 100),
    //         ]);
    //         User::logAudit(
    //             'Payment',
    //             "Processed payment of ₱{$contribution->amount} for member {$member->full_name} via coordinator ID {$this->coordinator}"
    //         );
    //     });

    //     Notification::make()
    //         ->title('Payment Complete')
    //         ->success()
    //         ->send();


    //     // $this->resetTable();
    //     return redirect()->route('coordinator.print-receipt', ['members' => $member->memberID]);
    // }

    // public function processPayment($member)
    // {
    //     DB::transaction(function () use ($member) {
    //         $sharePercentage = SystemSetting::where('key', 'coordinator_share_percentage')->value('value') ?? 12;
    //         $unpaidContributions = Contribution::where('payer_memberID', $member->memberID)
    //             ->where('status', 0)
    //             ->get();

    //         if ($unpaidContributions->isEmpty()) {
    //             return;
    //         }

    //         $total = $unpaidContributions->sum('amount');

    //         foreach ($unpaidContributions as $contribution) {
    //             $contribution->update([
    //                 'status' => 1,
    //                 'payment_date' => now(),
    //                 'coordinator_id' => $this->coordinator,
    //             ]);
    //         }

    //         CoordinatorEarning::create([
    //             'contribution_id' => $unpaidContributions->first()->consid,
    //             'coordinator_id' => $this->coordinator,
    //             'share_amount' => $total * ((float)$sharePercentage / 100),
    //         ]);

    //         User::logAudit(
    //             'Payment',
    //             "Processed payment of ₱{$total} for member {$member->full_name} via coordinator ID {$this->coordinator}"
    //         );
    //     });

    //     // Skip notification if you want direct redirect
    //     return redirect()->route('coordinator.print-receipt', [
    //         'members' => $member->memberID,
    //         'coordinator' => $this->coordinator,
    //     ]);
    // }

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
                "Processed payment of ₱{$total} for member {$member->full_name} via coordinator ID {$this->coordinator}"
            );
        });

        // Generate unique receipt ref
        $receiptRef = 'RCPT-' . now()->format('YmdHis') . '-' . $member->memberID;

        return redirect()->route('coordinator.print-receipt', [
            'members' => $member->memberID,
            'coordinator' => $this->coordinator,
            'ref' => $receiptRef, // ✅ pass this to generate QR
        ]);
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

        $ids = $members->pluck('memberID')->implode(',');
        $receiptRef = 'RCPT-' . now()->format('YmdHis') . '-bulk';

        return redirect()->route('coordinator.print-receipt', [
            'members' => $ids,
            'coordinator' => $this->coordinator,
            'ref' => $receiptRef, // ✅ pass this too
        ]);
    }


    // public function processBulkPayments(Collection $members)
    // {
    //     DB::transaction(function () use ($members) {
    //         $sharePercentage = SystemSetting::where('key', 'coordinator_share_percentage')->value('value') ?? 12;
    //         foreach ($members as $member) {
    //             $unpaidContributions = Contribution::where('payer_memberID', $member->memberID)
    //                 ->where('status', 0)
    //                 ->get();

    //             if ($unpaidContributions->isEmpty()) {
    //                 continue;
    //             }

    //             $total = $unpaidContributions->sum('amount');

    //             foreach ($unpaidContributions as $contribution) {
    //                 $contribution->update([
    //                     'status' => 1,
    //                     'payment_date' => now(),
    //                     'coordinator_id' => $this->coordinator,
    //                 ]);
    //             }

    //             CoordinatorEarning::create([
    //                 'contribution_id' => $unpaidContributions->first()->consid,
    //                 'coordinator_id' => $this->coordinator,
    //                 'share_amount' => $total * ((float)$sharePercentage / 100),
    //             ]);

    //             User::logAudit(
    //                 'Bulk Payment',
    //                 "Processed payment of ₱{$total} for member {$member->full_name} via coordinator ID {$this->coordinator}"
    //             );
    //         }
    //     });

    //     Notification::make()
    //         ->title('Bulk Payment Complete')
    //         ->success()
    //         ->send();

    //     // $this->resetTable();
    //     $ids = $members->pluck('memberID')->implode(',');
    //     return redirect()->route('coordinator.print-receipt', [
    //         'members' => $ids,
    //         'coordinator' => $this->coordinator,
    //     ]);
    // }
}
