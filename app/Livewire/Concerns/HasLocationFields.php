<?php

namespace App\Livewire\Concerns;

use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;
use Nnjeim\World\Models\State;

trait HasLocationFields
{
    public int $countryId = 0;
    public int $stateId = 0;
    public int $cityId = 0;
    public array $availableCountries = [];
    public array $availableStates = [];
    public array $availableCities = [];

    public function initializeLocation(): void
    {
        $this->availableCountries = Country::orderBy('name')->pluck('name', 'id')->toArray();

        if (!empty($this->country)) {
            $countryModel = Country::where('name', $this->country)->first();
            if ($countryModel) {
                $this->countryId = $countryModel->id;
                $this->availableStates = State::where('country_id', $this->countryId)
                    ->orderBy('name')->pluck('name', 'id')->toArray();
            }
        }

        if (!empty($this->state) && $this->countryId) {
            $stateModel = State::where('name', $this->state)
                ->where('country_id', $this->countryId)->first();
            if ($stateModel) {
                $this->stateId = $stateModel->id;
                $this->availableCities = City::where('state_id', $this->stateId)
                    ->orderBy('name')->pluck('name', 'id')->toArray();
            }
        }

        if (!empty($this->city) && $this->stateId) {
            $cityModel = City::where('name', $this->city)
                ->where('state_id', $this->stateId)->first();
            if ($cityModel) {
                $this->cityId = $cityModel->id;
            }
        }
    }

    public function updatedCountryId(int $value): void
    {
        $this->stateId = 0;
        $this->cityId = 0;
        $this->availableStates = [];
        $this->availableCities = [];
        $this->country = Country::find($value)?->name ?? '';
        $this->state = '';
        $this->city = '';

        if ($value) {
            $this->availableStates = State::where('country_id', $value)
                ->orderBy('name')->pluck('name', 'id')->toArray();
        }
    }

    public function updatedStateId(int $value): void
    {
        $this->cityId = 0;
        $this->availableCities = [];
        $this->state = State::find($value)?->name ?? '';
        $this->city = '';

        if ($value) {
            $this->availableCities = City::where('state_id', $value)
                ->orderBy('name')->pluck('name', 'id')->toArray();
        }
    }

    public function updatedCityId(int $value): void
    {
        $this->city = City::find($value)?->name ?? '';
    }
}
