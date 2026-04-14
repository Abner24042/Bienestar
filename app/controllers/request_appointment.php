<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$user = $authController->getCurrentUser();

try {
    $input = file_get_contents('php://input');
    $data  = json_decode($input, true);

    if (!$data || empty($data['tipo']) || empty($data['fecha']) || empty($data['especialista'])) {
        throw new Exception('Debes seleccionar tipo, fecha y especialista');
    }

    $motivo = !empty($data['motivo']) ? substr(strip_tags($data['motivo']), 0, 300) : '';

    $citaModel = new Cita();
    $citaId = $citaModel->createSolicitud([
        'fecha'       => $data['fecha'],
        'tipo'        => $data['tipo'],
        'motivo'      => $motivo ?: 'Sin motivo especificado',
        'correo'      => $user['correo'],
        'especialista'=> $data['especialista'],
    ]);

    if ($citaId) {
        echo json_encode(['success' => true, 'message' => 'Solicitud enviada correctamente']);
    } else {
        throw new Exception('Error al guardar la solicitud');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
