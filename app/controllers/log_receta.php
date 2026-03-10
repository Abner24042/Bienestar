<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/ActividadRegistro.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$data     = json_decode(file_get_contents('php://input'), true);
$receta_id = (int)($data['receta_id'] ?? 0);
$titulo    = trim($data['titulo'] ?? '');
$calorias  = (int)($data['calorias'] ?? 0);

if (!$receta_id || $calorias < 0) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$user  = $authController->getCurrentUser();
$model = new ActividadRegistro();
$id    = $model->logReceta($user['correo'], $receta_id, $titulo, $calorias);

echo json_encode(['success' => (bool)$id]);
