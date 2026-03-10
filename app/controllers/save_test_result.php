<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/TestResult.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

try {
    $user = $authController->getCurrentUser();
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['puntaje']) || !isset($data['nivel'])) {
        throw new Exception('Datos incompletos');
    }

    $puntaje = (int)$data['puntaje'];
    if ($puntaje < 0 || $puntaje > 20) {
        throw new Exception('Puntaje inválido');
    }

    $model = new TestResult();
    $id = $model->saveResult($user['correo'], $puntaje, $data['nivel']);

    if ($id) {
        echo json_encode(['success' => true, 'message' => 'Resultado guardado']);
    } else {
        throw new Exception('Error al guardar resultado');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
