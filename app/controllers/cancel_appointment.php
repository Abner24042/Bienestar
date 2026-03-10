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

    if (!$data || empty($data['citaId'])) {
        throw new Exception('ID de cita no proporcionado');
    }

    $citaId = $data['citaId'];

    // Obtener información de la cita antes de eliminarla
    $citaModel = new Cita();
    $cita = $citaModel->findById($citaId);

    if (!$cita) {
        throw new Exception('Cita no encontrada');
    }

    // Solo admin o profesional que la creó pueden cancelar
    $isAdminUser = isAdmin();
    $isProfCreator = isset($cita['profesional_correo']) && $cita['profesional_correo'] === $user['correo'];
    if (!$isAdminUser && !$isProfCreator) {
        throw new Exception('Solo un administrador puede cancelar citas');
    }

    // Eliminar la cita de la base de datos
    $eliminada = $citaModel->delete($citaId);

    if ($eliminada) {
        // Devolver información para enviar el email
        echo json_encode([
            'success' => true,
            'message' => 'Cita cancelada exitosamente',
            'citaData' => [
                'titulo' => $cita['titulo'],
                'fecha' => $cita['fecha'],
                'hora' => $cita['hora']
            ],
            'userData' => [
                'userName' => $user['nombre'],
                'userEmail' => $user['correo']
            ]
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
