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

$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['nombre'])) {
    echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
    exit;
}

$model = new PlanAlimenticio();

if (!empty($input['id'])) {
    $existing = $model->findById($input['id']);
    if (!$existing || $existing['nutriologo_correo'] !== $user['correo']) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar este plan']);
        exit;
    }
    $model->update($input['id'], $input);
    $id = $input['id'];
} else {
    $input['nutriologo_correo'] = $user['correo'];
    $id = $model->create($input);
}

try {
    $recetas = $input['recetas'] ?? [];
    $model->setRecetas($id, $recetas);
} catch (Exception $e) {
    error_log('Error en setRecetas: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al guardar las recetas']);
    exit;
}

echo json_encode(['success' => true, 'id' => $id]);
