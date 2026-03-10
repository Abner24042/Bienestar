<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Plan.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isProfessional()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $tipo = $data['tipo'] ?? '';   // 'ejercicio' | 'receta' | 'recomendacion'
    $id   = $data['id']   ?? null;

    if (!$tipo || !$id) throw new Exception('Datos incompletos');

    $profesional = $authController->getCurrentUser();
    $model = new Plan();
    $ok = match ($tipo) {
        'ejercicio'      => $model->removeEjercicio($id),
        'receta'         => $model->removeReceta($id),
        'recomendacion'  => $model->removeRecomendacion($id, $profesional['correo']),
        default          => false,
    };

    echo json_encode($ok
        ? ['success' => true,  'message' => 'Eliminado del plan']
        : ['success' => false, 'message' => 'No encontrado o sin permisos']
    );
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
