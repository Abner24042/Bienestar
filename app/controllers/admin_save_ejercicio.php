<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Ejercicio.php';
require_once __DIR__ . '/../helpers/file_helper.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

try {
    $model = new Ejercicio();

    $data = [
        'titulo' => $_POST['titulo'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? null,
        'duracion' => $_POST['duracion'] ?? null,
        'nivel' => $_POST['nivel'] ?? 'principiante',
        'tipo' => $_POST['tipo'] ?? 'cardio',
        'calorias_quemadas' => $_POST['calorias_quemadas'] ?? null,
        'video_url' => $_POST['video_url'] ?? null,
        'instrucciones' => $_POST['instrucciones'] ?? null
    ];

    if (empty($data['titulo'])) {
        throw new Exception('El título es requerido');
    }

    // Manejar imagen
    if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $result = uploadFile($_FILES['imagen'], 'ejercicios');
        if ($result['success']) {
            $data['imagen'] = $result['url'];
        }
    }

    $id = $_POST['id'] ?? null;

    if (!empty($id)) {
        $existing = $model->findById($id);
        if (!$existing) {
            throw new Exception('Ejercicio no encontrado');
        }
        if (!isset($data['imagen'])) {
            unset($data['imagen']);
        }
        $model->update($id, $data);
        echo json_encode(['success' => true, 'message' => 'Ejercicio actualizado']);
    } else {
        $data['creado_por'] = $_POST['creado_por'] ?? null;
        $newId = $model->create($data);
        if ($newId) {
            echo json_encode(['success' => true, 'message' => 'Ejercicio creado', 'id' => $newId]);
        } else {
            throw new Exception('Error al crear ejercicio');
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
