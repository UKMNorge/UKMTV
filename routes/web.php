<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\FestivalController;
use App\Http\Controllers\FylkeController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\DebugController;

// Debug
Route::get('/debug/films', [DebugController::class, 'films'])->name('debug.films');
Route::get('/debug/festival/{year}', [DebugController::class, 'festivalYear'])->name('debug.festival.year');
Route::get('/debug/film/{id}', [DebugController::class, 'film'])->name('debug.film');

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Films
Route::get('/film/{id}', [FilmController::class, 'show'])->name('film.show');

// Festival
Route::get('/festival', [FestivalController::class, 'years'])->name('festival.years');
Route::get('/festival/{year}', [FestivalController::class, 'year'])->name('festival.year');

// Fylke (Counties)
Route::get('/fylke', [FylkeController::class, 'index'])->name('fylke.index');
Route::get('/fylke/{fylkeKey}', [FylkeController::class, 'show'])->name('fylke.show');
Route::get('/fylke/{fylkeKey}/{year}', [FylkeController::class, 'year'])->name('fylke.year');

// Lokal (kommuner)
Route::get('/lokal', [LocalController::class, 'index'])->name('lokal.index');
Route::get('/lokal/{fylkeKey}/{year?}', [LocalController::class, 'fylke'])->name('lokal.fylke');
Route::get('/lokal/{fylkeKey}/{year}/{kommuneId}', [LocalController::class, 'kommune'])->name('lokal.kommune');

// Search
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/search/results', [SearchController::class, 'results'])->name('search.results');

// API Routes
Route::prefix('api')->group(function () {
    Route::get('/films/latest', [FilmController::class, 'latest']);
    Route::get('/film/{id}/search', [FilmController::class, 'search']);
    
    Route::get('/festival/years', [FestivalController::class, 'yearsApi']);
    Route::get('/festival/{year}/films', [FestivalController::class, 'yearApi']);
    
    Route::get('/fylke', [FylkeController::class, 'indexApi']);
    Route::get('/fylke/{fylkeKey}/years', [FylkeController::class, 'yearsApi']);
    Route::get('/fylke/{fylkeKey}/{year}/films', [FylkeController::class, 'yearApi']);

    Route::get('/search', [SearchController::class, 'api']);
});
