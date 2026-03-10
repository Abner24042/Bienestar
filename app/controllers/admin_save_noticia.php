<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Noticia.php';
require_once __DIR__ . '/../helpers/file_helper.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

try {
    $model = new Noticia();

    $data = [
        'titulo' => $_POST['titulo'] ?? '',
        'contenido' => $_POST['contenido'] ?? '',
        'resumen' => $_POST['resumen'] ?? null,
        'categoria' => $_POST['categoria'] ?? 'general',
        'autor' => $_POST['autor'] ?? null,
        'publicado' => isset($_POST['publicado']) ? (int)$_POST['publicado'] : 0
    ];

    if (empty($data['titulo']) || empty($data['contenido'])) {
        throw new Exception('Título y contenido son requeridos');
    }

    // Manejar imagen
    if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $result = uploadFile($_FILES['imagen'], 'noticias');
        if ($result['success']) {
            $data['imagen'] = $result['url'];
        }
    }

    $id = $_POST['id'] ?? null;

    if (!empty($id)) {
        $existing = $model->findById($id);
        if (!$existing) {
            throw new Exception('Noticia no encontrada');
        }
        if (!isset($data['imagen'])) {
            unset($data['imagen']);
        }
        $model->update($id, $data);
        echo json_encode(['success' => true, 'message' => 'Noticia actualizada']);
    } else {
        $data['creado_por'] = $_POST['creado_por'] ?? null;
        $newId = $model->create($data);
        if ($newId) {
            echo json_encode(['success' => true, 'message' => 'Noticia creada', 'id' => $newId]);
        } else {
            throw new Exception('Error al crear noticia');
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
