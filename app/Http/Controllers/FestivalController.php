<?php

namespace App\Http\Controllers;

use App\Services\FilmService;
use Inertia\Inertia;

class FestivalController extends Controller
{
    /**
     * Show all available festival years
     */
    public function years()
    {
        $years = FilmService::getFestivalYears();
        
        return Inertia::render('Festival/Years', [
            'years' => $years
        ]);
    }

    /**
     * Show films for a specific festival year
     */
    public function year($year)
    {
        $year = (int) $year;
        $films = FilmService::getFestivalYearFilms($year); // returns safe array
        $data = [];

        foreach ($films as $film) {
            try {
                $data[] = FilmService::filmToArray($film);
            } catch (\Throwable $e) {
                // Skip problematic legacy film entries
                continue;
            }
        }
        
        return Inertia::render('Festival/Show', [
            'year' => $year,
            'films' => $data
        ]);
    }

    /**
     * Get festival years as API endpoint
     */
    public function yearsApi()
    {
        $years = FilmService::getFestivalYears();
        return response()->json($years);
    }

    /**
     * Get films for a year as API endpoint
     */
    public function yearApi($year)
    {
        $year = (int) $year;
        $films = FilmService::getFestivalYearFilms($year); // returns safe array
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
