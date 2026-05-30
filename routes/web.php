<?php

use App\Livewire\PokemonDetail;
use App\Livewire\PokemonList;
use Illuminate\Support\Facades\Route;

Route::get('/', PokemonList::class)->name('pokemon.index');
Route::get('/pokemon/{name}', PokemonDetail::class)->name('pokemon.show');
