<div class="hidden mr-4 md:block">
    <form method="GET" action="{{ route('filament.admin.pages.coordinator-contributions') }}">
        <select name="coordinator" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-white">
            <option value="">Select Coordinator</option>
            @foreach(\App\Models\Member::where('role', 'coordinator')->get() as $coordinator)
                <option value="{{ $coordinator->memberID }}" {{ request('coordinator') == $coordinator->memberID ? 'selected' : '' }}>
                    {{ $coordinator->full_name }}
                </option>
            @endforeach
        </select>
    </form>
</div>
