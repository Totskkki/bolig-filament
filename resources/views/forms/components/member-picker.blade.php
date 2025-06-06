<div>
    <div class="flex items-center gap-2">
        <input
            type="text"
            id="{{ $getId() }}"
            name="{{ $getName() }}"
            readonly
            value="{{ $getState() }}"
            class="w-full border-gray-300 rounded-lg shadow-sm"
            placeholder="Click Search to select member"
        />

        <button
            type="button"
            wire:click="$dispatch('open-member-modal')"
            class="px-3 py-2 bg-blue-600 text-white rounded-lg"
        >
            🔍 Search
        </button>
    </div>
</div>
