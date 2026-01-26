<?php

namespace App\Http\Controllers;

use App\Services\FilmService;
use Inertia\Inertia;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Show search page
     */
    public function index()
    {
        return Inertia::render('Search/Index');
    }

    /**
     * Search films
     */
    public function results(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return Inertia::render('Search/Results', [
                'query' => $query,
                'films' => []
            ]);
        }
        
        $films = FilmService::search($query);
        $data = [];

        foreach ($films as $film) {
            try {
                $data[] = FilmService::filmToArray($film);
            } catch (\Throwable $e) {
                continue;
            }
        }
        
        return Inertia::render('Search/Results', [
            'query' => $query,
            'films' => $data
        ]);
    }

    /**
     * API: Search films
     */
    public function api(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
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
