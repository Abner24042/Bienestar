<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false, 'found' => false]);
    exit;
}

$user  = $authController->getCurrentUser();
$model = new Cita();
$cita  = $model->getNextUpcoming($user['correo']);

if (!$cita) {
    echo json_encode(['success' => true, 'found' => false]);
    exit;
}

$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

$fecha = new DateTime($cita['fecha']);
$hora  = date('g:i A', strtotime($cita['hora']));

echo json_encode([
    'success' => true,
    'found'   => true,
    'dia'     => $fecha->format('j'),
    'mes'     => $meses[(int)$fecha->format('n')],
    'titulo'      => $cita['titulo'],
    'hora'        => $hora,
    'descripcion' => $cita['descripcion'] ?? null
]);
