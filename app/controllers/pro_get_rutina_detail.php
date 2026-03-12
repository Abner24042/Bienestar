<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Rutina.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$auth = new AuthController();
if (!$auth->isAuthenticated() || !isProfessional()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID requerido']);
    exit;
}

$user = $auth->getCurrentUser();
$model = new Rutina();
$rutina = $model->getDetail($id);
if (!$rutina || $rutina['coach_correo'] !== $user['correo']) {
    echo json_encode(['success' => false, 'message' => 'No encontrado']);
    exit;
}

echo json_encode(['success' => true, 'rutina' => $rutina]);
