<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class PokemonDetail extends Component
{
    public string $name;

    public function mount(string $name): void
    {
        $this->name = $name;
    }

    public function render(): View
    {
        return view('livewire.pokemon-detail')
            ->title('Pokemon Detail');
    }
}
