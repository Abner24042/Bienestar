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
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID requerido']);
    exit;
}

$model = new PlanAlimenticio();
$plan = $model->findById($input['id']);
if (!$plan || $plan['nutriologo_correo'] !== $user['correo']) {
    echo json_encode(['success' => false, 'message' => 'No encontrado o sin permisos']);
    exit;
}

$model->delete($input['id']);
echo json_encode(['success' => true]);
