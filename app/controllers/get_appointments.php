<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

// Verificar autenticación
$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode([]);
    exit;
}

$user = $authController->getCurrentUser();

try {
    $citaModel = new Cita();
    $citas = $citaModel->getByEmailOrProfessional($user['correo']);

    // Formatear citas para el calendario
    $citasFormateadas = array_map(function($cita) {
        return [
            'id' => $cita['id'],
            'fecha' => $cita['fecha'],
            'hora' => $cita['hora'],
            'titulo' => $cita['titulo'],
            'profesional_correo' => $cita['profesional_correo'] ?? null
        ];
    }, $citas);

    echo json_encode([
        'success' => true,
        'appointments' => $citasFormateadas
    ]);

} catch (Exception $e) {
    error_log('Error en get_appointments: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'appointments' => []
    ]);
}
