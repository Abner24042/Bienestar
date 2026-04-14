<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false]);
    exit;
}

$tipo = $_GET['tipo'] ?? '';

$rolMap = [
    'Nutrición'        => 'nutriologo',
    'Ejercicio / Coach'=> 'coach',
    'Psicología'       => 'psicologo',
];

$rol = $rolMap[$tipo] ?? null;

try {
    $db = (new Database())->getConnection();
    if ($rol) {
        $stmt = $db->prepare(
            "SELECT nombre, correo, area FROM usuarios
             WHERE rol = :rol ORDER BY nombre ASC"
        );
        $stmt->execute([':rol' => $rol]);
    } else {
        $stmt = $db->query(
            "SELECT nombre, correo, area, rol FROM usuarios
             WHERE rol IN ('nutriologo','coach','psicologo') ORDER BY nombre ASC"
        );
    }
    echo json_encode(['success' => true, 'especialistas' => $stmt->fetchAll()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
