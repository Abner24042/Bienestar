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

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID requerido']);
    exit;
}

$user = $auth->getCurrentUser();
$model = new PlanAlimenticio();
$plan = $model->getDetail($id);
if (!$plan || $plan['nutriologo_correo'] !== $user['correo']) {
    echo json_encode(['success' => false, 'message' => 'No encontrado']);
    exit;
}

echo json_encode(['success' => true, 'plan' => $plan]);
