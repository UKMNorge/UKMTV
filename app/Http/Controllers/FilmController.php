<?php

namespace App\Http\Controllers;

use App\Services\FilmService;
use Inertia\Inertia;

class FilmController extends Controller
{
    /**
     * Get latest films (API endpoint)
     */
    public function latest()
    {
        $films = FilmService::getLatest(50);
        $data = [];
        
        foreach ($films as $film) {
            try {
                $data[] = FilmService::filmToArray($film);
            } catch (\Throwable $e) {
                continue;
            }
        }
        
        return response()->json($data);
    }

    /**
     * Show film detail page
     */
    public function show($id)
    {
        $film = FilmService::getById($id);
        
        if (!$film) {
            return abort(404, 'Film not found');
        }
        
        return Inertia::render('Film/Show', [
            'film' => FilmService::filmToArray($film)
        ]);
    }

    /**
     * Search films
     */
    public function search($query)
    {
        $films = FilmService::search($query);
        $data = [];
        
        foreach ($films as $film) {
            try {
                $data[] = FilmService::filmToArray($film);
            } catch (\Throwable $e) {
                continue;
            }
        }
        
        return response()->json($data);
    }
}
