<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/TestResult.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false, 'result' => null]);
    exit;
}

try {
    $user = $authController->getCurrentUser();
    $model = new TestResult();
    $result = $model->getLastResult($user['correo']);

    if ($result) {
        // Calcular cuánto tiempo hace
        $fecha = new DateTime($result['created_at']);
        $ahora = new DateTime();
        $diff = $ahora->diff($fecha);

        if ($diff->days === 0) {
            $hace = 'Hoy';
        } elseif ($diff->days === 1) {
            $hace = 'Hace 1 día';
        } else {
            $hace = 'Hace ' . $diff->days . ' días';
        }

        echo json_encode([
            'success' => true,
            'result' => [
                'puntaje' => (int)$result['puntaje'],
                'nivel' => $result['nivel'],
                'hace' => $hace
            ]
        ]);
    } else {
        echo json_encode(['success' => true, 'result' => null]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'result' => null]);
}
