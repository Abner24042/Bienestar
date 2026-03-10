<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/ActividadRegistro.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false]);
    exit;
}

$user  = $authController->getCurrentUser();
$model = new ActividadRegistro();

echo json_encode([
    'success'           => true,
    'calorias_hoy'      => $model->getCaloriasHoy($user['correo']),
    'ejercicios_semana' => $model->getEjerciciosEstaSemana($user['correo'])
]);
