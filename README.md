# SF2 Ultra

Registro de pleitos (matches) de Street Fighter II con rankings por temporada, reporte del dia, comparativas VS y estadisticas por jugador. Laravel 13 / PHP 8.3 / MySQL.

## Instalacion

```bash
composer install
cp .env.example .env        # ajusta DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD
php artisan key:generate
php artisan migrate --seed  # crea las tablas e importa el historial (7 jugadores, 106 matches)
php artisan serve
```

Las migraciones son idempotentes: si la base `sf2` ya tiene las tablas `players` y `matches`, se respetan y solo se agrega el indice de `fecha`. El seeder no hace nada si ya hay datos.

## Estructura

| Capa | Ubicacion |
| --- | --- |
| Rutas | `routes/web.php` |
| Controladores | `app/Http/Controllers` (`DashboardController`, `MatchController`, `PlayerController`, `Api/*`) |
| Validacion | `app/Http/Requests` |
| Reglas de negocio | `app/Services/MatchService.php` (altas/ediciones con transaccion y totales), `app/Services/StatsService.php` (rankings y estadisticas) |
| Modelos | `app/Models/Player.php`, `app/Models/GameMatch.php` (tabla `matches`) |
| Vistas | `resources/views` (layout + modales en `partials/modals`) |
| Frontend | `public/js/dashboard.js`, `public/css/app.css`, Bootstrap 5.0.2 local en `public/vendor` |

## Pruebas

Usan una base MySQL aparte (`sf2_testing`, ver `phpunit.xml`) para no tocar los datos reales:

```bash
php artisan test
```
