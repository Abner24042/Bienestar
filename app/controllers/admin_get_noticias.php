<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Noticia.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

try {
    $model = new Noticia();
    $noticias = $model->getAll();
    echo json_encode(['success' => true, 'noticias' => $noticias]);
} catch (Exception $e) {
    error_log('Error en admin_get_noticias: ' . $e->getMessage());
    echo json_encode(['success' => false, 'noticias' => []]);
}
