<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Rutina.php';
require_once __DIR__ . '/../models/Plan.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$auth = new AuthController();
if (!$auth->isAuthenticated() || !isProfessional()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

$user = $auth->getCurrentUser();
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['usuario_id']) || empty($input['rutina_id'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$rutinaModel = new Rutina();
$rutina = $rutinaModel->getDetail($input['rutina_id']);
if (!$rutina) {
    echo json_encode(['success' => false, 'message' => 'Rutina no encontrada']);
    exit;
}

if (empty($rutina['ejercicios'])) {
    echo json_encode(['success' => false, 'message' => 'La rutina no tiene ejercicios']);
    exit;
}

$planModel = new Plan();
$asignados = 0;
foreach ($rutina['ejercicios'] as $ej) {
    $notas = 'Rutina: ' . $rutina['nombre'];
    if (!empty($input['notas'])) $notas .= ' — ' . $input['notas'];
    $planModel->asignarEjercicio($input['usuario_id'], $ej['ejercicio_id'], $user['correo'], $notas);
    $asignados++;
}

echo json_encode(['success' => true, 'asignados' => $asignados, 'message' => "$asignados ejercicio(s) agregados al plan del usuario"]);
