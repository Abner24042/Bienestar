<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Rutina.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$auth = new AuthController();
if (!$auth->isAuthenticated() || !isProfessional()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

$user = $auth->getCurrentUser();
if ($user['rol'] !== 'coach') {
    echo json_encode(['success' => false, 'message' => 'Solo para coaches']);
    exit;
}

$model = new Rutina();
$rutinas = $model->getByCoach($user['correo']);
echo json_encode(['success' => true, 'rutinas' => $rutinas]);
