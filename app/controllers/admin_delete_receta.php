<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Receta.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (empty($data['id'])) {
        throw new Exception('ID requerido');
    }

    $model = new Receta();
    $action = $data['action'] ?? 'delete';

    if ($action === 'toggle') {
        $activo = $data['activo'] ?? 0;
        $model->toggleActive($data['id'], $activo);
        echo json_encode(['success' => true, 'message' => $activo ? 'Receta activada' : 'Receta desactivada']);
    } else {
        $model->delete($data['id']);
        echo json_encode(['success' => true, 'message' => 'Receta eliminada']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
