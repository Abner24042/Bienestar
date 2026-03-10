<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Ejercicio.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isProfessional()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

try {
    $user = $authController->getCurrentUser();
    $model = new Ejercicio();
    $ejercicios = $model->getByCreator($user['correo']);
    echo json_encode(['success' => true, 'ejercicios' => $ejercicios]);
} catch (Exception $e) {
    error_log('Error en pro_get_ejercicios: ' . $e->getMessage());
    echo json_encode(['success' => false, 'ejercicios' => []]);
}
