<?php

namespace App\Services;

use UKMNorge\Filmer\UKMTV\Filmer;
use UKMNorge\Filmer\UKMTV\Film;
use UKMNorge\Filmer\UKMTV\Tags\Tag;
use UKMNorge\Filmer\UKMTV\Tags\Tags;
use UKMNorge\Geografi\Fylker;
use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Innslag\Innslag;
use UKMNorge\Geografi\Kommune;

class FilmService
{
    private const MIN_YEAR = 2023;
    // Lokal views should use the same minimum year as the rest of the site
    public const LOCAL_MIN_YEAR = self::MIN_YEAR;
    /**
     * Get the latest films
     */
    public static function getLatest(int $limit = 50)
    {
        // Fetch extra to allow filtering out older films
        $batch = Filmer::getLatest($limit * 3);
        $items = method_exists($batch, 'getAll') ? $batch->getAll() : [];

        $filtered = [];
        foreach ($items as $film) {
            if (self::isRecent($film)) {
                $filtered[] = $film;
            }
            if (count($filtered) >= $limit) {
                break;
            }
        }

        return $filtered;
    }

    /**
     * Get film by ID
     */
    public static function getById($id)
    {
        // If non-numeric, treat as Cloudflare ID first
        if (!is_numeric($id)) {
            try {
                return Filmer::getByCFId((string) $id);
            } catch (\Throwable $e) {
                return null;
            }
        }

        $numericId = (int) $id;

        // First try TV id
        try {
            $film = Filmer::getById($numericId);
        } catch (\Throwable $e) {
            $film = null;
        }

        if ($film) {
            return $film;
        }

        // Next try getByTvId if available
        if (method_exists(Filmer::class, 'getByTvId')) {
            try {
                $film = Filmer::getByTvId($numericId);
            } catch (\Throwable $e) {
                $film = null;
            }
            if ($film) {
                return $film;
            }
        }

        // Then try Cloudflare id (string) using numeric as string
        try {
            return Filmer::getByCFId((string) $numericId);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get film by CloudFlare ID
     */
    public static function getByCFId(string $cfId)
    {
        return Filmer::getByCFId($cfId);
    }

    /**
     * Get films by search string
     */
    public static function search(string $searchString): array
    {
        $query = trim($searchString);
        if ($query === '') {
            return [];
        }

        $results = [];
        $seen = [];

        // Primary text search (covers title/artist/sted in UKM catalogue)
        $primary = self::safeNormalizeFilms(function () use ($query) {
            return Filmer::getBySearchString($query);
        });
        foreach ($primary as $film) {
            self::pushUniqueRecent($results, $seen, $film);
        }

        // Year fallback when user types a year (e.g. 2023)
        $queryYear = self::extractYearFromQuery($query);
        if ($queryYear !== null) {
            $byYear = self::safeNormalizeFilms(function () use ($queryYear) {
                return Filmer::getByTags([
                    new Tag('sesong', $queryYear)
                ]);
            });
            foreach ($byYear as $film) {
                if (!self::isFilmYear($film, $queryYear)) {
                    continue;
                }
                self::pushUniqueRecent($results, $seen, $film, $queryYear);
            }
        }

        // Kommune match: if query matches kommune name, include kommune films across valid years
        $kommuner = self::findKommunerByName($query);
        if (!empty($kommuner)) {
            $currentYear = (int) date('Y');
            for ($year = $currentYear; $year >= self::MIN_YEAR; $year--) {
                foreach ($kommuner as $kommune) {
                    $films = self::getKommuneYearFilms($kommune, $year, self::MIN_YEAR);
                    foreach ($films as $film) {
                        self::pushUniqueRecent($results, $seen, $film, $year);
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Get films by tag
     */
    public static function getByTag(string $tagKey, int $tagId)
    {
        return Filmer::getByTag($tagKey, $tagId);
    }

    /**
     * Get films by multiple tags
     */
    public static function getByTags(array $tags)
    {
        return Filmer::getByTags($tags);
    }

    /**
     * Get films by arrangement
     */
    public static function getByArrangement(int $arrangementId)
    {
        return Filmer::getByArrangement($arrangementId);
    }

    /**
     * Get festival years with films
     */
    public static function getFestivalYears()
    {
        $years = [];
        $currentYear = intval(date('Y'));
        
        for ($year = self::MIN_YEAR; $year <= $currentYear; $year++) {
            $tags = [
                new Tag('arrangement_type', Tags::getArrangementTypeId('land')),
                new Tag('sesong', $year)
            ];
            if (Filmer::harTagsFilmer($tags)) {
                $years[] = $year;
            }
        }
        
        return array_reverse($years); // Return newest first
    }

    /**
     * Get films for a festival year
     */
    public static function getFestivalYearFilms(int $year)
    {
        if ($year < self::MIN_YEAR) {
            return [];
        }

        // Some legacy rows have malformed fields that trigger warnings when CloudflareFilm is constructed.
        // Temporarily silence warnings/notices during fetch so we can still return the rest, and materialize
        // a safe array of film objects (skipping null/invalid entries).
        $previousHandler = set_error_handler(function () {
            return true; // swallow warning/notice
        }, E_WARNING | E_NOTICE);

        $films = self::safeNormalizeFilms(function () use ($year) {
            return Filmer::getByTags([
                new Tag('arrangement_type', Tags::getArrangementTypeId('land')),
                new Tag('sesong', $year)
            ]);
        });

        // Fallback 1: if empty, try only sesong
        if (empty($films)) {
            $films = self::safeNormalizeFilms(function () use ($year) {
                return Filmer::getByTags([
                    new Tag('sesong', $year)
                ]);
            });
        }

        // Fallback 2: if still empty, try alternative arrangement_type key if available
        if (empty($films)) {
            try {
                $altId = Tags::getArrangementTypeId('festivalen');
            } catch (\Throwable $e) {
                $altId = null;
            }

            if ($altId) {
                $films = self::safeNormalizeFilms(function () use ($year, $altId) {
                    return Filmer::getByTags([
                        new Tag('arrangement_type', $altId),
                        new Tag('sesong', $year)
                    ]);
                });
            }
        }

        // Restore error handler
        if ($previousHandler !== null) {
            set_error_handler($previousHandler);
        } else {
            restore_error_handler();
        }

        return $films;
    }

    /**
     * Get latest kommune-level films (arrangement_type=kommune) for years >= MIN_YEAR
     */
    public static function getLatestKommunerFilms(int $limit = 50): array
    {
        $currentYear = intval(date('Y'));
        $collected = [];
        $seen = [];

        for ($year = $currentYear; $year >= self::MIN_YEAR && count($collected) < $limit; $year--) {
            $films = self::safeNormalizeFilms(function () use ($year) {
                return Filmer::getByTags([
                    new Tag('arrangement_type', Tags::getArrangementTypeId('kommune')),
                    new Tag('sesong', $year)
                ]);
            });

            foreach ($films as $film) {
                $id = method_exists($film, 'getId') ? $film->getId() : spl_object_id($film);
                if (isset($seen[$id])) {
                    continue;
                }
                $seen[$id] = true;
                $collected[] = $film;
                if (count($collected) >= $limit) {
                    break;
                }
            }
        }

        return $collected;
    }

    /**
     * Get fylker that have kommune-level films from MIN_YEAR and up.
     * Returns array of fylke objects.
     */
    public static function getFylkerWithRecentKommuner(int $minYear = self::MIN_YEAR): array
    {
        $fylker = self::getFylker();
        $list = is_array($fylker) ? $fylker : (method_exists($fylker, 'getAll') ? $fylker->getAll() : []);
        $result = [];

        foreach ($list as $fylke) {
            $kommuner = self::getFylkeKommunerWithFilms($fylke, $minYear);
            if (!empty($kommuner)) {
                $result[] = $fylke;
            }
        }

        return $result;
    }

    /**
     * Get films for a kommune and year (arrangement_type=kommune)
     */
    public static function getKommuneYearFilms($kommune, int $year, int $minYear = self::MIN_YEAR): array
    {
        if ($year < $minYear) {
            return [];
        }

        $kommuneIds = self::getKommuneIds($kommune);

        $films = self::safeNormalizeFilms(function () use ($kommuneIds, $year) {
            return Filmer::getByTags([
                new Tag('arrangement_type', Tags::getArrangementTypeId('kommune')),
                new Tag('kommune', $kommuneIds),
                new Tag('sesong', $year)
            ]);
        });

        // Keep only films that actually report the target year to avoid ghost years
        $filtered = [];
        foreach ($films as $film) {
            if (self::isFilmYear($film, $year)) {
                $filtered[] = $film;
            }
        }

        return $filtered;
    }

    /**
     * Get latest year (>= MIN_YEAR) where the kommune has films
     */
    public static function getLatestYearWithKommuneFilms($kommune, int $minYear = self::MIN_YEAR): ?int
    {
        $kommuneIds = self::getKommuneIds($kommune);
        $currentYear = intval(date('Y'));

        for ($year = $currentYear; $year >= $minYear; $year--) {
            $tags = [
                new Tag('arrangement_type', Tags::getArrangementTypeId('kommune')),
                new Tag('kommune', $kommuneIds),
                new Tag('sesong', $year)
            ];

            if (Filmer::harTagsFilmer($tags)) {
                return $year;
            }
        }

        return null;
    }

    /**
     * Get latest year in a fylke where any kommune has films
     */
    public static function getLatestYearWithKommunerFilmsInFylke($fylke, int $minYear = self::MIN_YEAR): ?int
    {
        $kommuner = self::getFylkeKommunerWithFilms($fylke, $minYear);
        $latest = null;

        foreach ($kommuner as $kommune) {
            $year = self::getLatestYearWithKommuneFilms($kommune, $minYear);
            if ($year === null) {
                continue;
            }
            if ($latest === null || $year > $latest) {
                $latest = $year;
            }
        }

        return $latest;
    }

    /**
     * Get kommune-level films for all kommuner in a fylke for a given year
     */
    public static function getKommunerFilmsByFylkeYear($fylke, int $year, int $minYear = self::MIN_YEAR): array
    {
        if ($year < $minYear) {
            return [];
        }

        $kommuner = self::getFylkeKommunerWithFilms($fylke, $minYear);
        $collected = [];
        $seen = [];

        foreach ($kommuner as $kommune) {
            $films = self::getKommuneYearFilms($kommune, $year, $minYear);
            foreach ($films as $film) {
                $id = method_exists($film, 'getId') ? $film->getId() : spl_object_id($film);
                if (isset($seen[$id])) {
                    continue;
                }
                $seen[$id] = true;
                $collected[] = $film;
            }
        }

        return $collected;
    }

    /**
     * Normalize a Filmer collection into a safe array of film objects
     */
    private static function safeNormalizeFilms(callable $producer): array
    {
        try {
            $collection = $producer();
        } catch (\Throwable $e) {
            return [];
        }

        if (!$collection) {
            return [];
        }

        // Prefer getAll if available
        if (method_exists($collection, 'getAll')) {
            try {
                $items = $collection->getAll();
            } catch (\Throwable $e) {
                $items = [];
            }
        } else {
            $items = $collection;
        }

        $films = [];
        try {
            foreach ($items as $film) {
                if (!$film) {
                    continue;
                }
                $films[] = $film;
            }
        } catch (\Throwable $e) {
            // ignore and return what we have
        }

        return $films;
    }

    /**
     * Build a stable key for a film to dedupe results
     */
    private static function getFilmKey($film): string
    {
        $cfId = method_exists($film, 'getCloudflareId') ? $film->getCloudflareId() : null;
        $tvId = method_exists($film, 'getTvId') ? $film->getTvId() : null;
        $filmId = method_exists($film, 'getId') ? $film->getId() : null;

        return (string) ($cfId ?: ($tvId ?: ($filmId ?: spl_object_id($film))));
    }

    /**
     * Extract a 4-digit year from a query, if present and within allowed range
     */
    private static function extractYearFromQuery(string $query): ?int
    {
        if (preg_match('/\b(20\d{2})\b/', $query, $match)) {
            $year = (int) $match[1];
            $current = (int) date('Y');
            if ($year >= self::LOCAL_MIN_YEAR && $year <= $current) {
                return $year;
            }
        }

        return null;
    }

    /**
     * Push film to list if not already seen and within year constraints
     */
    private static function pushUniqueRecent(array &$list, array &$seen, $film, ?int $targetYear = null): void
    {
        if (!$film || !self::isRecent($film)) {
            return;
        }

        if ($targetYear !== null && !self::isFilmYear($film, $targetYear)) {
            return;
        }

        $key = self::getFilmKey($film);
        if (isset($seen[$key])) {
            return;
        }

        $seen[$key] = true;
        $list[] = $film;
    }

    /**
     * Find kommuner whose names match the query (case-insensitive contains)
     */
    private static function findKommunerByName(string $query): array
    {
        $needle = mb_strtolower(trim($query));
        if ($needle === '') {
            return [];
        }

        $fylker = self::getFylker();
        $list = is_array($fylker) ? $fylker : (method_exists($fylker, 'getAll') ? $fylker->getAll() : []);
        $matches = [];

        foreach ($list as $fylke) {
            $kommuner = method_exists($fylke, 'getKommuner') ? $fylke->getKommuner()->getAll() : [];
            foreach ($kommuner as $kommune) {
                $name = method_exists($kommune, 'getNavn') ? $kommune->getNavn() : '';
                if ($name === '') {
                    continue;
                }
                if (mb_strpos(mb_strtolower($name), $needle) !== false) {
                    $matches[] = $kommune;
                }
            }
        }

        return $matches;
    }

    /**
     * Get all fylker (counties)
     */
    public static function getFylker()
    {
        return Fylker::getAllInkludertDeaktiverte();
    }

    /**
     * Get fylke by link/key
     */
    public static function getFylkeByLink(string $fylkeKey)
    {
        return Fylker::getByLink($fylkeKey);
    }

    /**
     * Get fylke years with films
     */
    public static function getFylkeYears($fylke)
    {
        $years = [];
        $currentYear = intval(date('Y'));
        
        for ($year = self::MIN_YEAR; $year <= $currentYear; $year++) {
            $fylkeTag = new Tag('fylke', $fylke->getId());
            $tags = [
                new Tag('arrangement_type', Tags::getArrangementTypeId('fylke')),
                $fylkeTag,
                new Tag('sesong', $year)
            ];
            
            if (Filmer::harTagsFilmer($tags)) {
                $years[] = $year;
            }
        }
        
        return array_reverse($years);
    }

    /**
     * Get films for a fylke year
     */
    public static function getFylkeYearFilms($fylke, int $year, int $minYear = self::MIN_YEAR): array
    {
        if ($year < $minYear || !$fylke || !method_exists($fylke, 'getId')) {
            return [];
        }

        $fylkeId = $fylke->getId();

        $films = self::safeNormalizeFilms(function () use ($fylkeId, $year) {
            return Filmer::getByTags([
                new Tag('arrangement_type', Tags::getArrangementTypeId('fylke')),
                new Tag('fylke', $fylkeId),
                new Tag('sesong', $year)
            ]);
        });

        // Keep only films that actually report the target year to avoid ghost/legacy rows
        $filtered = [];
        foreach ($films as $film) {
            if (self::isFilmYear($film, $year)) {
                $filtered[] = $film;
            }
        }

        return $filtered;
    }

    /**
     * Get kommuner in fylke with films
     */
    public static function getFylkeKommunerWithFilms($fylke, int $minYear = self::MIN_YEAR)
    {
        $kommuner = [];
        $currentYear = intval(date('Y'));
        
        foreach ($fylke->getKommuner()->getAll() as $kommune) {
            $kommuneIds = self::getKommuneIds($kommune);

            // Only include kommuner that have films from MIN_YEAR and up
            $hasRecent = false;
            for ($year = $minYear; $year <= $currentYear; $year++) {
                $tags = [
                    new Tag('arrangement_type', Tags::getArrangementTypeId('kommune')),
                    new Tag('kommune', $kommuneIds),
                    new Tag('sesong', $year)
                ];
                if (Filmer::harTagsFilmer($tags)) {
                    $hasRecent = true;
                    break;
                }
            }

            if ($hasRecent) {
                $kommuner[] = $kommune;
            }
        }
        
        return $kommuner;
    }

    /**
     * Get all years where any kommune in a fylke has films
     */
    public static function getKommunerYearsInFylke($fylke, int $minYear = self::MIN_YEAR): array
    {
        $years = [];
        $currentYear = intval(date('Y'));
        $kommuner = self::getFylkeKommunerWithFilms($fylke, $minYear);

        for ($year = $minYear; $year <= $currentYear; $year++) {
            foreach ($kommuner as $kommune) {
                $kommuneIds = self::getKommuneIds($kommune);
                $tags = [
                    new Tag('arrangement_type', Tags::getArrangementTypeId('kommune')),
                    new Tag('kommune', $kommuneIds),
                    new Tag('sesong', $year)
                ];
                if (Filmer::harTagsFilmer($tags)) {
                    $years[] = $year;
                    break;
                }
            }
        }

        return array_reverse($years);
    }

    /**
     * Get all years where a kommune has films
     */
    public static function getKommuneYears($kommune, int $minYear = self::MIN_YEAR): array
    {
        $years = [];
        $currentYear = intval(date('Y'));

        for ($year = $minYear; $year <= $currentYear; $year++) {
            $films = self::getKommuneYearFilms($kommune, $year, $minYear);

            // Only surface the year if at least one film can be safely rendered
            foreach ($films as $film) {
                try {
                    // Ensure film belongs to the year and can be normalized
                    if (!self::isFilmYear($film, $year)) {
                        continue;
                    }
                    self::filmToArray($film);
                    $years[] = $year;
                    break;
                } catch (\Throwable $e) {
                    continue;
                }
            }
        }

        return array_reverse($years);
    }

    /**
     * Check if film belongs to a given year
     */
    private static function isFilmYear($film, int $year): bool
    {
        $filmYear = null;
        if (method_exists($film, 'getSesong')) {
            $filmYear = $film->getSesong();
        } elseif (method_exists($film, 'getSeason')) {
            $filmYear = $film->getSeason();
        }

        return $filmYear !== null && (int) $filmYear === (int) $year;
    }

    /**
     * Build kommune id list including historical ids
     */
    private static function getKommuneIds($kommune): array
    {
        $kommuneIds = [];

        if (method_exists($kommune, 'getTidligereIdList') && !empty($kommune->getTidligereIdList())) {
            $kommuneIds = array_merge(
                $kommuneIds,
                explode(',', $kommune->getTidligereIdList())
            );
        }

        $kommuneIds[] = $kommune->getId();

        return $kommuneIds;
    }

    private static function isRecent($film): bool
    {
        $year = self::getFilmYearValue($film);

        return $year === null ? false : ((int) $year >= self::MIN_YEAR);
    }

    // Expose for controllers where needed
    public static function isRecentPublic($film): bool
    {
        return self::isRecent($film);
    }

    /**
     * Convert film object to array for API response
     * Handles both Film and CloudflareFilm objects
     */
    public static function filmToArray($film): array
    {
        $isCloudflare = strpos(get_class($film), 'CloudflareFilm') !== false;

        // Prefer IDs that will resolve with full metadata
        $cfId = method_exists($film, 'getCloudflareId') ? $film->getCloudflareId() : null;
        $tvId = method_exists($film, 'getTvId') ? $film->getTvId() : null;
        $filmId = method_exists($film, 'getId') ? $film->getId() : null;
        // For CloudflareFilm, favor cfId first; for legacy Film, favor tvId
        if ($isCloudflare) {
            $routeId = $cfId ?: ($tvId ?: $filmId);
        } else {
            $routeId = $tvId ?: ($filmId ?: $cfId);
        }

        // If Cloudflare ID is missing but route id looks like one, reuse it for media fallbacks
        $effectiveCfId = $cfId;
        if (!$effectiveCfId && is_string($routeId) && preg_match('/^[A-Za-z0-9]{16,}$/', $routeId)) {
            $effectiveCfId = $routeId;
        }
        
        // Get thumbnail URL - prefer Cloudflare direct thumbnail when we have an id
        $thumbnailUrl = null;
        if ($isCloudflare) {
            // Build a stable Cloudflare thumbnail first to avoid malformed customer URLs
            $cfThumb = $effectiveCfId
                ? "https://videodelivery.net/{$effectiveCfId}/thumbnails/thumbnail.jpg?time=10s&height=360"
                : null;

            // Try library-provided URLs, then normalize
            $thumbnailUrl = $film->getThumbnailShare() ?? $film->getThumbnail() ?? $film->getImageUrl();

            if ($thumbnailUrl) {
                // Drop any duplicate ?time params
                $thumbnailUrl = preg_replace('/\?time=3s\?time=3s/', '?time=3s', $thumbnailUrl);
                // Force a later frame (10s) to reduce black thumbnails
                $thumbnailUrl = preg_replace('/time=\d+(\.\d+)?s/', 'time=10s', $thumbnailUrl);
                // Ensure height param for consistent sizing
                if (strpos($thumbnailUrl, 'height=') === false) {
                    $thumbnailUrl .= (str_contains($thumbnailUrl, '?') ? '&' : '?') . 'height=360';
                }
            }

            // If provided URL is missing or uses customer domain (risk of 404), prefer the stable thumb
            if (!$thumbnailUrl || str_contains($thumbnailUrl, 'cloudflarestream.com')) {
                $thumbnailUrl = $cfThumb ?? $thumbnailUrl;
            }
        } else {
            // Guard against missing miniature methods on legacy Film objects
            if (method_exists($film, 'getMiniaturebilde')) {
                $thumbnailUrl = $film->getMiniaturebilde();
            } elseif (method_exists($film, 'getMiniature')) {
                $thumbnailUrl = $film->getMiniature();
            } elseif (method_exists($film, 'getBilde')) {
                $thumbnailUrl = $film->getBilde();
            } elseif (method_exists($film, 'getImageUrl')) {
                $thumbnailUrl = $film->getImageUrl();
            } elseif (method_exists($film, 'getBildeUrl')) {
                $thumbnailUrl = $film->getBildeUrl();
            }
        }

        // Cloudflare fallback thumbnail if still missing but cf id exists
        if (!$thumbnailUrl && $effectiveCfId) {
            $thumbnailUrl = "https://videodelivery.net/{$effectiveCfId}/thumbnails/thumbnail.jpg?time=10s&height=360";
        }

        // Fallback placeholder if still missing (inline SVG data URI to avoid CSP/DNS issues)
        if (!$thumbnailUrl) {
            $thumbnailUrl = 'data:image/svg+xml,%3Csvg%20xmlns="http://www.w3.org/2000/svg"%20width="640"%20height="360"%20viewBox="0%200%20640%20360"%20fill="none"%3E%3Crect%20width="640"%20height="360"%20rx="8"%20fill="%23102130"/%3E%3Ctext%20x="50%25"%20y="50%25"%20dominant-baseline="middle"%20text-anchor="middle"%20fill="%23a855f7"%20font-family="Arial"%20font-size="32"%20font-weight="bold"%3EUKM%20TV%3C/text%3E%3C/svg%3E';
        }

        // Build a stable embed URL for Cloudflare to avoid legacy WP dependencies (twig.js)
        $cloudflareEmbed = $effectiveCfId
            ? "https://customer-554chiv4hi7wraol.cloudflarestream.com/{$effectiveCfId}/iframe"
            : null;

        // Build non-Cloudflare video url safely
        $videoUrl = null;
        if ($isCloudflare) {
            $videoUrl = $cloudflareEmbed;
        } else {
            if (method_exists($film, 'getUrl')) {
                $videoUrl = $film->getUrl();
            } elseif (method_exists($film, 'getTvUrl')) {
                $videoUrl = $film->getTvUrl();
            } elseif (method_exists($film, 'getPlayUrl')) {
                $videoUrl = $film->getPlayUrl();
            }
        }

        $year = self::getFilmYearValue($film);

        $kommuneName = null;
        $kommuneId = null;
        $fylkeName = null;
        $fylkeId = null;

        if (method_exists($film, 'getTags')) {
            try {
                $tags = $film->getTags();
                if ($tags && method_exists($tags, 'getKommune')) {
                    try {
                        $kommune = $tags->getKommune();
                        if ($kommune) {
                            $kommuneName = method_exists($kommune, 'getNavn') ? $kommune->getNavn() : null;
                            $kommuneId = method_exists($kommune, 'getId') ? $kommune->getId() : null;
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
                if ($tags && method_exists($tags, 'getFylke')) {
                    try {
                        $fylke = $tags->getFylke();
                        if ($fylke) {
                            $fylkeName = method_exists($fylke, 'getNavn') ? $fylke->getNavn() : null;
                            $fylkeId = method_exists($fylke, 'getId') ? $fylke->getId() : null;
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
                // Festival films can lack explicit geo tags, so derive kommune/fylke from innslag when available (prefer innslag data even if tags exist)
                if ($tags && method_exists($tags, 'getInnslag')) {
                    try {
                        $innslag = $tags->getInnslag();
                        if ($innslag && method_exists($innslag, 'getKommune')) {
                            $innslagKommune = $innslag->getKommune();
                            if ($innslagKommune) {
                                $kommuneName = method_exists($innslagKommune, 'getNavn') ? $innslagKommune->getNavn() : $kommuneName;
                                $kommuneId = method_exists($innslagKommune, 'getId') ? $innslagKommune->getId() : $kommuneId;
                                $innslagFylke = null;
                                if (method_exists($innslagKommune, 'getFylke')) {
                                    $innslagFylke = $innslagKommune->getFylke();
                                } elseif (method_exists($innslagKommune, 'getFylkeId')) {
                                    $fylkeIdValue = $innslagKommune->getFylkeId();
                                    if ($fylkeIdValue) {
                                        $innslagFylke = Fylker::getById((int) $fylkeIdValue);
                                    }
                                }
                                if ($innslagFylke) {
                                    $fylkeName = method_exists($innslagFylke, 'getNavn') ? $innslagFylke->getNavn() : $fylkeName;
                                    $fylkeId = method_exists($innslagFylke, 'getId') ? $innslagFylke->getId() : $fylkeId;
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
                // Fallback via arrangement if explicit geo tags are missing
                if ((!$kommuneName || !$kommuneId) && $tags && method_exists($tags, 'getArrangement')) {
                    try {
                        $arr = $tags->getArrangement();
                        if ($arr && method_exists($arr, 'getKommune')) {
                            $arrKommune = $arr->getKommune();
                            if ($arrKommune) {
                                $kommuneName = $kommuneName ?: (method_exists($arrKommune, 'getNavn') ? $arrKommune->getNavn() : null);
                                $kommuneId = $kommuneId ?: (method_exists($arrKommune, 'getId') ? $arrKommune->getId() : null);
                            }
                        }
                        if ((!$fylkeName || !$fylkeId) && $arr && method_exists($arr, 'getFylke')) {
                            $arrFylke = $arr->getFylke();
                            if ($arrFylke) {
                                $fylkeName = $fylkeName ?: (method_exists($arrFylke, 'getNavn') ? $arrFylke->getNavn() : null);
                                $fylkeId = $fylkeId ?: (method_exists($arrFylke, 'getId') ? $arrFylke->getId() : null);
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
                // If innslag_id is set on the film, derive geo from the innslag directly
                if (method_exists($film, 'getInnslagId')) {
                    try {
                        $innslagId = $film->getInnslagId();
                        if ($innslagId) {
                            $innslag = new Innslag($innslagId);
                            if (method_exists($innslag, 'getKommune')) {
                                $innslagKommune = $innslag->getKommune();
                                if ($innslagKommune) {
                                    $kommuneName = method_exists($innslagKommune, 'getNavn') ? $innslagKommune->getNavn() : $kommuneName;
                                    $kommuneId = method_exists($innslagKommune, 'getId') ? $innslagKommune->getId() : $kommuneId;
                                    $innslagFylke = null;
                                    if (method_exists($innslagKommune, 'getFylke')) {
                                        $innslagFylke = $innslagKommune->getFylke();
                                    } elseif (method_exists($innslagKommune, 'getFylkeId')) {
                                        $fylkeIdValue = $innslagKommune->getFylkeId();
                                        if ($fylkeIdValue) {
                                            $innslagFylke = Fylker::getById((int) $fylkeIdValue);
                                        }
                                    }
                                    if ($innslagFylke) {
                                        $fylkeName = method_exists($innslagFylke, 'getNavn') ? $innslagFylke->getNavn() : $fylkeName;
                                        $fylkeId = method_exists($innslagFylke, 'getId') ? $innslagFylke->getId() : $fylkeId;
                                    }
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
                // As a final fallback, use arrangement id on the film if available
                if ((!$kommuneName || !$fylkeName) && method_exists($film, 'getArrangementId')) {
                    try {
                        $arrId = $film->getArrangementId();
                        if ($arrId) {
                            $arr = new Arrangement($arrId);
                            if (!$kommuneName && method_exists($arr, 'getKommune')) {
                                $arrKommune = $arr->getKommune();
                                if ($arrKommune) {
                                    $kommuneName = method_exists($arrKommune, 'getNavn') ? $arrKommune->getNavn() : null;
                                    $kommuneId = method_exists($arrKommune, 'getId') ? $arrKommune->getId() : null;
                                }
                            }
                            if (!$fylkeName && method_exists($arr, 'getFylke')) {
                                $arrFylke = $arr->getFylke();
                                if ($arrFylke) {
                                    $fylkeName = method_exists($arrFylke, 'getNavn') ? $arrFylke->getNavn() : null;
                                    $fylkeId = method_exists($arrFylke, 'getId') ? $arrFylke->getId() : null;
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            } catch (\Throwable $e) {
                // ignore tag errors
            }
        }

        // Fallback using kommune id on film (if present) to derive correct kommune/fylke
        if ((!$kommuneName || !$fylkeName) && method_exists($film, 'getKommuneId')) {
            try {
                $kommuneIdValue = $film->getKommuneId();
                if ($kommuneIdValue) {
                    $kommuneObj = Kommune::getById((int) $kommuneIdValue);
                    if ($kommuneObj) {
                        $kommuneName = $kommuneName ?: (method_exists($kommuneObj, 'getNavn') ? $kommuneObj->getNavn() : null);
                        $kommuneId = $kommuneId ?: (method_exists($kommuneObj, 'getId') ? $kommuneObj->getId() : null);
                        $kommuneFylke = null;
                        if (method_exists($kommuneObj, 'getFylke')) {
                            $kommuneFylke = $kommuneObj->getFylke();
                        } elseif (method_exists($kommuneObj, 'getFylkeId')) {
                            $fId = $kommuneObj->getFylkeId();
                            if ($fId) {
                                $kommuneFylke = Fylker::getById((int) $fId);
                            }
                        }
                        if ($kommuneFylke) {
                            $fylkeName = $fylkeName ?: (method_exists($kommuneFylke, 'getNavn') ? $kommuneFylke->getNavn() : null);
                            $fylkeId = $fylkeId ?: (method_exists($kommuneFylke, 'getId') ? $kommuneFylke->getId() : null);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Fallback using Cloudflare-specific fields when tags are missing
        if ((!$kommuneName || !$fylkeName) && method_exists($film, 'getFylkeId')) {
            try {
                $fylkeIdFromFilm = $film->getFylkeId();
                if ($fylkeIdFromFilm) {
                    $fylkeObj = Fylker::getById((int) $fylkeIdFromFilm);
                    if ($fylkeObj) {
                        $fylkeName = $fylkeName ?: (method_exists($fylkeObj, 'getNavn') ? $fylkeObj->getNavn() : null);
                        $fylkeId = $fylkeId ?: (method_exists($fylkeObj, 'getId') ? $fylkeObj->getId() : null);
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return [
            'id' => $routeId,
            'tv_id' => $tvId,
            'cf_id' => $cfId,
            'title' => $film->getTitle() ?? $film->getNavn() ?? '',
            'description' => $film->getDescription() ?? $film->getBeskrivelse() ?? '',
            'video_url' => $videoUrl,
            'thumbnail_url' => $thumbnailUrl,
            'duration' => method_exists($film, 'getVarighet') ? $film->getVarighet() : 0,
            'year' => $year ?? '',
            'kommune' => $kommuneName,
            'kommune_id' => $kommuneId,
            'fylke' => $fylkeName,
            'fylke_id' => $fylkeId,
        ];
    }

    /**
     * Safely read film year from various method names
     */
    private static function getFilmYearValue($film): ?int
    {
        if (method_exists($film, 'getSesong')) {
            return $film->getSesong();
        }
        if (method_exists($film, 'getSeason')) {
            return $film->getSeason();
        }
        if (method_exists($film, 'getYear')) {
            return $film->getYear();
        }
        if (method_exists($film, 'getSesongAttribute')) {
            return $film->getSesongAttribute();
        }

        return null;
    }
}
