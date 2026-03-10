<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

if (!isProfessional() && !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

try {
    $userModel = new User();
    $users = $userModel->getAllRegularUsers();
    echo json_encode(['success' => true, 'users' => $users]);
} catch (Exception $e) {
    error_log('Error en get_users: ' . $e->getMessage());
    echo json_encode(['success' => false, 'users' => []]);
}
