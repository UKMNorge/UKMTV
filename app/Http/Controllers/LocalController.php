<?php

namespace App\Http\Controllers;

use App\Services\FilmService;
use Inertia\Inertia;

class LocalController extends Controller
{
    /**
     * Show Lokalfestivaler landing: choose fylke, then kommune; also show recent picks
     */
    public function index()
    {
        $fylker = FilmService::getFylkerWithRecentKommuner(FilmService::LOCAL_MIN_YEAR);
        $fylkerData = [];
        foreach ($fylker as $fylke) {
            $fylkerData[] = [
                'id' => $fylke->getId(),
                'name' => $fylke->getNavn(),
                'link' => $fylke->getLink(),
            ];
        }

        return Inertia::render('Local/Index', [
            'fylker' => $fylkerData,
        ]);
    }

    public function fylke($fylkeKey, $year = null)
    {
        $fylke = FilmService::getFylkeByLink($fylkeKey);
        if (!$fylke) {
            return abort(404);
        }

        $kommuner = FilmService::getFylkeKommunerWithFilms($fylke, FilmService::LOCAL_MIN_YEAR);
        $kommunerData = [];
        foreach ($kommuner as $kommune) {
            $years = FilmService::getKommuneYears($kommune, FilmService::LOCAL_MIN_YEAR);
            if (empty($years)) {
                continue; // skip kommuner without any film years
            }

            $kommunerData[] = [
                'id' => $kommune->getId(),
                'name' => $kommune->getNavn(),
                'defaultYear' => $years[0], // newest first
            ];
        }

        return Inertia::render('Local/ShowFylke', [
            'fylke' => [
                'id' => $fylke->getId(),
                'name' => $fylke->getNavn(),
                'link' => $fylke->getLink(),
            ],
            'kommuner' => $kommunerData,
        ]);
    }

    public function kommune($fylkeKey, $year, $kommuneId)
    {
        $requestedYear = $year ? (int) $year : null;
        $fylke = FilmService::getFylkeByLink($fylkeKey);
        if (!$fylke) {
            return abort(404);
        }

        $kommuner = FilmService::getFylkeKommunerWithFilms($fylke, FilmService::LOCAL_MIN_YEAR);
        $kommuneObj = null;
        foreach ($kommuner as $kommune) {
            if ((int) $kommune->getId() === (int) $kommuneId) {
                $kommuneObj = $kommune;
                break;
            }
        }
        if (!$kommuneObj) {
            return abort(404);
        }

        $years = FilmService::getKommuneYears($kommuneObj, FilmService::LOCAL_MIN_YEAR);
        if (empty($years)) {
            return abort(404);
        }

        $latestYear = FilmService::getLatestYearWithKommuneFilms($kommuneObj, FilmService::LOCAL_MIN_YEAR);
        $yearToUse = $requestedYear ?? ($latestYear ?? $years[0]);

        // If requested year is not available, jump to the newest available year
        if (!in_array($yearToUse, $years, true)) {
            $yearToUse = $years[0];
        }

        $films = FilmService::getKommuneYearFilms($kommuneObj, $yearToUse, FilmService::LOCAL_MIN_YEAR);

        // If requested year has no renderable films but another year does, redirect to the latest available
        if ((empty($films) || empty($years) || !in_array($yearToUse, $years, true)) && !empty($years)) {
            $yearToUse = $years[0]; // years are reversed (newest first)
            $films = FilmService::getKommuneYearFilms($kommuneObj, $yearToUse, FilmService::LOCAL_MIN_YEAR);
        }

        if (empty($films) && $latestYear && $latestYear !== $yearToUse) {
            $yearToUse = $latestYear;
            $films = FilmService::getKommuneYearFilms($kommuneObj, $yearToUse, FilmService::LOCAL_MIN_YEAR);
        }
        $filmData = [];
        foreach ($films as $film) {
            try {
                $filmData[] = FilmService::filmToArray($film);
            } catch (\Throwable $e) {
                continue;
            }
        }

        return Inertia::render('Local/ShowKommune', [
            'fylke' => [
                'id' => $fylke->getId(),
                'name' => $fylke->getNavn(),
                'link' => $fylke->getLink(),
            ],
            'kommune' => [
                'id' => $kommuneObj->getId(),
                'name' => $kommuneObj->getNavn(),
            ],
            'year' => $yearToUse,
            'years' => $years,
            'films' => $filmData,
        ]);
    }
}
