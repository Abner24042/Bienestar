<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Plan.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isProfessional()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

try {
    $data      = json_decode(file_get_contents('php://input'), true);
    $usuarioId = $data['usuario_id'] ?? null;
    $recetaId  = $data['receta_id']  ?? null;
    $notas     = trim($data['notas'] ?? '') ?: null;

    if (!$usuarioId || !$recetaId) throw new Exception('Datos incompletos');

    $profesional = $authController->getCurrentUser();
    $model = new Plan();
    $ok = $model->asignarReceta($usuarioId, $recetaId, $profesional['correo'], $notas);

    echo json_encode($ok
        ? ['success' => true,  'message' => 'Receta asignada al plan']
        : ['success' => false, 'message' => 'Error al asignar']
    );
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
