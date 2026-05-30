<?php

namespace App\Livewire;

use App\Services\PokeApiService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PokemonDetail extends Component
{
    public string $name;

    public ?array $pokemon = null;

    public ?string $error = null;

    public function mount(string $name, PokeApiService $pokeApi): void
    {
        $this->name = $name;

        try {
            $this->pokemon = $pokeApi->getPokemon($name);
        } catch (\Throwable) {
            $this->pokemon = null;
            $this->error = 'Detail Pokemon belum bisa dimuat. Coba beberapa saat lagi.';

            return;
        }

        if ($this->pokemon === null) {
            $this->error = 'Pokemon tidak ditemukan.';
        }
    }

    public function render(): View
    {
        return view('livewire.pokemon-detail')
            ->title($this->pokemon ? "{$this->pokemon['display_name']} - Pokemon Explorer" : 'Pokemon tidak ditemukan');
    }
}
