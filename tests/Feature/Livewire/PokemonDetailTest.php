<?php

namespace Tests\Feature\Livewire;

use App\Livewire\PokemonDetail;
use App\Services\PokeApiService;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class PokemonDetailTest extends TestCase
{
    public function test_it_renders_pokemon_detail(): void
    {
        $service = Mockery::mock(PokeApiService::class);
        $service->shouldReceive('getPokemon')->once()->with('pikachu')->andReturn($this->pokemon());

        $this->app->instance(PokeApiService::class, $service);

        Livewire::test(PokemonDetail::class, ['name' => 'pikachu'])
            ->assertSee('Pikachu')
            ->assertSee('Electric')
            ->assertSee('Static')
            ->assertSee('Thunder Shock');
    }

    public function test_it_shows_error_when_pokemon_is_missing(): void
    {
        $service = Mockery::mock(PokeApiService::class);
        $service->shouldReceive('getPokemon')->once()->with('missingno')->andReturn(null);

        $this->app->instance(PokeApiService::class, $service);

        Livewire::test(PokemonDetail::class, ['name' => 'missingno'])
            ->assertSee('Pokemon tidak ditemukan');
    }

    private function pokemon(): array
    {
        return [
            'id' => 25,
            'name' => 'pikachu',
            'display_name' => 'Pikachu',
            'image' => 'https://img.test/pikachu.png',
            'types' => ['electric'],
            'abilities' => [
                ['name' => 'static', 'display_name' => 'Static', 'is_hidden' => false],
            ],
            'stats' => [
                ['name' => 'hp', 'display_name' => 'Hp', 'value' => 35],
                ['name' => 'speed', 'display_name' => 'Speed', 'value' => 90],
            ],
            'height' => 4,
            'weight' => 60,
            'base_experience' => 112,
            'moves' => ['Thunder Shock', 'Quick Attack'],
        ];
    }
}
