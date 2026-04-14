<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Plan.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isProfessional()) {
    echo json_encode(['success' => false]);
    exit;
}

$usuarioId = $_GET['usuario_id'] ?? null;
if (!$usuarioId) {
    echo json_encode(['success' => false, 'message' => 'ID requerido']);
    exit;
}

$profesional = $authController->getCurrentUser();
$model = new Plan();
$userModel = new User();
$usuarioData = $userModel->findById((int)$usuarioId);
$planBase = $model->getMiPlan($usuarioId);

// Solo mostrar las recomendaciones que puso este profesional
$planBase['recomendaciones'] = $model->getRecomendacionesPorProEnPlan($usuarioId, $profesional['correo']);

$ejerciciosDisponibles = $model->getEjerciciosDisponibles();
$recetasDisponibles    = $profesional['rol'] === 'nutriologo'
    ? $model->getRecetasDisponibles($profesional['correo'])
    : $model->getRecetasDisponibles();

$peso   = $usuarioData['peso']   ?? null;
$altura = $usuarioData['altura'] ?? null;
$imc    = ($peso && $altura && $altura > 0) ? round($peso / ($altura * $altura), 1) : null;

echo json_encode([
    'success'   => true,
    'plan'      => $planBase,
    'ejercicios_disponibles' => $ejerciciosDisponibles,
    'recetas_disponibles'    => $recetasDisponibles,
    'salud'     => [
        'peso'   => $peso   ? (float)$peso   : null,
        'altura' => $altura ? (float)$altura : null,
        'imc'    => $imc,
    ],
]);
