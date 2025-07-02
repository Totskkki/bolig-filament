<?php

namespace App\Filament\Pages;

use Filament\Forms;
use App\Models\Member;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Livewire\Attributes\Url;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;

use Filament\Tables\Filters\SelectFilter;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

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
                )
                ->searchable()
                ->preload()
                ->live(debounce: 500)
                ->afterStateUpdated(function ($state, callable $set) {
                    $set('coordinator', $state);
                }),
        ];
    }




    protected function getTableQuery()
    {
        if (!$this->coordinator) {
            return Member::whereRaw('0 = 1');
        }

        return Member::where('coordinator_id', $this->coordinator)
            ->withSum(['contributions as contributions_sum_amount' => function ($query) {
                $query->where('status', 0); // Only sum unpaid contributions
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



    // protected function getTableFilters(): array
    // {
    //     return [
    //         SelectFilter::make('coordinator_id')
    //             ->label('Coordinator')
    //             ->options(
    //                 \App\Models\Member::with('name')
    //                     ->where('role', 'coordinator')
    //                     ->get()
    //                     ->mapWithKeys(fn($c) => [$c->memberID => $c->full_name])
    //             )
    //             ->searchable(),
    //     ];
    // }
}
