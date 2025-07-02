<x-filament::page>
    <h2 class="text-2xl font-bold mb-6 text-primary dark:text-primary-300">💸 Money Releasing</h2>

    {{-- TABS --}}
    <div x-data="{ tab: 'pending' }" class="mb-6">
        <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700">
            <button
                x-on:click="tab = 'pending'"
                :class="{ 'border-b-2 border-primary text-primary dark:text-primary-300': tab === 'pending' }"
                class="px-4 py-2 text-sm font-semibold hover:text-primary dark:hover:text-primary-300"
            >
                🕒 Pending Releases
            </button>
            <button
                x-on:click="tab = 'released'"
                :class="{ 'border-b-2 border-success text-success dark:text-success-300': tab === 'released' }"
                class="px-4 py-2 text-sm font-semibold hover:text-success dark:hover:text-success-300"
            >
                ✅ Released Money
            </button>
        </div>

        {{-- PENDING TAB --}}
        <div x-show="tab === 'pending'" class="mt-4 space-y-4">
            @forelse ($groups->where('contributions.0.release_status', '!=', '1') as $group)
                <x-filament::card class="p-4 border-l-4 border-yellow-500 dark:border-yellow-400">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $group->member->full_name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Deceased: {{ $group->full_name }} <br>
                                Collected: <span class="font-semibold text-gray-700 dark:text-gray-200">₱{{ number_format($group->contributions->sum('amount'), 2) }}</span>
                            </div>
                            <div class="mt-1">
                                <x-filament::badge color="warning">Pending</x-filament::badge>
                            </div>
                        </div>
                        <x-filament::button color="success" wire:click="openConfirmModal({{ $group->deceasedID }})">
                            Release
                        </x-filament::button>
                    </div>
                </x-filament::card>
            @empty
                <div class="text-center text-gray-500 dark:text-gray-400 mt-10">🎉 No pending contributions!</div>
            @endforelse
        </div>

        {{-- RELEASED TAB --}}
        <div x-show="tab === 'released'" class="mt-4 space-y-4">
            @forelse ($groups->where('contributions.0.release_status', '==', '1') as $group)
                <x-filament::card class="p-4 border-l-4 border-green-500 dark:border-green-400">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $group->member->full_name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Deceased: {{ $group->full_name }} <br>
                                Collected: <span class="font-semibold text-gray-700 dark:text-gray-200">₱{{ number_format($group->contributions->sum('amount'), 2) }}</span>
                            </div>
                            <div class="mt-1">
                                <x-filament::badge color="success">Released</x-filament::badge>
                            </div>
                        </div>
                        @if ($group->contributions->first()?->release_receipt_path)
                            <a href="{{ Storage::url($group->contributions->first()->release_receipt_path) }}"
                                target="_blank"
                                class="text-sm text-primary dark:text-blue-400 underline hover:text-blue-800 dark:hover:text-blue-300 mt-2">
                                📄 View Receipt
                            </a>
                        @endif
                    </div>
                </x-filament::card>
            @empty
                <div class="text-center text-gray-500 dark:text-gray-400 mt-10">📭 No released records yet.</div>
            @endforelse
        </div>
    </div>

    {{-- Modal --}}
    <x-filament::modal id="confirm-release-modal" width="md">
        <x-slot name="header">
            Confirm Money Release
        </x-slot>

        <x-slot name="content">
            <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                Are you sure you want to release the contributions for this deceased member?
                This action cannot be undone.
            </p>

            @if ($selectedDeceasedId)
                <form wire:submit.prevent="uploadReceipt" class="space-y-2 mt-3" enctype="multipart/form-data">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload Receipt</label>
                        <input
                            type="file"
                            wire:model="receiptFile"
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 focus:outline-none"
                        />
                    </div>
                    @error('receiptFile')
                        <span class="text-danger dark:text-red-400 text-sm">{{ $message }}</span>
                    @enderror
                </form>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-filament::button color="gray" wire:click="$dispatch('close-modal', { id: 'confirm-release-modal' })">
                Cancel
            </x-filament::button>

            <x-filament::button color="success" wire:click="confirmRelease">
                Confirm Release
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament::page>
