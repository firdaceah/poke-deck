<?php

namespace Tests\Feature;

use App\Services\PokeApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PokeApiServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config(['services.pokeapi.base_url' => 'https://pokeapi.test/api/v2']);
    }

    public function test_it_gets_paginated_pokemon_with_normalized_details(): void
    {
        Http::fake([
            'pokeapi.test/api/v2/pokemon?limit=2&offset=0' => Http::response([
                'count' => 2,
                'results' => [
                    ['name' => 'bulbasaur', 'url' => 'https://pokeapi.test/api/v2/pokemon/1/'],
                    ['name' => 'ivysaur', 'url' => 'https://pokeapi.test/api/v2/pokemon/2/'],
                ],
            ]),
            'pokeapi.test/api/v2/pokemon/bulbasaur' => Http::response($this->pokemonPayload('bulbasaur', 1, ['grass', 'poison'])),
            'pokeapi.test/api/v2/pokemon/ivysaur' => Http::response($this->pokemonPayload('ivysaur', 2, ['grass', 'poison'])),
        ]);

        $result = app(PokeApiService::class)->listPokemon(limit: 2, offset: 0);

        $this->assertSame(2, $result['count']);
        $this->assertSame('bulbasaur', $result['items'][0]['name']);
        $this->assertSame(1, $result['items'][0]['id']);
        $this->assertSame(['grass', 'poison'], $result['items'][0]['types']);
    }

    public function test_it_gets_one_pokemon_detail(): void
    {
        Http::fake([
            'pokeapi.test/api/v2/pokemon/pikachu' => Http::response($this->pokemonPayload('pikachu', 25, ['electric'])),
        ]);

        $pokemon = app(PokeApiService::class)->getPokemon('pikachu');

        $this->assertSame('pikachu', $pokemon['name']);
        $this->assertSame(25, $pokemon['id']);
        $this->assertSame('electric', $pokemon['types'][0]);
        $this->assertNotEmpty($pokemon['stats']);
        $this->assertNotEmpty($pokemon['abilities']);
    }

    public function test_it_returns_null_when_detail_request_fails(): void
    {
        Http::fake([
            'pokeapi.test/api/v2/pokemon/missingno' => Http::response([], 404),
        ]);

        $pokemon = app(PokeApiService::class)->getPokemon('missingno');

        $this->assertNull($pokemon);
    }

    private function pokemonPayload(string $name, int $id, array $types): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'height' => 7,
            'weight' => 69,
            'base_experience' => 64,
            'sprites' => [
                'front_default' => "https://img.test/{$name}.png",
                'other' => [
                    'official-artwork' => [
                        'front_default' => "https://img.test/{$name}-official.png",
                    ],
                ],
            ],
            'types' => collect($types)->map(fn (string $type, int $index) => [
                'slot' => $index + 1,
                'type' => ['name' => $type],
            ])->all(),
            'abilities' => [
                ['ability' => ['name' => 'overgrow'], 'is_hidden' => false],
            ],
            'stats' => [
                ['base_stat' => 45, 'stat' => ['name' => 'hp']],
                ['base_stat' => 49, 'stat' => ['name' => 'attack']],
                ['base_stat' => 49, 'stat' => ['name' => 'defense']],
                ['base_stat' => 65, 'stat' => ['name' => 'special-attack']],
                ['base_stat' => 65, 'stat' => ['name' => 'special-defense']],
                ['base_stat' => 45, 'stat' => ['name' => 'speed']],
            ],
            'moves' => [
                ['move' => ['name' => 'tackle']],
                ['move' => ['name' => 'vine-whip']],
            ],
        ];
    }
}
