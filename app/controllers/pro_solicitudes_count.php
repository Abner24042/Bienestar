<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isProfessional()) {
    echo json_encode(['count' => 0]);
    exit;
}

$user  = $authController->getCurrentUser();
$model = new Cita();
echo json_encode(['count' => $model->getSolicitudesCount($user['correo'])]);
