<?php

namespace App\Livewire;

use App\Services\PokeApiService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class PokemonList extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    public array $pokemon = [];

    public int $limit = 20;

    public int $offset = 0;

    public int $total = 0;

    public ?string $error = null;

    public function mount(PokeApiService $pokeApi): void
    {
        $this->loadInitialPokemon($pokeApi);
    }

    public function updatedSearch(PokeApiService $pokeApi): void
    {
        $this->offset = 0;
        $this->error = null;

        if (trim($this->search) === '') {
            $this->loadInitialPokemon($pokeApi);

            return;
        }

        try {
            $this->pokemon = $pokeApi->searchPokemon($this->search);
            $this->total = count($this->pokemon);
        } catch (\Throwable) {
            $this->pokemon = [];
            $this->error = 'Data Pokemon belum bisa dimuat. Coba beberapa saat lagi.';
        }
    }

    public function loadMore(PokeApiService $pokeApi): void
    {
        if ($this->search !== '') {
            return;
        }

        $this->offset += $this->limit;

        try {
            $result = $pokeApi->listPokemon($this->limit, $this->offset);
            $this->pokemon = array_merge($this->pokemon, $result['items']);
            $this->total = $result['count'];
        } catch (\Throwable) {
            $this->error = 'Data Pokemon berikutnya belum bisa dimuat.';
        }
    }

    public function render(): View
    {
        return view('livewire.pokemon-list')
            ->title('Pokemon Explorer');
    }

    private function loadInitialPokemon(PokeApiService $pokeApi): void
    {
        try {
            $result = $pokeApi->listPokemon($this->limit, 0);
            $this->pokemon = $result['items'];
            $this->total = $result['count'];
        } catch (\Throwable) {
            $this->pokemon = [];
            $this->total = 0;
            $this->error = 'Data Pokemon belum bisa dimuat. Coba beberapa saat lagi.';
        }
    }
}
