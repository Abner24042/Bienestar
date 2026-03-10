<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Chat.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false]);
    exit;
}

$yo = $authController->getCurrentUser();
$conversacionId = (int)($_GET['conversacion_id'] ?? 0);
$desdeMensajeId = (int)($_GET['desde_id'] ?? 0);

if (!$conversacionId) {
    echo json_encode(['success' => false, 'message' => 'ID requerido']);
    exit;
}

$model = new Chat();
if (!$model->validarParticipante($conversacionId, (int)$yo['id'])) {
    echo json_encode(['success' => false, 'message' => 'Sin acceso']);
    exit;
}

$mensajes = $model->getMensajes($conversacionId, $desdeMensajeId);
echo json_encode(['success' => true, 'mensajes' => $mensajes]);
