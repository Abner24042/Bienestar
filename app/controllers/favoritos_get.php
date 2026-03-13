<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Favorito.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false]);
    exit;
}

$user  = $authController->getCurrentUser();
$model = new Favorito();

$ids  = $model->getIds((int)$user['id']);
$data = $model->getWithData((int)$user['id']);

echo json_encode([
    'success'       => true,
    'receta_ids'    => $ids['receta_ids'],
    'ejercicio_ids' => $ids['ejercicio_ids'],
    'recetas'       => $data['recetas'],
    'ejercicios'    => $data['ejercicios'],
]);
