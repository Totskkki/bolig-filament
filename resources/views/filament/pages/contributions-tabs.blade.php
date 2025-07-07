<div class="flex space-x-4 px-4 py-2">
    <button
        wire:click="$set('statusTab', 'unpaid')"
        @class([
            'text-sm px-4 py-1 rounded-lg font-medium',
            'bg-primary-600 text-white' => $statusTab === 'unpaid',
            'bg-gray-100 text-gray-700 hover:bg-gray-200' => $statusTab !== 'unpaid',
        ])
    >
        Unpaid Contributions
    </button>

    <button
        wire:click="$set('statusTab', 'paid')"
        @class([
            'text-sm px-4 py-1 rounded-lg font-medium',
            'bg-primary-600 text-white' => $statusTab === 'paid',
            'bg-gray-100 text-gray-700 hover:bg-gray-200' => $statusTab !== 'paid',
        ])
    >
        Paid Contributions
    </button>
</div>
