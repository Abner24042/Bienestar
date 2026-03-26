<?php

define('GNEWS_API_KEY', '9f4f1b819466afce5777b6648afb438f');
define('CRON_SECRET', 'bieniestar_cron_2026_x7k9m');

// Artículos a traer por categoría (máx 10 con plan gratuito)
define('NEWS_MAX_PER_CATEGORY', 5);

// Días antes de despublicar artículos auto-generados
define('NEWS_EXPIRE_DAYS', 3);

// Búsquedas por categoría — términos cortos para mayor cobertura
define('NEWS_SEARCHES', [
    'alimentacion' => 'alimentación salud',
    'ejercicio' => 'ejercicio deporte',
    'salud-mental' => 'salud mental',
    'general' => 'bienestar salud',
]);
