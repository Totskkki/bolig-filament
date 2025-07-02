<div class="p-4">
    <input
        type="text"
        wire:model.debounce.500ms="search"
        class="w-full p-2 mb-4 border"
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
                class="p-2 cursor-pointer hover:bg-gray-200"
            >
                {{ $fullName }}
            </li>
        @endforeach
    </ul>
</div>
