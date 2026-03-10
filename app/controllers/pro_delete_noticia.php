<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Noticia.php';
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

    $model = new Noticia();
    $existing = $model->findById($data['id']);

    if (!$existing || $existing['creado_por'] !== $user['correo']) {
        throw new Exception('No tienes permiso para eliminar esta publicación');
    }

    $model->delete($data['id']);
    echo json_encode(['success' => true, 'message' => 'Publicación eliminada']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
