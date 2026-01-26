<?php

namespace App\Http\Controllers;

use App\Services\FilmService;

class DebugController extends Controller
{
    public function films()
    {
        $films = FilmService::getLatest(3);
        
        $result = [];
        foreach ($films->getAll() as $film) {
            $data = [
                'class' => get_class($film),
                'id' => $film->getId(),
                'title' => $film->getTitle() ?? $film->getNavn() ?? 'NO TITLE',
                'methods' => [
                    'getThumbnail()' => $film->getThumbnail() ?? 'null',
                    'getThumbnailShare()' => $film->getThumbnailShare() ?? 'null',
                    'getImageUrl()' => $film->getImageUrl() ?? 'null',
                    'getImagePath()' => $film->getImagePath() ?? 'null',
                    'getBildeUrl()' => $film->getBildeUrl() ?? 'null',
                    'getMiniaturebilde()' => method_exists($film, 'getMiniaturebilde') ? ($film->getMiniaturebilde() ?? 'null') : 'N/A',
                ]
            ];
            $result[] = $data;
        }
        
        return response()->json($result);
    }

    public function festivalYear($year)
    {
        $year = (int) $year;
        $films = FilmService::getFestivalYearFilms($year);

        $result = [];
        foreach ($films as $film) {
            try {
                $result[] = [
                    'class' => get_class($film),
                    'id' => method_exists($film, 'getId') ? $film->getId() : null,
                    'title' => method_exists($film, 'getTitle') ? $film->getTitle() : (method_exists($film, 'getNavn') ? $film->getNavn() : null),
                ];
            } catch (\Throwable $e) {
                $result[] = [
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json($result);
    }

    public function film($id)
    {
        $id = (int) $id;
        $paths = [];

        // Try getById
        try {
            $film = \UKMNorge\Filmer\UKMTV\Filmer::getById($id);
            $paths['getById'] = $film ? self::filmMeta($film) : null;
        } catch (\Throwable $e) {
            $paths['getById'] = 'error: ' . $e->getMessage();
            $film = null;
        }

        // Try getByTvId if available
        if (method_exists(\UKMNorge\Filmer\UKMTV\Filmer::class, 'getByTvId')) {
            try {
                $filmTv = \UKMNorge\Filmer\UKMTV\Filmer::getByTvId($id);
                $paths['getByTvId'] = $filmTv ? self::filmMeta($filmTv) : null;
            } catch (\Throwable $e) {
                $paths['getByTvId'] = 'error: ' . $e->getMessage();
            }
        }

        // Try CF id
        try {
            $filmCf = \UKMNorge\Filmer\UKMTV\Filmer::getByCFId((string) $id);
            $paths['getByCFId'] = $filmCf ? self::filmMeta($filmCf) : null;
        } catch (\Throwable $e) {
            $paths['getByCFId'] = 'error: ' . $e->getMessage();
        }

        return response()->json($paths);
    }

    private static function filmMeta($film)
    {
        return [
            'class' => get_class($film),
            'id' => method_exists($film, 'getId') ? $film->getId() : null,
            'tvId' => method_exists($film, 'getTvId') ? $film->getTvId() : null,
            'cfId' => method_exists($film, 'getCloudflareId') ? $film->getCloudflareId() : null,
            'title' => method_exists($film, 'getTitle') ? $film->getTitle() : (method_exists($film, 'getNavn') ? $film->getNavn() : null),
        ];
    }
}
