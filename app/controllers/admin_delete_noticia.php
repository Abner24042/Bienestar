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
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (empty($data['id'])) {
        throw new Exception('ID requerido');
    }

    $model = new Noticia();
    $action = $data['action'] ?? 'delete';

    if ($action === 'toggle') {
        $publicado = $data['publicado'] ?? 0;
        $model->togglePublished($data['id'], $publicado);
        echo json_encode(['success' => true, 'message' => $publicado ? 'Noticia publicada' : 'Noticia despublicada']);
    } elseif ($action === 'destacar') {
        $model->setDestacado($data['id']);
        echo json_encode(['success' => true, 'message' => 'Noticia marcada como destacada']);
    } else {
        $model->delete($data['id']);
        echo json_encode(['success' => true, 'message' => 'Noticia eliminada']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
