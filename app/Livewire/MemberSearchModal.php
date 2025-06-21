<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Member;

class MemberSearchModal extends Component
{
    public $search = '';
    public $members = [];

    public function updatedSearch()
    {
        $search = $this->search;

        $this->members = Member::query()
            ->with('user') // eager load user relation
            ->whereHas('user', function ($query) use ($search) {
                $query->whereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            })
            ->limit(10)
            ->get();
    }

    public function selectMember($memberId)
    {
        $this->dispatchBrowserEvent('member-selected', ['memberId' => $memberId]);
        $this->dispatchBrowserEvent('close-modal');
    }

    public function render()
    {
        return view('livewire.member-search-modal');
    }
}
