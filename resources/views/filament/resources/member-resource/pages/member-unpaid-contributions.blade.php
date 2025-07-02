<x-filament::page>
    <h2 class="mb-4 text-xl font-bold">Unpaid Contributions for {{ $record->full_name }}</h2>

    <table class="w-full text-sm border">
        <thead>
            <tr class="text-left bg-gray-100">
                <th class="p-2 border">Deceased</th>
                <th class="p-2 border">Amount</th>
                <th class="p-2 border">Status</th>
                <th class="p-2 text-center border">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($record->unpaidContributions as $c)
                <tr class="text-center border-t">
                    <td class="p-2">{{ $c->deceased->full_name ?? 'N/A' }}</td>
                    <td class="p-2">₱{{ number_format($c->amount, 2) }}</td>
                    <td class="p-2">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            {{ $c->status == 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                            {{ $c->status == 0 ? 'Unpaid' : 'Paid' }}
                        </span>
                    </td>
                    <td class="p-2">
                        <form wire:submit.prevent="pay({{ $c->consid }})">
                            <x-filament::button type="submit" color="success" size="sm">
                                Pay
                            </x-filament::button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-500">
                        No unpaid contributions.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @push('scripts')
        <script>
            window.addEventListener('receiptReady', event => {
                const { payer, batch } = event.detail;
                const url = `/admin/contributions/receipt?payer=${payer}&batch=${batch}`;
                window.open(url, '_blank');
            });
        </script>
    @endpush
</x-filament::page>
