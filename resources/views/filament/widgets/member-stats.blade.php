<x-filament::widget>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div class="p-4 text-white shadow-md rounded-xl bg-gradient-to-r from-blue-500 to-cyan-500">
            <div class="text-lg font-bold">Total Members</div>
            <div class="text-3xl">{{ $totalMembers }}</div>
            <div class="text-sm">All registered members</div>
        </div>

        <div class="p-4 text-white shadow-md rounded-xl bg-gradient-to-r from-green-500 to-emerald-500">
            <div class="text-lg font-bold">Active Members</div>
            <div class="text-3xl">{{ $activeMembers }}</div>
            <div class="text-sm">Currently active</div>
        </div>

        <div class="p-4 text-white shadow-md rounded-xl bg-gradient-to-r from-yellow-400 to-orange-500">
            <div class="text-lg font-bold">Inactive Members</div>
            <div class="text-3xl">{{ $inactiveMembers }}</div>
            <div class="text-sm">No longer active</div>
        </div>

        <div class="p-4 text-white shadow-md rounded-xl bg-gradient-to-r from-red-500 to-pink-500">
            <div class="text-lg font-bold">Deceased Members</div>
            <div class="text-3xl">{{ $deceasedMembers }}</div>
            <div class="text-sm">Marked as deceased</div>
        </div>

        <div class="p-4 text-white shadow-md rounded-xl bg-gradient-to-r from-purple-500 to-indigo-500">
            <div class="text-lg font-bold">Paid Contributions</div>
            <div class="text-3xl">₱{{ number_format($paidContributions, 2) }}</div>
            <div class="text-sm">Total paid</div>
        </div>

        <div class="p-4 text-white shadow-md rounded-xl bg-gradient-to-r from-amber-500 to-yellow-600">
            <div class="text-lg font-bold">Unpaid Contributions</div>
            <div class="text-3xl">₱{{ number_format($unpaidContributions, 2) }}</div>
            <div class="text-sm">Still to collect</div>
        </div>
    </div>
</x-filament::widget>
