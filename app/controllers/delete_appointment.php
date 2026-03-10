<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

// Verificar autenticación
$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$user = $authController->getCurrentUser();

try {
    // Obtener datos del POST
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || !isset($data['id'])) {
        throw new Exception('ID de cita no proporcionado');
    }

    $citaModel = new Cita();

    // Verificar que la cita pertenece al usuario
    $cita = $citaModel->findById($data['id']);

    if (!$cita) {
        throw new Exception('Cita no encontrada');
    }

    // Solo admin o profesional que la creó pueden cancelar
    $isAdminUser = isAdmin();
    $isProfCreator = isset($cita['profesional_correo']) && $cita['profesional_correo'] === $user['correo'];
    if (!$isAdminUser && !$isProfCreator) {
        throw new Exception('Solo un administrador puede cancelar citas');
    }

    // Eliminar la cita
    if ($citaModel->delete($data['id'])) {
        echo json_encode([
            'success' => true,
            'message' => 'Cita cancelada exitosamente'
        ]);
    } else {
        throw new Exception('Error al cancelar la cita');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
