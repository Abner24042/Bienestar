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
$myRol = strtolower($currentUser['rol']);
$profesionales = ['coach', 'nutriologo', 'psicologo'];

if ($myRol === 'usuario') {
    // Usuario: puede ver especialistas y otros usuarios — NO admin
    $stmt = $db->prepare(
        "SELECT id, nombre, correo, rol FROM usuarios
         WHERE activo = 1 AND id != :me
           AND LOWER(rol) != 'administrador'
         ORDER BY rol ASC, nombre ASC"
    );
} elseif (in_array($myRol, $profesionales)) {
    // Especialista: puede ver usuarios, otros especialistas y admin
    $stmt = $db->prepare(
        "SELECT id, nombre, correo, rol FROM usuarios
         WHERE activo = 1 AND id != :me
         ORDER BY rol ASC, nombre ASC"
    );
} else {
    // Admin: puede ver a todos
    $stmt = $db->prepare(
        "SELECT id, nombre, correo, rol FROM usuarios
         WHERE activo = 1 AND id != :me
         ORDER BY rol ASC, nombre ASC"
    );
}

$stmt->execute([':me' => $currentUser['id']]);
$usuarios = $stmt->fetchAll();

echo json_encode(['success' => true, 'usuarios' => $usuarios]);
