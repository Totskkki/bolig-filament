<?php

namespace App\Filament\Pages;

use App\Models\Contribution;
use App\Models\Deceased;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\WithFileUploads;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;


class ReleasingMoney extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static string $view = 'filament.pages.releasing-money';
    protected static ?string $navigationLabel = 'Releasing';
    protected static ?string $navigationGroup = 'Payables';
    protected static ?int $navigationSort = 5;

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return new HtmlString(view('components.icons.money-icon')->render());
    }

    public Collection $groups;
    public ?int $selectedDeceasedId = null;
    public $receiptFile;

    public function mount(): void
    {
        $this->loadGroups();
    }

    public function loadGroups(): void
    {
        $this->groups = Deceased::with([
            'member',
            'contributions' => fn($q) => $q->where('status', '1')
        ])->get();
    }


    public function openConfirmModal(int $deceasedId): void
    {
        $this->selectedDeceasedId = $deceasedId;
        $this->dispatch('open-modal', id: 'confirm-release-modal');
    }

    public function confirmRelease(): void
    {
        if (!$this->selectedDeceasedId) return;

        // Find the deceased record from the already loaded groups collection
        $deceased = $this->groups->firstWhere('deceasedID', $this->selectedDeceasedId);

        if (!$deceased) {
            Notification::make()
                ->title("Deceased record not found.")
                ->danger()
                ->send();
            return;
        }

        // Access total_collected directly from the already loaded model
        if ($deceased->total_collected_amount <= 0) {

            Notification::make()
                ->title("Cannot release money. No funds available.")
                ->warning()
                ->send();
            return;
        }

                Contribution::where('deceased_id', $this->selectedDeceasedId)
                    ->where('status', '1')
                    ->update([
                'release_status' => '1',
                'released_by' => Auth::id(),
                'released_at' => now(),
            ]);

        $deceasedName = $deceased->member->full_name ?? 'Unknown';

        Notification::make()
            ->title("Released {$deceasedName}")
            ->success()
            ->send();

        User::logAudit('Released Money', "Marked contributions as released for deceased: {$deceasedName}");

        $this->reset(['selectedDeceasedId', 'receiptFile']);
        $this->dispatch('close-modal', id: 'confirm-release-modal');
        $this->loadGroups(); // Re-load groups to reflect the release status
    }

    public function uploadReceipt(): void
    {
        $this->validate([
            'receiptFile' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if (!$this->selectedDeceasedId) return;

        $filePath = $this->receiptFile->store('release_receipts', 'public');

        Contribution::where('deceased_id', $this->selectedDeceasedId)
            ->where('status', '1')
            ->update(['release_receipt_path' => $filePath]);

        User::logAudit('Uploaded Receipt', "Uploaded receipt for deceased ID {$this->selectedDeceasedId}");

        Notification::make()
            ->title('Receipt Uploaded')
            ->success()
            ->send();

        $this->reset(['receiptFile', 'selectedDeceasedId']);
        $this->dispatch('close-modal', id: 'confirm-release-modal');
        $this->loadGroups();
    }
}
