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
    $data      = json_decode(file_get_contents('php://input'), true);
    $usuarioId = $data['usuario_id'] ?? null;
    $titulo    = trim($data['titulo']    ?? '');
    $contenido = trim($data['contenido'] ?? '');
    $tipo      = $data['tipo'] ?? 'general';

    if (!$usuarioId || !$titulo) throw new Exception('Datos incompletos');

    $profesional = $authController->getCurrentUser();
    $model = new Plan();
    $id = $model->addRecomendacion($usuarioId, $profesional['correo'], $titulo, $contenido, $tipo);

    echo json_encode($id
        ? ['success' => true,  'message' => 'Recomendación agregada']
        : ['success' => false, 'message' => 'Error al agregar']
    );
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
