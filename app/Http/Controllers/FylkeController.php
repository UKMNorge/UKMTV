<?php

namespace App\Http\Controllers;

use App\Services\FilmService;
use Inertia\Inertia;

class FylkeController extends Controller
{
    /**
     * Show all fylker
     */
    public function index()
    {
        $fylker = FilmService::getFylker();
        $fylkerList = is_array($fylker)
            ? $fylker
            : (method_exists($fylker, 'getAll') ? $fylker->getAll() : []);

        $fylkerData = [];
        foreach ($fylkerList as $fylke) {
            // Only include fylker that have films from 2023 and up
            $years = FilmService::getFylkeYears($fylke);
            if (empty($years)) {
                continue;
            }
            $fylkerData[] = [
                'id' => $fylke->getId(),
                'name' => $fylke->getNavn(),
                'link' => $fylke->getLink(),
            ];
        }
        
        return Inertia::render('Fylke/Index', [
            'fylker' => $fylkerData
        ]);
    }

    /**
     * Show fylke with years and kommuner
     */
    public function show($fylkeKey)
    {
        $fylke = FilmService::getFylkeByLink($fylkeKey);
        
        if (!$fylke) {
            return response()->json(['error' => 'Fylke not found'], 404);
        }
        
        $years = FilmService::getFylkeYears($fylke);
        $kommuner = FilmService::getFylkeKommunerWithFilms($fylke, FilmService::LOCAL_MIN_YEAR);
        
        $kommunerData = [];
        foreach ($kommuner as $kommune) {
            $kommuneYears = FilmService::getKommuneYears($kommune, FilmService::LOCAL_MIN_YEAR);
            if (empty($kommuneYears)) {
                continue;
            }
            $kommunerData[] = [
                'id' => $kommune->getId(),
                'name' => $kommune->getNavn(),
                'defaultYear' => $kommuneYears[0],
            ];
        }
        
        return Inertia::render('Fylke/Show', [
            'fylke' => [
                'id' => $fylke->getId(),
                'name' => $fylke->getNavn(),
                'link' => $fylke->getLink(),
            ],
            'years' => $years,
            'kommuner' => $kommunerData
        ]);
    }

    /**
     * Show films for a fylke year
     */
    public function year($fylkeKey, $year)
    {
        $year = (int) $year;
        $fylke = FilmService::getFylkeByLink($fylkeKey);
        
        if (!$fylke) {
            return response()->json(['error' => 'Fylke not found'], 404);
        }
        
        $films = FilmService::getFylkeYearFilms($fylke, $year);
        $data = [];

        foreach ($films as $film) {
            try {
                $data[] = FilmService::filmToArray($film);
            } catch (\Throwable $e) {
                continue;
            }
        }
        
        return Inertia::render('Fylke/Year', [
            'fylke' => [
                'id' => $fylke->getId(),
                'name' => $fylke->getNavn(),
                'link' => $fylke->getLink(),
            ],
            'year' => $year,
            'films' => $data
        ]);
    }

    /**
     * API: Get all fylker
     */
    public function indexApi()
    {
        $fylker = FilmService::getFylker();
        $fylkerList = is_array($fylker)
            ? $fylker
            : (method_exists($fylker, 'getAll') ? $fylker->getAll() : []);

        $fylkerData = [];
        foreach ($fylkerList as $fylke) {
            $years = FilmService::getFylkeYears($fylke);
            if (empty($years)) {
                continue;
            }
            $fylkerData[] = [
                'id' => $fylke->getId(),
                'name' => $fylke->getNavn(),
                'link' => $fylke->getLink(),
            ];
        }
        
        return response()->json($fylkerData);
    }

    /**
     * API: Get fylke years
     */
    public function yearsApi($fylkeKey)
    {
        $fylke = FilmService::getFylkeByLink($fylkeKey);
        
        if (!$fylke) {
            return response()->json(['error' => 'Fylke not found'], 404);
        }
        
        $years = FilmService::getFylkeYears($fylke);
        return response()->json($years);
    }

    /**
     * API: Get films for fylke year
     */
    public function yearApi($fylkeKey, $year)
    {
        $year = (int) $year;
        $fylke = FilmService::getFylkeByLink($fylkeKey);
        
        if (!$fylke) {
            return response()->json(['error' => 'Fylke not found'], 404);
        }
        
        $films = FilmService::getFylkeYearFilms($fylke, $year);
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
