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

// Solo profesionales pueden crear citas
if (!isProfessional()) {
    echo json_encode(['success' => false, 'message' => 'Solo los especialistas pueden agendar citas']);
    exit;
}

try {
    // Obtener datos del POST
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Datos inválidos');
    }

    // Validar datos requeridos
    if (empty($data['title']) || empty($data['date']) || empty($data['time']) || empty($data['type'])) {
        throw new Exception('Faltan datos requeridos');
    }

    // Preparar datos para guardar
    $citaData = [
        'fecha' => $data['date'],  // Ya viene en formato YYYY-MM-DD
        'hora' => $data['time'],
        'titulo' => $data['title'],
        'correo' => $user['correo']
    ];

    // Guardar en base de datos
    $citaModel = new Cita();
    $citaId = $citaModel->create($citaData);

    if ($citaId) {
        // Devolver también los datos del usuario para el email
        echo json_encode([
            'success' => true,
            'message' => 'Cita guardada exitosamente',
            'citaId' => $citaId,
            'userData' => [
                'userName' => $user['nombre'],
                'userEmail' => $user['correo']
            ]
        ]);
    } else {
        throw new Exception('Error al guardar la cita');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
