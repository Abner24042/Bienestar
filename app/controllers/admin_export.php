<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/AuthController.php';

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isAdmin()) {
    http_response_code(403);
    exit('Sin permisos');
}

$type = $_GET['type'] ?? '';
$allowed = ['usuarios', 'citas', 'ejercicios', 'recetas', 'noticias'];

if (!in_array($type, $allowed)) {
    http_response_code(400);
    exit('Tipo inválido');
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $date = date('Y-m-d');

    if ($type === 'usuarios') {
        $stmt = $db->query("SELECT id, nombre, correo, rol, fecha FROM usuarios ORDER BY fecha DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $filename = "usuarios_{$date}.csv";
        $headers = ['ID', 'Nombre', 'Correo', 'Rol', 'Fecha Registro'];
    } elseif ($type === 'citas') {
        $stmt = $db->query("SELECT id, fecha, hora, titulo, descripcion, correo, profesional_correo FROM citas_bieniestar ORDER BY fecha DESC, hora DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $filename = "citas_{$date}.csv";
        $headers = ['ID', 'Fecha', 'Hora', 'Título', 'Descripción', 'Usuario', 'Profesional'];
    } elseif ($type === 'ejercicios') {
        $stmt = $db->query("SELECT id, titulo, tipo, nivel, duracion, calorias_quemadas, activo, creado_por, created_at FROM ejercicios ORDER BY created_at DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $filename = "ejercicios_{$date}.csv";
        $headers = ['ID', 'Título', 'Tipo', 'Nivel', 'Duración (min)', 'Calorías', 'Activo', 'Creado Por', 'Fecha'];
    } elseif ($type === 'recetas') {
        $stmt = $db->query("SELECT id, titulo, categoria, tiempo_preparacion, porciones, calorias, activo, creado_por, created_at FROM recetas ORDER BY created_at DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $filename = "recetas_{$date}.csv";
        $headers = ['ID', 'Título', 'Categoría', 'Tiempo (min)', 'Porciones', 'Calorías', 'Activo', 'Creado Por', 'Fecha'];
    } else {
        $stmt = $db->query("SELECT id, titulo, categoria, autor, publicado, destacado, fecha_publicacion, url_fuente FROM noticias ORDER BY fecha_publicacion DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $filename = "noticias_{$date}.csv";
        $headers = ['ID', 'Título', 'Categoría', 'Autor', 'Publicado', 'Destacada', 'Fecha Publicación', 'URL Fuente'];
    }

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');

    // BOM UTF-8 para que Excel abra correctamente
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');
    fputcsv($out, $headers, ';');
    foreach ($rows as $row) {
        fputcsv($out, array_values($row), ';');
    }
    fclose($out);

} catch (Exception $e) {
    error_log('Error en admin_export: ' . $e->getMessage());
    http_response_code(500);
    exit('Error al exportar datos');
}
