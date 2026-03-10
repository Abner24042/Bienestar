<?php
/**
 * CRON: Auto-fetch de noticias desde GNews API
 *
 * Uso: GET /cron/news?secret=TU_CRON_SECRET
 *
 * Qué hace cada vez que se ejecuta:
 *   1. Despublica artículos auto-generados de más de NEWS_EXPIRE_DAYS días
 *   2. Busca artículos nuevos en GNews para 4 categorías
 *   3. Inserta los que no existan aún en la BD
 *
 * Configurar en IONOS → "Tareas programadas" → frecuencia diaria:
 *   https://tudominio.com/cron/news?secret=TU_CRON_SECRET
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/news_config.php';
require_once __DIR__ . '/../models/Noticia.php';

header('Content-Type: text/plain; charset=utf-8');

// ── Seguridad ─────────────────────────────────────────────────────
$secret = $_GET['secret'] ?? '';
if (!defined('CRON_SECRET') || $secret !== CRON_SECRET) {
    http_response_code(403);
    echo "Forbidden.\n";
    exit;
}

if (!defined('GNEWS_API_KEY') || GNEWS_API_KEY === 'TU_API_KEY_AQUI') {
    echo "ERROR: Configura GNEWS_API_KEY en app/config/news_config.php\n";
    echo "Regístrate gratis en https://gnews.io para obtener tu API key.\n";
    exit;
}

$model = new Noticia();

// ── 1. Asegurar columnas extra ─────────────────────────────────────
$model->ensureColumns();
echo "[1/3] Columnas verificadas.\n";

// ── 2. Despublicar artículos vencidos ─────────────────────────────
$expired = $model->unpublishOldAuto(NEWS_EXPIRE_DAYS);
echo "[2/3] Artículos auto-generados eliminados (>" . NEWS_EXPIRE_DAYS . " días): {$expired}\n";

// ── 3. Traer noticias nuevas ───────────────────────────────────────
$inserted = 0;
$skipped  = 0;
$errors   = 0;

foreach (NEWS_SEARCHES as $categoria => $query) {
    $url = 'https://gnews.io/api/v4/search?'
         . http_build_query([
             'q'      => $query,
             'lang'   => 'es',
             'max'    => NEWS_MAX_PER_CATEGORY,
             'sortby' => 'publishedAt',
             'token'  => GNEWS_API_KEY,
         ]);

    sleep(1); // pequeña pausa entre llamadas para no saturar la API
    $ctx      = stream_context_create(['http' => ['timeout' => 15]]);
    $response = @file_get_contents($url, false, $ctx);

    if ($response === false) {
        echo "  [{$categoria}] ERROR al contactar GNews API.\n";
        $errors++;
        continue;
    }

    $data = json_decode($response, true);

    if (isset($data['errors'])) {
        echo "  [{$categoria}] GNews error: " . implode(', ', $data['errors']) . "\n";
        $errors++;
        continue;
    }

    $articles = $data['articles'] ?? [];
    echo "  [{$categoria}] " . count($articles) . " artículos recibidos.\n";

    foreach ($articles as $a) {
        $artUrl = $a['url'] ?? '';
        if (!$artUrl || $model->existsByUrl($artUrl)) {
            $skipped++;
            continue;
        }

        // GNews recorta el content con "... [N chars]" — se elimina ese sufijo
        $contenido = preg_replace('/\s*\[\d+\s*chars\]$/u', '', $a['content'] ?? $a['description'] ?? '');
        $resumen   = preg_replace('/\s*\[\d+\s*chars\]$/u', '', $a['description'] ?? '');
        $titulo    = trim($a['title']  ?? '');

        if (!$titulo) { $skipped++; continue; }

        // Fecha de publicación desde la API
        $fecha = isset($a['publishedAt'])
            ? date('Y-m-d H:i:s', strtotime($a['publishedAt']))
            : date('Y-m-d H:i:s');

        $id = $model->createAuto([
            'titulo'     => $titulo,
            'contenido'  => $contenido,
            'resumen'    => $resumen,
            'imagen'     => $a['image']          ?? null,
            'categoria'  => $categoria,
            'autor'      => $a['source']['name'] ?? null,
            'url_fuente' => $artUrl,
            'fecha'      => $fecha,
        ]);

        if ($id) {
            $inserted++;
            echo "    + [" . substr($titulo, 0, 60) . "]\n";
        } else {
            $errors++;
        }
    }
}

echo "\n[3/3] Resumen: +{$inserted} insertados, {$skipped} omitidos, {$errors} errores.\n";
echo "OK\n";
