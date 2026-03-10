<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Noticia.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isProfessional()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

try {
    $user = $authController->getCurrentUser();
    $model = new Noticia();
    $noticias = $model->getByCreator($user['correo']);
    echo json_encode(['success' => true, 'noticias' => $noticias]);
} catch (Exception $e) {
    error_log('Error en pro_get_noticias: ' . $e->getMessage());
    echo json_encode(['success' => false, 'noticias' => []]);
}
