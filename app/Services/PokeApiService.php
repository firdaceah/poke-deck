<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
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

    private function client(): PendingRequest
    {
        return Http::baseUrl(config('services.pokeapi.base_url'))
            ->acceptJson()
            ->timeout(10)
            ->retry(2, 200);
    }
}
