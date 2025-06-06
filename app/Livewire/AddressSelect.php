<?php
namespace App\Livewire;

use Livewire\Component;

class AddressSelect extends Component
{
    public $countries = [];
    public $states = [];
    public $cities = [];

    public $selectedCountry = '';
    public $selectedState = '';
    public $selectedCity = '';

    public function mount()
    {
        $this->countries = json_decode(file_get_contents(storage_path('app/locations/countries.json')), true);
    }

    public function updatedSelectedCountry($value)
    {
        $states = json_decode(file_get_contents(storage_path('app/locations/states.json')), true);
        $this->states = array_filter($states, fn($state) => $state['country_id'] == $value);
        $this->selectedState = '';
        $this->cities = [];
        $this->selectedCity = '';
    }

    public function updatedSelectedState($value)
    {
        $cities = json_decode(file_get_contents(storage_path('app/locations/cities.json')), true);
        $this->cities = array_filter($cities, fn($city) => $city['state_id'] == $value);
        $this->selectedCity = '';
    }

    public function render()
    {
        return view('livewire.address-select');
    }
}
