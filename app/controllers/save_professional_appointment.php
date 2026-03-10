<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

if (!isProfessional()) {
    echo json_encode(['success' => false, 'message' => 'Solo profesionales pueden usar este endpoint']);
    exit;
}

$professional = $authController->getCurrentUser();

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Datos invalidos');
    }

    if (empty($data['user_email']) || empty($data['date']) ||
        empty($data['time']) || empty($data['title'])) {
        throw new Exception('Faltan datos requeridos (usuario, fecha, hora, titulo)');
    }

    // Verificar que el usuario destino existe
    $userModel = new User();
    $targetUser = $userModel->findByEmail($data['user_email']);
    if (!$targetUser) {
        throw new Exception('Usuario no encontrado');
    }

    $citaData = [
        'fecha' => $data['date'],
        'hora' => $data['time'],
        'titulo' => $data['title'],
        'descripcion' => $data['description'] ?? null,
        'correo' => $data['user_email'],
        'profesional_correo' => $professional['correo']
    ];

    $citaModel = new Cita();
    $citaId = $citaModel->create($citaData);

    error_log('save_professional_appointment - citaId result: ' . var_export($citaId, true));
    error_log('save_professional_appointment - citaData: ' . json_encode($citaData));

    if ($citaId) {
        echo json_encode([
            'success' => true,
            'message' => 'Cita creada exitosamente',
            'citaId' => $citaId,
            'userData' => [
                'userName' => $targetUser['nombre'],
                'userEmail' => $targetUser['correo']
            ],
            'professionalData' => [
                'name' => $professional['nombre'],
                'email' => $professional['correo']
            ]
        ]);
    } else {
        throw new Exception('Error al guardar la cita');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
