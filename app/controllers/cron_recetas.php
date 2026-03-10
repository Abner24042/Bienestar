<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/edamam_config.php';
require_once __DIR__ . '/../models/Receta.php';

// Autenticación por secret
$secret = $_GET['secret'] ?? '';
if ($secret !== EDAMAM_CRON_SECRET) {
    http_response_code(403);
    exit('Acceso denegado');
}

header('Content-Type: text/plain; charset=UTF-8');
set_time_limit(300);

// Traducir texto en→es usando MyMemory API (gratuita, sin clave)
function traducir($text) {
    if (empty(trim($text))) return $text;
    $text = mb_substr($text, 0, 490); // límite MyMemory: 500 chars
    $url  = 'https://api.mymemory.translated.net/get?q=' . urlencode($text) . '&langpair=en|es';
    $ctx  = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
    $raw  = @file_get_contents($url, false, $ctx);
    if ($raw === false) return $text;
    $json = json_decode($raw, true);
    $t    = $json['responseData']['translatedText'] ?? null;
    if (!$t || stripos($t, 'QUERY LENGTH') !== false) return $text;
    return html_entity_decode($t, ENT_QUOTES, 'UTF-8');
}

// Traducir lista de ingredientes dividiendo en bloques de 490 chars
function traducirIngredientes($lineas) {
    if (empty($lineas)) return '';
    $bloques  = [];
    $actual   = '';
    foreach ($lineas as $linea) {
        $prueba = $actual ? $actual . "\n" . $linea : $linea;
        if (mb_strlen($prueba) > 490) {
            if ($actual) $bloques[] = $actual;
            $actual = $linea;
        } else {
            $actual = $prueba;
        }
    }
    if ($actual) $bloques[] = $actual;

    $traducidos = [];
    foreach ($bloques as $bloque) {
        $traducidos[] = traducir($bloque);
        usleep(300000); // 0.3s entre llamadas
    }
    return implode("\n", $traducidos);
}

$model = new Receta();
$model->ensureColumns();

$totalInserted = 0;
$totalSkipped  = 0;
$errors        = [];

// Campos requeridos en la respuesta
$fields = implode('&field=', [
    'label', 'image', 'url', 'source', 'yield',
    'calories', 'totalTime', 'cuisineType', 'mealType',
    'dietLabels', 'healthLabels', 'ingredientLines', 'totalNutrients',
]);

foreach (EDAMAM_CATEGORIES as $categoria => $query) {
    echo "--- Buscando: $query (categoria: $categoria) ---\n";

    $url = EDAMAM_API_URL
        . '?type=public'
        . '&q=' . urlencode($query)
        . '&app_id=' . EDAMAM_APP_ID
        . '&app_key=' . EDAMAM_APP_KEY
        . '&random=true'
        . '&field=' . $fields;

    $ctx = stream_context_create([
        'http' => [
            'timeout'        => 15,
            'ignore_errors'  => true,
            'header'         => "Accept: application/json\r\nEdamam-Account-User: " . EDAMAM_APP_ID . "\r\n",
        ],
    ]);

    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        $errors[] = "Error de conexión para: $query";
        echo "  ERROR: no se pudo conectar\n";
        sleep(1);
        continue;
    }

    $json = json_decode($raw, true);
    if (!isset($json['hits'])) {
        $errors[] = "Respuesta inválida para: $query — " . substr($raw, 0, 200);
        echo "  ERROR: respuesta inválida\n";
        sleep(1);
        continue;
    }

    $hits = array_slice($json['hits'], 0, EDAMAM_MAX_PER_CATEGORY);
    echo "  Encontradas: " . count($hits) . " recetas\n";

    foreach ($hits as $hit) {
        $r = $hit['recipe'] ?? null;
        if (!$r || empty($r['label']) || empty($r['url'])) continue;

        $nutrientes = $r['totalNutrients'] ?? [];
        $yield      = max(1, (int)($r['yield'] ?? 1));
        $calorias   = isset($r['calories']) ? round($r['calories'] / $yield, 1) : null;

        // Descargar imagen localmente para evitar bloqueo de hotlinks
        $imagenLocal = null;
        if (!empty($r['image'])) {
            $uploadDir = UPLOAD_PATH . '/recetas/';
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
            $filename  = 'edamam_' . md5($r['url']) . '.jpg';
            $localPath = $uploadDir . $filename;
            if (!file_exists($localPath)) {
                $imgData = @file_get_contents($r['image'], false, stream_context_create([
                    'http' => ['timeout' => 8, 'ignore_errors' => true],
                ]));
                if ($imgData !== false && strlen($imgData) > 1000) {
                    file_put_contents($localPath, $imgData);
                }
            }
            if (file_exists($localPath)) {
                $imagenLocal = ASSETS_URL . '/uploads/recetas/' . $filename;
            }
        }

        $tituloEs       = traducir($r['label']);
        $ingredientesEs = traducirIngredientes($r['ingredientLines'] ?? []);
        echo "  Traduciendo: {$r['label']} → $tituloEs\n";

        $data = [
            'titulo'          => $tituloEs,
            'descripcion'     => 'Receta de ' . ($r['source'] ?? 'Edamam') . '. ' . implode(', ', array_slice($r['healthLabels'] ?? [], 0, 3)),
            'ingredientes'    => $ingredientesEs,
            'imagen'          => $imagenLocal,
            'categoria'       => $categoria,
            'tiempo_preparacion' => !empty($r['totalTime']) ? (int)$r['totalTime'] : null,
            'porciones'       => $yield,
            'calorias'        => $calorias,
            'url_fuente'      => $r['url'],
            'fuente'          => $r['source'] ?? null,
            'tipo_cocina'     => !empty($r['cuisineType']) ? ucfirst($r['cuisineType'][0]) : null,
            'etiquetas_dieta' => !empty($r['dietLabels'])   ? json_encode($r['dietLabels'])   : null,
            'etiquetas_salud' => !empty($r['healthLabels']) ? json_encode(array_slice($r['healthLabels'], 0, 6)) : null,
            'proteinas'       => isset($nutrientes['PROCNT']['quantity']) ? round($nutrientes['PROCNT']['quantity'] / $yield, 1) : null,
            'carbohidratos'   => isset($nutrientes['CHOCDF']['quantity']) ? round($nutrientes['CHOCDF']['quantity'] / $yield, 1) : null,
            'grasas'          => isset($nutrientes['FAT']['quantity'])    ? round($nutrientes['FAT']['quantity'] / $yield, 1)    : null,
            'fibra'           => isset($nutrientes['FIBTG']['quantity'])  ? round($nutrientes['FIBTG']['quantity'] / $yield, 1)  : null,
        ];

        $result = $model->saveFromEdamam($data);
        if (is_string($result) && str_starts_with($result, 'ERROR:')) {
            $errors[] = $result;
            echo "  !! ERROR DB: " . substr($result, 6) . "\n";
        } elseif ($result) {
            $totalInserted++;
            echo "  + Guardada: {$data['titulo']}\n";
        } else {
            $totalSkipped++;
            echo "  ~ Duplicada: {$data['titulo']}\n";
        }
    }

    sleep(1); // pausa entre llamadas a la API
}

// Eliminar recetas no aprobadas con más de 48 horas
$deletedUnapproved = $model->deleteOldUnapproved(48);
echo "\n--- Recetas sin aprobar eliminadas: $deletedUnapproved ---\n";

// Limpiar recetas aprobadas muy antiguas
$deleted = $model->deleteOldAuto(EDAMAM_EXPIRE_DAYS);
echo "--- Recetas antiguas eliminadas: $deleted ---\n";

echo "\n=== RESUMEN ===\n";
echo "Insertadas: $totalInserted\n";
echo "Duplicadas: $totalSkipped\n";
if (!empty($errors)) {
    echo "Errores:\n";
    foreach ($errors as $e) echo "  - $e\n";
}
echo "Fin: " . date('Y-m-d H:i:s') . "\n";
