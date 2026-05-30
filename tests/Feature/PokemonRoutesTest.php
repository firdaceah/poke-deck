<?php

namespace Tests\Feature;

use App\Services\PokeApiService;
use Mockery;
use Tests\TestCase;

class PokemonRoutesTest extends TestCase
{
    public function test_homepage_is_public(): void
    {
        $service = Mockery::mock(PokeApiService::class);
        $service->shouldReceive('listPokemon')->once()->with(20, 0)->andReturn([
            'count' => 0,
            'items' => [],
        ]);

        $this->app->instance(PokeApiService::class, $service);

        $this->get(route('pokemon.index'))->assertOk();
    }

    public function test_detail_page_is_public(): void
    {
        $service = Mockery::mock(PokeApiService::class);
        $service->shouldReceive('getPokemon')->once()->with('pikachu')->andReturn([
            'id' => 25,
            'name' => 'pikachu',
            'display_name' => 'Pikachu',
            'image' => null,
            'types' => ['electric'],
            'abilities' => [],
            'stats' => [],
            'height' => 4,
            'weight' => 60,
            'base_experience' => 112,
            'moves' => [],
        ]);

        $this->app->instance(PokeApiService::class, $service);

        $this->get(route('pokemon.show', 'pikachu'))->assertOk();
    }

    public function test_load_more_route_returns_rendered_cards(): void
    {
        $service = Mockery::mock(PokeApiService::class);
        $service->shouldReceive('listPokemon')->once()->with(20, 20)->andReturn([
            'count' => 60,
            'items' => [$this->pokemon('pikachu', ['electric'])],
        ]);

        $this->app->instance(PokeApiService::class, $service);

        $this->getJson(route('pokemon.load-more', ['offset' => 20]))
            ->assertOk()
            ->assertJsonPath('nextOffset', 40)
            ->assertJsonPath('hasMore', true)
            ->assertSee('Pikachu')
            ->assertSee('Electric');
    }

    public function test_search_route_returns_rendered_cards(): void
    {
        $service = Mockery::mock(PokeApiService::class);
        $service->shouldReceive('searchPokemon')->once()->with('pika')->andReturn([
            $this->pokemon('pikachu', ['electric']),
        ]);

        $this->app->instance(PokeApiService::class, $service);

        $this->getJson(route('pokemon.search', ['q' => 'pika']))
            ->assertOk()
            ->assertJsonPath('hasResults', true)
            ->assertJsonPath('hasMore', false)
            ->assertSee('Pikachu')
            ->assertSee('Electric');
    }

    private function pokemon(string $name, array $types = ['grass']): array
    {
        return [
            'id' => 25,
            'name' => $name,
            'display_name' => str($name)->replace('-', ' ')->title()->toString(),
            'image' => null,
            'types' => $types,
        ];
    }
}
