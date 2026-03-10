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

try {
    $model = new Receta();
    $recetas = $model->getAutoUnapproved();
    echo json_encode(['success' => true, 'recetas' => $recetas]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
