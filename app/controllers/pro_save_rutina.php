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

$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['nombre'])) {
    echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
    exit;
}

$model = new Rutina();

if (!empty($input['id'])) {
    // Verificar que le pertenece
    $existing = $model->findById($input['id']);
    if (!$existing || $existing['coach_correo'] !== $user['correo']) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar esta rutina']);
        exit;
    }
    $model->update($input['id'], $input);
    $id = $input['id'];
} else {
    $input['coach_correo'] = $user['correo'];
    $id = $model->create($input);
}

try {
    $ejercicios = $input['ejercicios'] ?? [];
    $model->setEjercicios($id, $ejercicios);
} catch (Exception $e) {
    error_log('Error en setEjercicios: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al guardar los ejercicios']);
    exit;
}

echo json_encode(['success' => true, 'id' => $id]);
