<?php

use App\Livewire\PokemonDetail;
use App\Livewire\PokemonList;
use App\Services\PokeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', PokemonList::class)->name('pokemon.index');
Route::get('/pokemon/search', function (Request $request, PokeApiService $pokeApi) {
    $limit = 20;
    $query = trim((string) $request->query('q', ''));

    if ($query === '') {
        $result = $pokeApi->listPokemon($limit, 0);
        $items = $result['items'];
        $hasMore = $result['count'] > count($items);
        $nextOffset = $limit;
    } else {
        $items = $pokeApi->searchPokemon($query);
        $hasMore = false;
        $nextOffset = 0;
    }

    $html = collect($items)
        ->map(fn (array $item) => view('components.pokemon.card', ['item' => $item])->render())
        ->implode('');

    return response()->json([
        'html' => $html,
        'emptyHtml' => view('components.pokemon.empty-state')->render(),
        'hasResults' => count($items) > 0,
        'hasMore' => $hasMore,
        'nextOffset' => $nextOffset,
    ]);
})->name('pokemon.search');
Route::get('/pokemon/load-more', function (Request $request, PokeApiService $pokeApi) {
    $limit = 20;
    $offset = max(0, (int) $request->integer('offset', 0));
    $result = $pokeApi->listPokemon($limit, $offset);
    $html = collect($result['items'])
        ->map(fn (array $item) => view('components.pokemon.card', ['item' => $item])->render())
        ->implode('');

    return response()->json([
        'html' => $html,
        'nextOffset' => $offset + $limit,
        'hasMore' => ($offset + $limit) < $result['count'],
    ]);
})->name('pokemon.load-more');
Route::get('/pokemon/{name}', PokemonDetail::class)->name('pokemon.show');
