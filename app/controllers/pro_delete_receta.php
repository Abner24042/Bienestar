<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Receta.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isProfessional()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

try {
    $user = $authController->getCurrentUser();
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (empty($data['id'])) {
        throw new Exception('ID requerido');
    }

    $model = new Receta();
    $existing = $model->findById($data['id']);

    if (!$existing) {
        throw new Exception('Receta no encontrada');
    }

    $esMia      = $existing['creado_por'] === $user['correo'];
    $esPending  = !empty($existing['auto_generada']) && empty($existing['aprobada']);
    $esNutri    = $user['rol'] === 'nutriologo';

    if (!$esMia && !($esPending && $esNutri)) {
        throw new Exception('No tienes permiso para eliminar esta receta');
    }

    $model->delete($data['id']);
    echo json_encode(['success' => true, 'message' => 'Receta eliminada']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
