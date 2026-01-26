<?php

namespace App\Http\Controllers;

use App\Services\FilmService;
use Inertia\Inertia;

class HomeController extends Controller
{
    /**
     * Show home page with latest films
     */
    public function index()
    {
        $films = FilmService::getLatest(12);
        $data = [];
        
        foreach ($films as $film) {
            $data[] = FilmService::filmToArray($film);
        }
        
        return Inertia::render('Home', [
            'latestFilms' => $data
        ]);
    }
}
