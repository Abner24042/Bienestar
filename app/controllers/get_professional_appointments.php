<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false, 'appointments' => []]);
    exit;
}

if (!isProfessional()) {
    echo json_encode(['success' => false, 'message' => 'Solo profesionales']);
    exit;
}

$professional = $authController->getCurrentUser();

try {
    $citaModel = new Cita();

    $upcoming = isset($_GET['upcoming']) && $_GET['upcoming'] === 'true';

    if ($upcoming) {
        $citas = $citaModel->getUpcomingByProfessional($professional['correo']);
    } else {
        $citas = $citaModel->getByProfessional($professional['correo']);
    }

    echo json_encode([
        'success' => true,
        'appointments' => $citas
    ]);
} catch (Exception $e) {
    error_log('Error en get_professional_appointments: ' . $e->getMessage());
    echo json_encode(['success' => false, 'appointments' => []]);
}
