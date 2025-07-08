<x-filament::page>
    <div class="w-full max-w-md mb-4">
        {{ $this->form }}
    </div>

    @if ($coordinator)
        <div class="mb-4 text-lg font-semibold">
            Total Collected from Coordinator:
            ₱{{ number_format(
                \App\Models\Contribution::where('coordinator_id', $coordinator)->sum('amount'),
            2) }}
        </div>

        {{ $this->table }}
    @else
        <div class="text-sm italic text-gray-600">
            Please select a coordinator to display contributions.
        </div>
    @endif
</x-filament::page>

<script>
    window.addEventListener('open-receipt-tab', event => {
        window.open(event.detail.url, '_blank');
    });
</script>

