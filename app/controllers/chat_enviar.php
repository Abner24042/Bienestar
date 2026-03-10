<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Chat.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $destinatarioId = (int)($data['destinatario_id'] ?? 0);
    $contenido = trim($data['contenido'] ?? '');

    if (!$destinatarioId || !$contenido) throw new Exception('Datos incompletos');
    if (strlen($contenido) > 2000) throw new Exception('Mensaje demasiado largo (max 2000 caracteres)');

    $yo = $authController->getCurrentUser();
    $yoId = (int)$yo['id'];

    if ($yoId === $destinatarioId) throw new Exception('No puedes enviarte mensajes a ti mismo');

    $model = new Chat();
    $rolDestinatario = $model->getRolDestinatario($destinatarioId);
    if (!$rolDestinatario) throw new Exception('Usuario no encontrado');

    $rolesProf = ['coach', 'nutriologo', 'psicologo'];
    if (in_array($yo['rol'], $rolesProf)) {
        if ($rolDestinatario !== 'usuario') throw new Exception('Solo puedes chatear con usuarios');
        $usuarioId    = $destinatarioId;
        $profesionalId = $yoId;
    } else {
        if (!in_array($rolDestinatario, $rolesProf)) throw new Exception('Solo puedes chatear con profesionales');
        $usuarioId    = $yoId;
        $profesionalId = $destinatarioId;
    }

    $conversacionId = $model->getOCrearConversacion($usuarioId, $profesionalId);
    $msgId = $model->enviarMensaje($conversacionId, $yoId, $contenido);

    if (!$msgId) throw new Exception('Error al enviar mensaje');

    echo json_encode(['success' => true, 'mensaje_id' => $msgId, 'conversacion_id' => $conversacionId]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
