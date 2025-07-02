<x-filament::page>
    <form wire:submit.prevent="mountForm" class="space-y-4">
        {{ $this->form }}
        <x-filament::button type="submit">Filter</x-filament::button>
    </form>

    <div class="mt-6">
        <h2 class="text-lg font-bold">Contributions from {{ $fromDate ?? 'N/A' }} to {{ $toDate ?? 'N/A' }}</h2>

        {{-- Chart Section --}}
        <div class="my-4">
            <canvas id="contributionChart" style="height: 300px;"></canvas>
        </div>

        {{-- Table Section --}}
        <table class="w-full mt-4 text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Payer</th>
                    <th class="p-2 text-left">Amount</th>
                    <th class="p-2 text-left">Date</th>
                    <th class="p-2 text-left">Batch ID</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->getContributions() as $contribution)
                    <tr>
                        <td class="p-2">
                            {{ $contribution->payer->name->full_name ?? 'N/A' }}
                        </td>
                        <td class="p-2">₱{{ number_format($contribution->amount, 2) }}</td>
                        <td class="p-2">{{ \Carbon\Carbon::parse($contribution->payment_date)->format('Y-m-d') }}</td>
                        <td class="p-2">{{ $contribution->payment_batch }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Total --}}
        <div class="mt-4 text-lg font-bold text-right">
            Total: ₱{{ number_format($this->getTotalAmount(), 2) }}
        </div>

        {{-- PDF Export --}}
        <div class="mt-4 text-right">
            <a href="{{ route('contribution.report.pdf', ['from' => $fromDate, 'to' => $toDate]) }}"
               class="inline-flex items-center px-4 py-2 text-white rounded bg-primary-600 hover:bg-primary-700">
                🧾 Export PDF
            </a>
        </div>
    </div>

    {{-- Chart.js Script --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const labels = {!! json_encode(array_keys($this->getDailyTotals())) !!};
        const data = {!! json_encode(array_values($this->getDailyTotals())) !!};

        new Chart(document.getElementById('contributionChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Total',
                    data: data,
                    backgroundColor: '#3b82f6',
                }]
            },
        });
    </script>
</x-filament::page>
