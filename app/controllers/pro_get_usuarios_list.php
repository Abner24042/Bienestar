<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Plan.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isProfessional()) {
    echo json_encode(['success' => false]);
    exit;
}

$model    = new Plan();
$usuarios = $model->getUsuarios();
echo json_encode(['success' => true, 'usuarios' => $usuarios]);
