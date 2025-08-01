<x-filament::page>
    <x-slot name="header">
        Coordinator Contributions
    </x-slot>

    <div class="w-full max-w-md mb-4">
        {{ $this->form }}
    </div>

    @if ($coordinator)
        <div class="mb-4 text-lg font-semibold">
            Total Collected from Coordinator:
            ₱{{ number_format(\App\Models\Contribution::where('coordinator_id', $coordinator)->sum('amount'), 2) }}
        </div>

        {{ $this->table }}
    @else
        <div class="text-sm italic text-gray-600 mb-6">
            Please select a coordinator to display contributions.
        </div>
    @endif

    <h2 class="text-lg font-semibold mb-2 mt-6">
        Coordinator Released Shares Summary
    </h2>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[600px] divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">Deceased</th>
                    {{-- <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">Total Collected</th> --}}
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">Coordinator Share</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">Release Date</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-900">
                @forelse ($releasedEarnings as $earning)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-100">{{ $earning['deceased_name'] }}</td>
                        {{-- <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-100">₱{{ number_format($earning['total_released'], 2) }}</td> --}}
                        <td class="px-4 py-2 text-sm text-green-600 dark:text-green-400">₱{{ number_format($earning['total_share'], 2) }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">{{ $earning['release_date'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
                            No released earnings yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament::page>
