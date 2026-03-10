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

$currentUser = $authController->getCurrentUser();
$db = (new Database())->getConnection();

// Todos pueden hablar con todos
$stmt = $db->prepare(
    "SELECT id, nombre, correo, rol FROM usuarios
     WHERE activo = 1 AND id != :me
     ORDER BY rol ASC, nombre ASC"
);
$stmt->execute([':me' => $currentUser['id']]);
$usuarios = $stmt->fetchAll();

echo json_encode(['success' => true, 'usuarios' => $usuarios]);
