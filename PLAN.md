# Pokemon Explorer Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build an MVP Laravel + Livewire Pokemon Explorer that lists Pokemon from PokeAPI and shows detail pages without auth or database persistence.

**Architecture:** The app uses Livewire components for interactive UI state, a dedicated `PokeApiService` for all PokeAPI communication, and Laravel Cache to reduce repeated API calls. Blade views stay presentation-focused, while reusable UI pieces such as type badges and stat bars are split into small components.

**Tech Stack:** Laravel, Livewire, Blade, Tailwind CSS, Laravel HTTP Client, Laravel Cache, PHPUnit/Pest depending on the Laravel installer default.

---

## Progress Legend

- `[ ]` Not started
- `[x]` Done
- Keep this file updated after each phase and commit.

## Phase Overview

- [ ] Phase 0: Scaffold Laravel project
- [ ] Phase 1: Install and configure Livewire
- [ ] Phase 2: Add PokeAPI configuration and service layer
- [ ] Phase 3: Build Pokemon list feature
- [ ] Phase 4: Build Pokemon detail feature
- [ ] Phase 5: Add polished responsive UI
- [ ] Phase 6: Add tests
- [ ] Phase 7: Final verification and cleanup

---

## File Structure Map

Expected files after implementation:

- `config/services.php`  
  Stores `pokeapi.base_url` config.

- `.env.example`  
  Documents `POKEAPI_BASE_URL`.

- `app/Services/PokeApiService.php`  
  Single integration boundary for list, detail, normalization, cache, fallback images, and error-safe API calls.

- `app/Livewire/PokemonList.php`  
  Livewire state for listing, searching, pagination/load more, loading, and error state.

- `app/Livewire/PokemonDetail.php`  
  Livewire state for one Pokemon detail page.

- `resources/views/livewire/pokemon-list.blade.php`  
  Main list UI.

- `resources/views/livewire/pokemon-detail.blade.php`  
  Detail page UI.

- `resources/views/components/pokemon/type-badge.blade.php`  
  Reusable type badge.

- `resources/views/components/pokemon/stat-bar.blade.php`  
  Reusable stat display.

- `resources/views/components/pokemon/error-state.blade.php`  
  Reusable friendly error state.

- `resources/views/components/pokemon/empty-state.blade.php`  
  Reusable empty state.

- `resources/views/components/pokemon/skeleton-card.blade.php`  
  Reusable loading skeleton for cards.

- `routes/web.php`  
  Public routes for `/` and `/pokemon/{name}`.

- `tests/Feature/PokeApiServiceTest.php`  
  Service tests with HTTP fake.

- `tests/Feature/Livewire/PokemonListTest.php`  
  Livewire list behavior tests.

- `tests/Feature/Livewire/PokemonDetailTest.php`  
  Livewire detail behavior tests.

---

## Phase 0: Scaffold Laravel Project

**Purpose:** Create a clean Laravel app in the current project folder without auth scaffolding or database features.

**Files:**

- Create: Laravel default project files
- Keep: `PRD.md`
- Keep: `PLAN.md`

- [ ] **Step 0.1: Confirm current folder only contains docs or intended starter files**

Run:

```bash
dir
```

Expected:

```text
PRD.md
PLAN.md
```

- [ ] **Step 0.2: Create Laravel project in the current folder**

Run one of these commands.

Preferred when Laravel installer is available:

```bash
laravel new . --no-interaction
```

Fallback when only Composer is available:

```bash
composer create-project laravel/laravel .
```

Expected:

```text
Application ready
```

- [ ] **Step 0.3: Keep the MVP database-free**

Open `.env` and set cache/session to file-backed drivers:

```dotenv
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Do not run database migrations for this MVP.

- [ ] **Step 0.4: Verify Laravel boots**

Run:

```bash
php artisan about
```

Expected:

```text
Laravel
PHP
Environment
```

- [ ] **Step 0.5: Commit scaffold**

Run:

```bash
git init
git add .
git commit -m "chore: scaffold laravel pokemon explorer"
```

Expected:

```text
[main ...] chore: scaffold laravel pokemon explorer
```

---

## Phase 1: Install and Configure Livewire

**Purpose:** Add Livewire as the interactive UI layer.

**Files:**

- Modify: `composer.json`
- Modify: `resources/views/welcome.blade.php` or replace usage through routes
- Create: Livewire-generated directories if not present

- [ ] **Step 1.1: Install Livewire**

Run:

```bash
composer require livewire/livewire
```

Expected:

```text
Package manifest generated successfully
```

- [ ] **Step 1.2: Create Livewire components**

Run:

```bash
php artisan make:livewire PokemonList
php artisan make:livewire PokemonDetail
```

Expected:

```text
COMPONENT CREATED
```

- [ ] **Step 1.3: Verify generated files exist**

Run:

```bash
dir app\Livewire
dir resources\views\livewire
```

Expected:

```text
PokemonList.php
PokemonDetail.php
pokemon-list.blade.php
pokemon-detail.blade.php
```

- [ ] **Step 1.4: Commit Livewire setup**

Run:

```bash
git add composer.json composer.lock app/Livewire resources/views/livewire
git commit -m "chore: install livewire components"
```

Expected:

```text
[main ...] chore: install livewire components
```

---

## Phase 2: Add PokeAPI Configuration and Service Layer

**Purpose:** Centralize all external API behavior in one service so Livewire components stay small and DRY.

**Files:**

- Modify: `config/services.php`
- Modify: `.env.example`
- Create: `app/Services/PokeApiService.php`
- Test: `tests/Feature/PokeApiServiceTest.php`

- [x] **Step 2.1: Add PokeAPI config**

In `config/services.php`, add this entry to the returned array:

```php
'pokeapi' => [
    'base_url' => env('POKEAPI_BASE_URL', 'https://pokeapi.co/api/v2'),
],
```

In `.env.example`, add:

```dotenv
POKEAPI_BASE_URL=https://pokeapi.co/api/v2
```

- [x] **Step 2.2: Write service tests first**

Create `tests/Feature/PokeApiServiceTest.php`:

```php
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
```

- [ ] **Step 2.3: Run service tests and verify they fail**

> Attempted on 2026-05-30, but local `php artisan test --filter=PokeApiServiceTest` is blocked before Laravel boots because the installed Composer dependencies require PHP >= 8.4.1 and the active CLI PHP is 8.2.12.

Run:

```bash
php artisan test --filter=PokeApiServiceTest
```

Expected:

```text
Class "App\Services\PokeApiService" not found
```

- [x] **Step 2.4: Implement PokeApiService**

Create `app/Services/PokeApiService.php`:

```php
<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PokeApiService
{
    public function listPokemon(int $limit = 20, int $offset = 0): array
    {
        $limit = max(1, min($limit, 50));
        $offset = max(0, $offset);
        $cacheKey = "pokeapi:pokemon:list:{$limit}:{$offset}";

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($limit, $offset) {
            $response = $this->client()
                ->get('/pokemon', ['limit' => $limit, 'offset' => $offset])
                ->throw()
                ->json();

            $items = collect($response['results'] ?? [])
                ->map(fn (array $item) => $this->getPokemon($item['name']))
                ->filter()
                ->values()
                ->all();

            return [
                'count' => (int) ($response['count'] ?? count($items)),
                'next' => $response['next'] ?? null,
                'previous' => $response['previous'] ?? null,
                'items' => $items,
            ];
        });
    }

    public function searchPokemon(string $query, int $limit = 300): array
    {
        $query = Str::of($query)->lower()->trim()->toString();

        if ($query === '') {
            return $this->listPokemon()['items'];
        }

        $cacheKey = "pokeapi:pokemon:search-index:{$limit}";

        $index = Cache::remember($cacheKey, now()->addHours(12), function () use ($limit) {
            return $this->client()
                ->get('/pokemon', ['limit' => $limit, 'offset' => 0])
                ->throw()
                ->json('results', []);
        });

        return collect($index)
            ->filter(fn (array $item) => Str::contains($item['name'], $query))
            ->take(20)
            ->map(fn (array $item) => $this->getPokemon($item['name']))
            ->filter()
            ->values()
            ->all();
    }

    public function getPokemon(string|int $nameOrId): ?array
    {
        $nameOrId = Str::of((string) $nameOrId)->lower()->trim()->toString();
        $cacheKey = "pokeapi:pokemon:detail:{$nameOrId}";

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($nameOrId) {
            try {
                $payload = $this->client()
                    ->get("/pokemon/{$nameOrId}")
                    ->throw()
                    ->json();
            } catch (RequestException) {
                return null;
            }

            return $this->normalizePokemon($payload);
        });
    }

    private function normalizePokemon(array $payload): array
    {
        $image = data_get($payload, 'sprites.other.official-artwork.front_default')
            ?: data_get($payload, 'sprites.front_default');

        return [
            'id' => (int) $payload['id'],
            'name' => $payload['name'],
            'display_name' => Str::of($payload['name'])->replace('-', ' ')->title()->toString(),
            'image' => $image,
            'types' => collect($payload['types'] ?? [])
                ->sortBy('slot')
                ->pluck('type.name')
                ->values()
                ->all(),
            'abilities' => collect($payload['abilities'] ?? [])
                ->map(fn (array $ability) => [
                    'name' => $ability['ability']['name'],
                    'display_name' => Str::of($ability['ability']['name'])->replace('-', ' ')->title()->toString(),
                    'is_hidden' => (bool) $ability['is_hidden'],
                ])
                ->values()
                ->all(),
            'stats' => collect($payload['stats'] ?? [])
                ->map(fn (array $stat) => [
                    'name' => $stat['stat']['name'],
                    'display_name' => Str::of($stat['stat']['name'])->replace('-', ' ')->title()->toString(),
                    'value' => (int) $stat['base_stat'],
                ])
                ->values()
                ->all(),
            'height' => (int) $payload['height'],
            'weight' => (int) $payload['weight'],
            'base_experience' => $payload['base_experience'] ?? null,
            'moves' => collect($payload['moves'] ?? [])
                ->take(20)
                ->pluck('move.name')
                ->map(fn (string $move) => Str::of($move)->replace('-', ' ')->title()->toString())
                ->values()
                ->all(),
        ];
    }

    private function client()
    {
        return Http::baseUrl(config('services.pokeapi.base_url'))
            ->acceptJson()
            ->timeout(10)
            ->retry(2, 200);
    }
}
```

- [ ] **Step 2.5: Run service tests and verify they pass**

> Blocked by the same local PHP runtime mismatch noted in Step 2.3. Syntax checks passed for `app/Services/PokeApiService.php` and `tests/Feature/PokeApiServiceTest.php`.

Run:

```bash
php artisan test --filter=PokeApiServiceTest
```

Expected:

```text
PASS
```

- [ ] **Step 2.6: Commit service layer**

Run:

```bash
git add config/services.php .env.example app/Services/PokeApiService.php tests/Feature/PokeApiServiceTest.php
git commit -m "feat: add pokeapi service layer"
```

Expected:

```text
[main ...] feat: add pokeapi service layer
```

---

## Phase 3: Build Pokemon List Feature

**Purpose:** Implement the homepage list with search, pagination/load more, empty state, and API error handling.

**Files:**

- Modify: `routes/web.php`
- Modify: `app/Livewire/PokemonList.php`
- Modify: `resources/views/livewire/pokemon-list.blade.php`
- Create: `resources/views/components/pokemon/type-badge.blade.php`
- Create: `resources/views/components/pokemon/empty-state.blade.php`
- Create: `resources/views/components/pokemon/error-state.blade.php`
- Create: `resources/views/components/pokemon/skeleton-card.blade.php`
- Test: `tests/Feature/Livewire/PokemonListTest.php`

- [x] **Step 3.1: Add route for homepage**

Update `routes/web.php`:

```php
<?php

use App\Livewire\PokemonDetail;
use App\Livewire\PokemonList;
use Illuminate\Support\Facades\Route;

Route::get('/', PokemonList::class)->name('pokemon.index');
Route::get('/pokemon/{name}', PokemonDetail::class)->name('pokemon.show');
```

> Completed with a minimal `PokemonDetail` placeholder component and view so the `/pokemon/{name}` route is valid until Phase 4 implements the full detail page.

> Completed with `resources/views/layouts/app.blade.php` so Livewire full-page components can render through the default `layouts::app` layout.

> Completed by running `npm.cmd run build` and removing the remote Bunny font fetch from `vite.config.js`, so `public/build/manifest.json` can be generated without network access.

- [x] **Step 3.2: Write Livewire list tests**

Create `tests/Feature/Livewire/PokemonListTest.php`:

```php
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
```

- [ ] **Step 3.3: Run list tests and verify they fail**

> Attempted on 2026-05-30, but local `php artisan test --filter=PokemonListTest` is blocked before Laravel boots because the installed Composer dependencies require PHP >= 8.4.1 and the active CLI PHP is 8.2.12.

Run:

```bash
php artisan test --filter=PokemonListTest
```

Expected:

```text
FAIL
```

- [x] **Step 3.4: Implement PokemonList component**

Update `app/Livewire/PokemonList.php`:

```php
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
```

- [x] **Step 3.5: Add reusable list UI components**

Create `resources/views/components/pokemon/type-badge.blade.php`:

```blade
@props(['type'])

@php
    $colors = [
        'normal' => 'bg-zinc-100 text-zinc-700 ring-zinc-200',
        'fire' => 'bg-red-100 text-red-700 ring-red-200',
        'water' => 'bg-sky-100 text-sky-700 ring-sky-200',
        'electric' => 'bg-yellow-100 text-yellow-800 ring-yellow-200',
        'grass' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'ice' => 'bg-cyan-100 text-cyan-700 ring-cyan-200',
        'fighting' => 'bg-orange-100 text-orange-800 ring-orange-200',
        'poison' => 'bg-fuchsia-100 text-fuchsia-700 ring-fuchsia-200',
        'ground' => 'bg-amber-100 text-amber-800 ring-amber-200',
        'flying' => 'bg-indigo-100 text-indigo-700 ring-indigo-200',
        'psychic' => 'bg-pink-100 text-pink-700 ring-pink-200',
        'bug' => 'bg-lime-100 text-lime-800 ring-lime-200',
        'rock' => 'bg-stone-100 text-stone-700 ring-stone-200',
        'ghost' => 'bg-violet-100 text-violet-700 ring-violet-200',
        'dragon' => 'bg-purple-100 text-purple-700 ring-purple-200',
        'dark' => 'bg-neutral-800 text-white ring-neutral-700',
        'steel' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'fairy' => 'bg-rose-100 text-rose-700 ring-rose-200',
    ];

    $class = $colors[$type] ?? 'bg-zinc-100 text-zinc-700 ring-zinc-200';
@endphp

<span {{ $attributes->class("inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold capitalize ring-1 {$class}") }}>
    {{ str($type)->replace('-', ' ')->title() }}
</span>
```

Create `resources/views/components/pokemon/empty-state.blade.php`:

```blade
@props(['title' => 'Pokemon tidak ditemukan', 'message' => 'Coba gunakan kata kunci lain.'])

<div {{ $attributes->class('rounded-lg border border-dashed border-zinc-300 bg-white p-8 text-center') }}>
    <h2 class="text-base font-semibold text-zinc-900">{{ $title }}</h2>
    <p class="mt-2 text-sm text-zinc-500">{{ $message }}</p>
</div>
```

Create `resources/views/components/pokemon/error-state.blade.php`:

```blade
@props(['message' => 'Data belum bisa dimuat.'])

<div {{ $attributes->class('rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700') }}>
    {{ $message }}
</div>
```

Create `resources/views/components/pokemon/skeleton-card.blade.php`:

```blade
<div class="animate-pulse rounded-lg border border-zinc-200 bg-white p-5">
    <div class="mx-auto h-28 w-28 rounded-full bg-zinc-100"></div>
    <div class="mt-5 h-4 rounded bg-zinc-100"></div>
    <div class="mt-3 h-3 w-20 rounded bg-zinc-100"></div>
</div>
```

- [x] **Step 3.6: Implement list Blade view**

Update `resources/views/livewire/pokemon-list.blade.php`:

```blade
<main class="min-h-screen bg-zinc-50 text-zinc-950">
    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-4 border-b border-zinc-200 pb-6 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-red-600">PokeAPI Explorer</p>
                <h1 class="mt-2 text-3xl font-bold text-zinc-950 md:text-4xl">Pokemon Explorer</h1>
                <p class="mt-2 max-w-2xl text-sm text-zinc-600">
                    Jelajahi Pokemon, type, artwork, dan statistik dasar langsung dari PokeAPI.
                </p>
            </div>

            <label class="w-full md:max-w-sm">
                <span class="sr-only">Cari Pokemon</span>
                <input
                    wire:model.live.debounce.350ms="search"
                    type="search"
                    placeholder="Cari Pokemon..."
                    class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-red-500 focus:ring-4 focus:ring-red-100"
                >
            </label>
        </header>

        <div class="mt-6" wire:loading.delay>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (range(1, 4) as $item)
                    <x-pokemon.skeleton-card />
                @endforeach
            </div>
        </div>

        <div class="mt-6" wire:loading.delay.remove>
            @if ($error)
                <x-pokemon.error-state :message="$error" />
            @elseif (count($pokemon) === 0)
                <x-pokemon.empty-state />
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($pokemon as $item)
                        <a
                            href="{{ route('pokemon.show', $item['name']) }}"
                            wire:key="pokemon-{{ $item['id'] }}"
                            class="group rounded-lg border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-red-100"
                        >
                            <div class="flex items-center justify-between text-xs font-semibold text-zinc-400">
                                <span>#{{ str_pad((string) $item['id'], 4, '0', STR_PAD_LEFT) }}</span>
                                <span>{{ str($item['name'])->upper() }}</span>
                            </div>

                            <div class="mt-4 flex justify-center">
                                @if ($item['image'])
                                    <img
                                        src="{{ $item['image'] }}"
                                        alt="{{ $item['display_name'] }}"
                                        class="h-32 w-32 object-contain transition group-hover:scale-105"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="flex h-32 w-32 items-center justify-center rounded-full bg-zinc-100 text-sm text-zinc-400">
                                        No image
                                    </div>
                                @endif
                            </div>

                            <h2 class="mt-4 text-lg font-bold text-zinc-950">{{ $item['display_name'] }}</h2>

                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($item['types'] as $type)
                                    <x-pokemon.type-badge :type="$type" />
                                @endforeach
                            </div>
                        </a>
                    @endforeach
                </div>

                @if ($search === '' && count($pokemon) < $total)
                    <div class="mt-8 flex justify-center">
                        <button
                            wire:click="loadMore"
                            wire:loading.attr="disabled"
                            type="button"
                            class="rounded-lg bg-zinc-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            Muat lagi
                        </button>
                    </div>
                @endif
            @endif
        </div>
    </section>
</main>
```

- [ ] **Step 3.7: Run list tests**

> Blocked by the same local PHP runtime mismatch noted in Step 3.3. Syntax checks passed for `routes/web.php`, `app/Livewire/PokemonList.php`, and `tests/Feature/Livewire/PokemonListTest.php`; `git diff --check` also passed.

Run:

```bash
php artisan test --filter=PokemonListTest
```

Expected:

```text
PASS
```

- [ ] **Step 3.8: Commit list feature**

Run:

```bash
git add routes/web.php app/Livewire/PokemonList.php resources/views/livewire/pokemon-list.blade.php resources/views/components/pokemon tests/Feature/Livewire/PokemonListTest.php
git commit -m "feat: add pokemon list"
```

Expected:

```text
[main ...] feat: add pokemon list
```

---

## Phase 4: Build Pokemon Detail Feature

**Purpose:** Implement detail route and page with artwork, types, abilities, stats, height, weight, base experience, moves, loading, and error states.

**Files:**

- Modify: `app/Livewire/PokemonDetail.php`
- Modify: `resources/views/livewire/pokemon-detail.blade.php`
- Create: `resources/views/components/pokemon/stat-bar.blade.php`
- Test: `tests/Feature/Livewire/PokemonDetailTest.php`

- [x] **Step 4.1: Write Livewire detail tests**

Create `tests/Feature/Livewire/PokemonDetailTest.php`:

```php
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
```

- [ ] **Step 4.2: Run detail tests and verify they fail**

> Attempted on 2026-05-30, but local `php artisan test --filter=PokemonDetailTest` is blocked before Laravel boots because the installed Composer dependencies require PHP >= 8.4.1 and the active CLI PHP is 8.2.12.

Run:

```bash
php artisan test --filter=PokemonDetailTest
```

Expected:

```text
FAIL
```

- [x] **Step 4.3: Implement PokemonDetail component**

Update `app/Livewire/PokemonDetail.php`:

```php
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
```

- [x] **Step 4.4: Add stat bar component**

Create `resources/views/components/pokemon/stat-bar.blade.php`:

```blade
@props(['label', 'value'])

@php
    $percentage = min(100, round(((int) $value / 255) * 100));
@endphp

<div>
    <div class="flex items-center justify-between gap-4 text-sm">
        <span class="font-medium text-zinc-700">{{ $label }}</span>
        <span class="tabular-nums font-semibold text-zinc-950">{{ $value }}</span>
    </div>
    <div class="mt-2 h-2 overflow-hidden rounded-full bg-zinc-100">
        <div class="h-full rounded-full bg-red-500" style="width: {{ $percentage }}%"></div>
    </div>
</div>
```

- [x] **Step 4.5: Implement detail Blade view**

Update `resources/views/livewire/pokemon-detail.blade.php`:

```blade
<main class="min-h-screen bg-zinc-50 text-zinc-950">
    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <a href="{{ route('pokemon.index') }}" class="inline-flex items-center text-sm font-semibold text-zinc-600 transition hover:text-red-600">
            Kembali ke daftar
        </a>

        <div class="mt-6">
            @if ($error)
                <x-pokemon.error-state :message="$error" />
            @elseif ($pokemon)
                <div class="grid gap-6 lg:grid-cols-[minmax(0,420px)_1fr]">
                    <section class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between text-sm font-semibold text-zinc-400">
                            <span>#{{ str_pad((string) $pokemon['id'], 4, '0', STR_PAD_LEFT) }}</span>
                            <span>{{ str($pokemon['name'])->upper() }}</span>
                        </div>

                        <div class="mt-6 flex justify-center">
                            @if ($pokemon['image'])
                                <img
                                    src="{{ $pokemon['image'] }}"
                                    alt="{{ $pokemon['display_name'] }}"
                                    class="h-64 w-64 object-contain"
                                >
                            @else
                                <div class="flex h-64 w-64 items-center justify-center rounded-full bg-zinc-100 text-sm text-zinc-400">
                                    No image
                                </div>
                            @endif
                        </div>

                        <h1 class="mt-6 text-3xl font-bold">{{ $pokemon['display_name'] }}</h1>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($pokemon['types'] as $type)
                                <x-pokemon.type-badge :type="$type" />
                            @endforeach
                        </div>

                        <dl class="mt-6 grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-lg bg-zinc-50 p-3">
                                <dt class="text-xs font-medium text-zinc-500">Height</dt>
                                <dd class="mt-1 font-semibold">{{ $pokemon['height'] }}</dd>
                            </div>
                            <div class="rounded-lg bg-zinc-50 p-3">
                                <dt class="text-xs font-medium text-zinc-500">Weight</dt>
                                <dd class="mt-1 font-semibold">{{ $pokemon['weight'] }}</dd>
                            </div>
                            <div class="rounded-lg bg-zinc-50 p-3">
                                <dt class="text-xs font-medium text-zinc-500">EXP</dt>
                                <dd class="mt-1 font-semibold">{{ $pokemon['base_experience'] ?? '-' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="space-y-6">
                        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
                            <h2 class="text-lg font-bold">Base Stats</h2>
                            <div class="mt-5 space-y-4">
                                @foreach ($pokemon['stats'] as $stat)
                                    <x-pokemon.stat-bar :label="$stat['display_name']" :value="$stat['value']" />
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
                            <h2 class="text-lg font-bold">Abilities</h2>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($pokemon['abilities'] as $ability)
                                    <span class="rounded-full bg-zinc-100 px-3 py-1 text-sm font-medium text-zinc-700">
                                        {{ $ability['display_name'] }}{{ $ability['is_hidden'] ? ' (Hidden)' : '' }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
                            <h2 class="text-lg font-bold">Moves</h2>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($pokemon['moves'] as $move)
                                    <span class="rounded-md border border-zinc-200 bg-white px-3 py-1 text-sm text-zinc-700">
                                        {{ $move }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </section>
                </div>
            @endif
        </div>
    </section>
</main>
```

- [ ] **Step 4.6: Run detail tests**

> Blocked by the same local PHP runtime mismatch noted in Step 4.2. Syntax checks passed for `app/Livewire/PokemonDetail.php`, `tests/Feature/Livewire/PokemonDetailTest.php`, and `resources/views/components/pokemon/stat-bar.blade.php`; `git diff --check` also passed.

Run:

```bash
php artisan test --filter=PokemonDetailTest
```

Expected:

```text
PASS
```

- [ ] **Step 4.7: Commit detail feature**

Run:

```bash
git add app/Livewire/PokemonDetail.php resources/views/livewire/pokemon-detail.blade.php resources/views/components/pokemon/stat-bar.blade.php tests/Feature/Livewire/PokemonDetailTest.php
git commit -m "feat: add pokemon detail page"
```

Expected:

```text
[main ...] feat: add pokemon detail page
```

---

## Phase 5: Add Polished Responsive UI

**Purpose:** Ensure the app feels elegant, responsive, accessible, and consistent.

**Files:**

- Modify: `resources/css/app.css`
- Modify: `resources/views/livewire/pokemon-list.blade.php`
- Modify: `resources/views/livewire/pokemon-detail.blade.php`
- Modify: `resources/views/components/pokemon/*.blade.php`

- [x] **Step 5.1: Verify Tailwind is installed and built by Laravel**

Run:

```bash
npm install
npm run build
```

Expected:

```text
built
```

- [ ] **Step 5.2: Review responsive breakpoints manually**

> Attempted on 2026-05-30, but local `php artisan serve --host=127.0.0.1 --port=8000` is blocked before Laravel boots because the installed Composer dependencies require PHP >= 8.4.1 and the active CLI PHP is 8.2.12. UI polish was applied defensively for 375px, 768px, and 1440px breakpoints, and `npm.cmd run build` passed.

Run:

```bash
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

Check these viewport widths:

```text
375px mobile
768px tablet
1440px desktop
```

Expected:

```text
No text overlap, cards remain aligned, detail content stacks on mobile and uses two columns on desktop.
```

- [ ] **Step 5.3: Check UI state coverage**

> Blocked by the same local PHP runtime mismatch noted in Step 5.2. The list/detail Blade files and shared Pokemon components were updated for responsive wrapping, stable card heights, accessible focus states, empty/error states, and detail content stacking.

Manually verify:

```text
Homepage default list loads.
Search with "pika" shows Pikachu when API is available.
Search with an impossible query shows empty state.
Detail page for /pokemon/pikachu loads.
Detail page for /pokemon/not-a-pokemon shows friendly error.
```

- [ ] **Step 5.4: Commit UI polish**

Run:

```bash
git add resources/css/app.css resources/views
git commit -m "style: polish pokemon explorer ui"
```

Expected:

```text
[main ...] style: polish pokemon explorer ui
```

---

## Phase 6: Add Tests and Quality Gates

**Purpose:** Make the MVP safe to maintain by covering service behavior, Livewire state, routes, and rendering.

**Files:**

- Modify: `tests/Feature/PokeApiServiceTest.php`
- Modify: `tests/Feature/Livewire/PokemonListTest.php`
- Modify: `tests/Feature/Livewire/PokemonDetailTest.php`
- Create: `tests/Feature/PokemonRoutesTest.php`

- [ ] **Step 6.1: Add route tests**

Create `tests/Feature/PokemonRoutesTest.php`:

```php
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
}
```

- [ ] **Step 6.2: Run the full test suite**

Run:

```bash
php artisan test
```

Expected:

```text
PASS
```

- [ ] **Step 6.3: Run code formatter**

Run:

```bash
vendor/bin/pint
```

Expected:

```text
PASS
```

- [ ] **Step 6.4: Re-run tests after formatting**

Run:

```bash
php artisan test
```

Expected:

```text
PASS
```

- [ ] **Step 6.5: Commit tests**

Run:

```bash
git add tests
git commit -m "test: cover pokemon explorer mvp"
```

Expected:

```text
[main ...] test: cover pokemon explorer mvp
```

---

## Phase 7: Final Verification and Cleanup

**Purpose:** Confirm the MVP matches `PRD.md`, runs locally, and has a clean implementation record.

**Files:**

- Modify: `README.md`
- Modify: `PLAN.md`

- [ ] **Step 7.1: Add README setup notes**

Update `README.md` with:

````markdown
# Pokemon Explorer

Laravel + Livewire MVP for browsing Pokemon from PokeAPI.

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`.

## Notes

- No auth is required.
- No database persistence is used for Pokemon data.
- Pokemon data comes from `https://pokeapi.co/api/v2`.
- API responses are cached with Laravel Cache.
````

- [ ] **Step 7.2: Run production asset build**

Run:

```bash
npm run build
```

Expected:

```text
built
```

- [ ] **Step 7.3: Run final tests**

Run:

```bash
php artisan test
```

Expected:

```text
PASS
```

- [ ] **Step 7.4: Verify no auth or Pokemon database persistence was added**

Run:

```bash
dir app\Models
dir database\migrations
```

Expected:

```text
No Pokemon model.
No auth scaffolding migration requirement for this MVP.
No Pokemon migration.
```

- [ ] **Step 7.5: Update checklist status in PLAN.md**

Mark completed phases from:

```markdown
- [ ] Phase N
```

to:

```markdown
- [x] Phase N
```

Only mark a phase complete after its tests and manual checks pass.

- [ ] **Step 7.6: Commit documentation cleanup**

Run:

```bash
git add README.md PLAN.md
git commit -m "docs: add implementation tracking plan"
```

Expected:

```text
[main ...] docs: add implementation tracking plan
```

---

## PRD Coverage Checklist

- [ ] List page shows Pokemon cards with name, image, number, and type.
- [ ] Search by Pokemon name works with Livewire debounce.
- [ ] Empty state appears when search has no results.
- [ ] Error state appears when PokeAPI fails.
- [ ] Load more or pagination exists.
- [ ] Detail page shows artwork, types, abilities, stats, height, weight, base experience, and moves.
- [ ] UI is responsive on mobile, tablet, and desktop.
- [ ] No auth is added.
- [ ] No Pokemon database persistence is added.
- [ ] All PokeAPI access goes through `PokeApiService`.
- [ ] PokeAPI responses are cached.
- [ ] Main service and Livewire tests pass.

## Implementation Notes

- Prefer small commits at the end of each phase.
- Keep Livewire components focused on UI state.
- Keep API endpoint details inside `PokeApiService`.
- Do not add database-backed favorites, filters, admin, or auth during MVP.
- If PokeAPI request volume becomes high during manual testing, lower the list page size before adding new infrastructure.
