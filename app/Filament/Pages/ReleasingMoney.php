<?php

namespace App\Filament\Pages;

use App\Models\Contribution;
use App\Models\Deceased;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\WithFileUploads;

class ReleasingMoney extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static string $view = 'filament.pages.releasing-money';
    protected static ?string $navigationLabel = 'Releasing';
    protected static ?string $navigationGroup = 'Payables';
    protected static ?int $navigationSort = 5;

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
        ])
            ->withCount([
                'contributions as total_collected' => fn($query) => $query->where('status', '1')
            ])
            ->get();
    }

    public function openConfirmModal(int $deceasedId): void
    {
        $this->selectedDeceasedId = $deceasedId;

        $this->dispatch('open-modal', id: 'confirm-release-modal');
    }

    public function confirmRelease(): void
    {
        if (!$this->selectedDeceasedId) return;

        $deceased = Deceased::with('member')->find($this->selectedDeceasedId);

        Contribution::where('deceased_id', $this->selectedDeceasedId)
            ->where('status', '1')
            ->update(['release_status' => '1']);

        $deceasedName = $deceased->member->full_name ?? 'Unknown';

        Notification::make()
            ->title("Released {$deceasedName}")
            ->success()
            ->send();

        User::logAudit('Released Money', "Marked contributions as released for deceased: {$deceasedName}");

        $this->reset(['selectedDeceasedId', 'receiptFile']);
        $this->dispatch('close-modal', id: 'confirm-release-modal');
        $this->loadGroups();
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
