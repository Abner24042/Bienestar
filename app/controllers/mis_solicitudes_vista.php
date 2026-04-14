<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false]);
    exit;
}

$user  = $authController->getCurrentUser();
$input = json_decode(file_get_contents('php://input'), true);
$id    = (int)($input['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID requerido']);
    exit;
}

$model = new Cita();
$ok    = $model->marcarSolicitudVista($id, $user['correo']);
echo json_encode(['success' => (bool)$ok]);
