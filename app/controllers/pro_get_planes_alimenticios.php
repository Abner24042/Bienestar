<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/PlanAlimenticio.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$auth = new AuthController();
if (!$auth->isAuthenticated() || !isProfessional()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

$user = $auth->getCurrentUser();
if ($user['rol'] !== 'nutriologo') {
    echo json_encode(['success' => false, 'message' => 'Solo para nutriólogos']);
    exit;
}

$model = new PlanAlimenticio();
$planes = $model->getByNutriologo($user['correo']);
echo json_encode(['success' => true, 'planes' => $planes]);
