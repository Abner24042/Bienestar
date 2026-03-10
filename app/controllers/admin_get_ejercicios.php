<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Ejercicio.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

try {
    $model = new Ejercicio();
    $ejercicios = $model->getAll();
    echo json_encode(['success' => true, 'ejercicios' => $ejercicios]);
} catch (Exception $e) {
    error_log('Error en admin_get_ejercicios: ' . $e->getMessage());
    echo json_encode(['success' => false, 'ejercicios' => []]);
}
