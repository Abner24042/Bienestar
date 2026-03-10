<?php
/**
 * Configuración de auto-fetch de noticias
 *
 * 1. Regístrate gratis en https://gnews.io  (100 peticiones/día gratis)
 * 2. Copia tu API key y pégala en GNEWS_API_KEY
 * 3. Cambia CRON_SECRET por cualquier texto secreto largo
 * 4. En IONOS: Tareas programadas → URL diaria →
 *    https://tudominio.com/cron/news?secret=TU_CRON_SECRET
 */

define('GNEWS_API_KEY',  '9f4f1b819466afce5777b6648afb438f');
define('CRON_SECRET',    'bieniestar_cron_2026_x7k9m');

// Artículos a traer por categoría (máx 10 con plan gratuito)
define('NEWS_MAX_PER_CATEGORY', 5);

// Días antes de despublicar artículos auto-generados
define('NEWS_EXPIRE_DAYS', 3);

// Búsquedas por categoría — términos cortos para mayor cobertura
define('NEWS_SEARCHES', [
    'alimentacion' => 'alimentación salud',
    'ejercicio'    => 'ejercicio deporte',
    'salud-mental' => 'salud mental',
    'general'      => 'bienestar salud',
]);
