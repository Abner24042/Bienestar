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

$user = $authController->getCurrentUser();
if ($user['rol'] !== 'nutriologo') {
    echo json_encode(['success' => false, 'message' => 'Solo nutriólogos']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$id   = $body['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID requerido']);
    exit;
}

try {
    $model  = new Receta();
    $result = $model->aprobar($id);
    echo json_encode(['success' => (bool)$result]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
