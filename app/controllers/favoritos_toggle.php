<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Favorito.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false]);
    exit;
}

$user  = $authController->getCurrentUser();
$input = json_decode(file_get_contents('php://input'), true);

$tipo = $input['tipo'] ?? '';
$id   = (int)($input['id'] ?? 0);

if (!in_array($tipo, ['receta', 'ejercicio']) || !$id) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$model  = new Favorito();
$result = $model->toggle((int)$user['id'], $tipo, $id);

echo json_encode($result !== false
    ? ['success' => true, 'action' => $result]
    : ['success' => false]
);
