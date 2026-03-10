<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Chat.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$conversacionId = (int)($data['conversacion_id'] ?? 0);

if (!$conversacionId) {
    echo json_encode(['success' => false]);
    exit;
}

$yo = $authController->getCurrentUser();
$model = new Chat();

if (!$model->validarParticipante($conversacionId, (int)$yo['id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$model->marcarLeido($conversacionId, (int)$yo['id']);
echo json_encode(['success' => true]);
