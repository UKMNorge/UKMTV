# UKM TV - Modern Laravel Frontend

## Oversikt

Dette er en modernisert versjon av UKM TV bygget med Laravel 12, Vue 3, Inertia.js, og Tailwind CSS. Systemet bruker UKMNorges biblioteker direkte for å hente filmdata, slik som det originale systemet.

## Arkitektur

### Backend
- **Framework**: Laravel 12
- **Datasource**: UKMNorge Filmer-bibliotek (fra php.ini includes)
- **API Routes**: RESTful API endpoints for filmdata
- **Controllers**: 
  - `HomeController` - Forsiden med siste innslag
  - `FilmController` - Filmdetaljer og søk
  - `FestivalController` - Festival (land) arrangementer  
  - `FylkeController` - Fylke (county) browsing
  - `SearchController` - Søkefunksjonalitet

### Frontend
- **Framework**: Vue 3
- **Ruting**: Inertia.js (server-side routing med client-side rendering)
- **Styling**: Tailwind CSS
- **Design**: Inspirert av ukm.no med lilla, blå og grønn fargeskjema

## Filstruktur

```
/var/www/html/sites/newukmtv/
├── app/
│   ├── Http/
│   │   ├── Controllers/ (HomeController, FilmController, etc.)
│   │   └── Middleware/HandleInertiaRequests.php
│   └── Services/
│       └── FilmService.php (wrapper for UKMNorge Filmer library)
├── resources/
│   ├── js/
│   │   ├── Pages/
│   │   │   ├── Home.vue
│   │   │   ├── Film/Show.vue
│   │   │   ├── Festival/
│   │   │   ├── Fylke/
│   │   │   └── Search/
│   │   ├── Components/FilmCard.vue
│   │   └── app.js
│   ├── css/
│   │   └── app.css (Tailwind)
│   └── views/
│       └── app.blade.php (Inertia root template)
├── routes/
│   └── web.php (all routes defined here)
└── bootstrap/
    └── ukm.php (UKMNorge library initialization)
```

## Ruter

### Web Routes
- `GET /` - Forsiden (Home)
- `GET /film/{id}` - Filmdetaljer
- `GET /festival` - Festival år
- `GET /festival/{year}` - Filmer fra festival år
- `GET /fylke` - Alle fylker
- `GET /fylke/{fylkeKey}` - Fylke detaljer
- `GET /fylke/{fylkeKey}/{year}` - Filmer fra fylke år
- `GET /search` - Søkeside
- `GET /search/results` - Søkeresultater

### API Routes (under `/api`)
- `GET /films/latest` - Siste 50 innslag
- `GET /festival/years` - Alle festival år
- `GET /festival/{year}/films` - Filmer fra festival år
- `GET /fylke` - Alle fylker
- `GET /fylke/{fylkeKey}/years` - År med innslag for fylke
- `GET /fylke/{fylkeKey}/{year}/films` - Filmer for fylke år
- `GET /search?q=query` - Søk på query

## Vue Komponenter

### Pages
- **Home.vue** - Forsiden med siste innslag, navigasjonsknapper til festival, fylke, søk
- **Festival/Years.vue** - Grid av år med innslag
- **Festival/Show.vue** - Filmer fra spesifikt festival år
- **Fylke/Index.vue** - Grid av alle fylker
- **Fylke/Show.vue** - Fylke detaljer med år og kommuner
- **Fylke/Year.vue** - Filmer fra fylke + år
- **Film/Show.vue** - Filmdetaljer med video player og info
- **Search/Index.vue** - Søkeinput
- **Search/Results.vue** - Søkeresultater

### Components
- **FilmCard.vue** - Gjennbrukbar filmkort (thumbnail, tittel, år, views, varighet)

## UKMNorge Filmer Service

`app/Services/FilmService.php` er en wrapper omkring UKMNorge Filmer-biblioteket som tilbyr:

```php
// Filmhenting
FilmService::getLatest($limit);
FilmService::getById($id);
FilmService::search($searchString);

// Festival
FilmService::getFestivalYears();
FilmService::getFestivalYearFilms($year);

// Fylke
FilmService::getFylker();
FilmService::getFylkeByLink($fylkeKey);
FilmService::getFylkeYears($fylke);
FilmService::getFylkeYearFilms($fylke, $year);
FilmService::getFylkeKommunerWithFilms($fylke);

// Utility
FilmService::filmToArray($film); // konverter Film-objekt til array
```

## Oppstart

### 1. Forutsetninger
- PHP 8.3+
- MySQL/MariaDB
- Node.js 18+
- Composer

### 2. Installasjon
```bash
cd /var/www/html/sites/newukmtv

# Installer PHP dependencies
composer install

# Installer npm packages
npm install

# Build frontend assets
npm run build

# For development med watch mode:
npm run dev
```

### 3. Konfigurering

`.env` fil er allerede konfigurert for:
- MySQL database: `ukmtv_new`
- User: `root`
- Password: `devonly`
- Session driver: `file`

### 4. Start server
```bash
php artisan serve --port=8001
```

Besøk `http://127.0.0.1:8001`

## Design Inspirasjon

Designet er inspirert av ukm.no med:
- **Fargepalett**: 
  - Lilla (#8b5cf6) for primary actions
  - Blå (#3b82f6) for secondary
  - Grønn (#10b981) for tertiary/search
- **Dark mode**: Dunkle slag (slate-900, slate-800) for bakgrunn
- **Modern gradients**: Gradient bakgrunner for visual interest
- **Responsive grid**: 1-2-4 kolonner basert på skjermstørrelse

## Utvidelse

### Legge til nye sider
1. Opprett Vue fil i `resources/js/Pages/`
2. Opprett controller method som bruker `Inertia::render()`
3. Legg til rute i `routes/web.php`

Eksempel:
```php
// Route
Route::get('/ny-side', [NewController::class, 'show']);

// Controller
public function show() {
    return Inertia::render('NewPage', ['data' => $data]);
}

// Vue page
<script setup>
defineProps({
    data: Object,
});
</script>
<template>...</template>
```

### Legge til API endpoints
Alle nye endpoints legges under `/api` prefixen med JSON responses.

## Troubleshooting

### Session errors
Sjekk at `SESSION_DRIVER=file` i `.env`

### UKMNorge library errors
Sjekk at `/etc/php-includes/UKM/` finnes og er tilgjengelig
Sjekk at `bootstrap/ukm.php` initialiseres i `bootstrap/app.php`

### Build errors
```bash
npm install @vitejs/plugin-vue
npm install @inertiajs/vue3
npm run build
```

## Neste steg

- [ ] Testing av alle features med ekte data
- [ ] Performance optimalisering
- [ ] Accessibility audit
- [ ] Mobile testing
- [ ] Caching av API responses
- [ ] Error handling og logging
- [ ] Admin panel for filmhandstering (hvis ønskelig)

## Kontakt & Support

For spørsmål om systemet, kontakt UKM IT-team.
