<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Total usuarios
    $stmt = $db->query("SELECT COUNT(*) FROM usuarios");
    $totalUsuarios = (int)$stmt->fetchColumn();

    // Nuevos este mes
    $stmt = $db->query("SELECT COUNT(*) FROM usuarios WHERE MONTH(fecha) = MONTH(NOW()) AND YEAR(fecha) = YEAR(NOW())");
    $nuevosEsteMes = (int)$stmt->fetchColumn();

    // Total citas
    $stmt = $db->query("SELECT COUNT(*) FROM citas_bieniestar");
    $totalCitas = (int)$stmt->fetchColumn();

    // Citas futuras (desde hoy)
    $stmt = $db->query("SELECT COUNT(*) FROM citas_bieniestar WHERE fecha >= CURDATE()");
    $citasFuturas = (int)$stmt->fetchColumn();

    // Citas esta semana
    $stmt = $db->query("SELECT COUNT(*) FROM citas_bieniestar WHERE YEARWEEK(fecha, 1) = YEARWEEK(NOW(), 1)");
    $citasEstaSemana = (int)$stmt->fetchColumn();

    // Ejercicios activos
    $stmt = $db->query("SELECT COUNT(*) FROM ejercicios WHERE activo = 1");
    $ejerciciosActivos = (int)$stmt->fetchColumn();

    // Total recetas
    $stmt = $db->query("SELECT COUNT(*) FROM recetas");
    $totalRecetas = (int)$stmt->fetchColumn();

    // Noticias publicadas
    $stmt = $db->query("SELECT COUNT(*) FROM noticias WHERE publicado = 1");
    $noticiasPublicadas = (int)$stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'stats' => [
            'usuarios'           => $totalUsuarios,
            'usuarios_nuevos_mes' => $nuevosEsteMes,
            'citas'              => $totalCitas,
            'citas_futuras'      => $citasFuturas,
            'citas_semana'       => $citasEstaSemana,
            'ejercicios'         => $ejerciciosActivos,
            'recetas'            => $totalRecetas,
            'noticias'           => $noticiasPublicadas,
        ]
    ]);

} catch (Exception $e) {
    error_log('Error en admin_get_stats: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
