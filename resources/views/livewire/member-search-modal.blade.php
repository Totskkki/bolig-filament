<div class="p-4">
    <input
        type="text"
        wire:model.debounce.500ms="search"
        class="w-full border p-2 mb-4"
        placeholder="Search member by name..."
        autofocus
    >

    <ul>
        @foreach($members as $member)
            @php
                $name = $member->user->name;
                $fullName = trim(implode(' ', array_filter([
                    $name->first_name,
                    $name->middle_name,
                    $name->last_name,
                    $name->suffix,
                ])));
            @endphp
            <li wire:click="selectMember({{ $member->memberID }})"
                class="cursor-pointer hover:bg-gray-200 p-2"
            >
                {{ $fullName }}
            </li>
        @endforeach
    </ul>
</div>
