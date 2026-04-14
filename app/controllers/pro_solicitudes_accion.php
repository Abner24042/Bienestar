<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isProfessional()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$user  = $authController->getCurrentUser();
$input = json_decode(file_get_contents('php://input'), true);
$model = new Cita();

$id     = (int)($input['id'] ?? 0);
$accion = $input['accion'] ?? '';   // 'aceptar' | 'denegar'
$motivo = trim($input['motivo'] ?? '');
$reasig = $input['reasignado_a'] ?? null;   // email, opcional

if (!$id || !$accion) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    exit;
}

if ($accion === 'aceptar') {
    $titulo = trim($input['titulo'] ?? '');
    $fecha  = trim($input['fecha']  ?? '');
    $hora   = trim($input['hora']   ?? '');
    $notas  = trim($input['notas']  ?? '') ?: null;

    if (!$titulo || !$fecha || !$hora) {
        echo json_encode(['success' => false, 'message' => 'Título, fecha y hora son obligatorios']);
        exit;
    }
    $ok = $model->aceptarSolicitud($id, $user['correo'], $titulo, $fecha, $hora, $notas);
    echo json_encode(['success' => (bool)$ok]);
    exit;
}

if ($accion === 'denegar') {
    if (!$motivo) {
        echo json_encode(['success' => false, 'message' => 'Debes indicar el motivo']);
        exit;
    }
    // Validar que reasignado_a (si viene) sea un profesional real
    if ($reasig) {
        $db   = (new Database())->getConnection();
        $stmt = $db->prepare(
            "SELECT correo FROM usuarios
             WHERE correo = :c AND rol IN ('nutriologo','coach','psicologo') LIMIT 1"
        );
        $stmt->execute([':c' => $reasig]);
        if (!$stmt->fetch()) {
            $reasig = null;   // ignorar si no es válido
        }
    }
    $ok = $model->denegarSolicitud($id, $motivo, $reasig ?: null);
    echo json_encode(['success' => (bool)$ok]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
