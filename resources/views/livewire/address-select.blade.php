{{-- <div class="space-y-4">
    <div>
        <label>Country</label>
        <select wire:model="selectedCountry" class="w-full rounded border-gray-300">
            <option value="">-- Select Country --</option>
            @foreach($countries as $id => $country)
                <option value="{{ $id }}">{{ $country }}</option>
            @endforeach
        </select>
    </div>

    @if (!empty($states))
        <div>
            <label>Province / State</label>
            <select wire:model="selectedState" class="w-full rounded border-gray-300">
                <option value="">-- Select State --</option>
                @foreach($states as $state)
                    <option value="{{ $state['id'] }}">{{ $state['name'] }}</option>
                @endforeach
            </select>
        </div>
    @endif

    @if (!empty($cities))
        <div>
            <label>City</label>
            <select wire:model="selectedCity" class="w-full rounded border-gray-300">
                <option value="">-- Select City --</option>
                @foreach($cities as $city)
                    <option value="{{ $city['id'] }}">{{ $city['name'] }}</option>
                @endforeach
            </select>
        </div>
    @endif
</div> --}}
