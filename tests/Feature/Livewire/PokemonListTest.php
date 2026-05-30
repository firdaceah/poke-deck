<?php

namespace Tests\Feature\Livewire;

use App\Livewire\PokemonList;
use App\Services\PokeApiService;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class PokemonListTest extends TestCase
{
    public function test_it_renders_pokemon_cards(): void
    {
        $service = Mockery::mock(PokeApiService::class);
        $service->shouldReceive('listPokemon')->once()->with(20, 0)->andReturn([
            'count' => 1,
            'items' => [$this->pokemon('bulbasaur')],
        ]);

        $this->app->instance(PokeApiService::class, $service);

        Livewire::test(PokemonList::class)
            ->assertSee('Bulbasaur')
            ->assertSee('Grass');
    }

    public function test_it_searches_pokemon_by_name(): void
    {
        $service = Mockery::mock(PokeApiService::class);
        $service->shouldReceive('listPokemon')->once()->with(20, 0)->andReturn([
            'count' => 0,
            'items' => [],
        ]);
        $service->shouldReceive('searchPokemon')->once()->with('pika')->andReturn([
            $this->pokemon('pikachu', ['electric']),
        ]);

        $this->app->instance(PokeApiService::class, $service);

        Livewire::test(PokemonList::class)
            ->set('search', 'pika')
            ->assertSee('Pikachu')
            ->assertSee('Electric');
    }

    public function test_it_loads_more_pokemon(): void
    {
        $service = Mockery::mock(PokeApiService::class);
        $service->shouldReceive('listPokemon')->once()->with(20, 0)->andReturn([
            'count' => 40,
            'items' => [$this->pokemon('bulbasaur')],
        ]);
        $service->shouldReceive('listPokemon')->once()->with(20, 20)->andReturn([
            'count' => 40,
            'items' => [$this->pokemon('pikachu', ['electric'])],
        ]);

        $this->app->instance(PokeApiService::class, $service);

        Livewire::test(PokemonList::class)
            ->call('loadMore')
            ->assertSee('Bulbasaur')
            ->assertSee('Pikachu');
    }

    private function pokemon(string $name, array $types = ['grass']): array
    {
        return [
            'id' => 1,
            'name' => $name,
            'display_name' => str($name)->replace('-', ' ')->title()->toString(),
            'image' => 'https://img.test/pokemon.png',
            'types' => $types,
        ];
    }
}
