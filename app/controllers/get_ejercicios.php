<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Ejercicio.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

try {
    $model = new Ejercicio();
    $ejercicios = $model->getActive();

    echo json_encode([
        'success' => true,
        'ejercicios' => $ejercicios
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
