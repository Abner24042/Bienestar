<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Ejercicio.php';
require_once __DIR__ . '/../helpers/file_helper.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isProfessional()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

try {
    $user = $authController->getCurrentUser();
    $model = new Ejercicio();

    $data = [
        'titulo' => $_POST['titulo'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? null,
        'duracion' => $_POST['duracion'] ?? null,
        'nivel' => $_POST['nivel'] ?? 'principiante',
        'tipo' => $_POST['tipo'] ?? 'cardio',
        'calorias_quemadas'   => $_POST['calorias_quemadas'] ?? null,
        'musculo_objetivo'    => trim($_POST['musculo_objetivo'] ?? '') ?: null,
        'equipamiento'        => trim($_POST['equipamiento'] ?? '') ?: null,
        'musculos_secundarios'=> trim($_POST['musculos_secundarios'] ?? '') ?: null,
        'video_url'           => $_POST['video_url'] ?? null,
        'instrucciones'       => $_POST['instrucciones'] ?? null
    ];

    if (empty($data['titulo'])) {
        throw new Exception('El título es requerido');
    }

    if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $result = uploadFile($_FILES['imagen'], 'ejercicios');
        if ($result['success']) {
            $data['imagen'] = $result['url'];
        }
    }

    $id = $_POST['id'] ?? null;

    if (!empty($id)) {
        $existing = $model->findById($id);
        if (!$existing || $existing['creado_por'] !== $user['correo']) {
            throw new Exception('No tienes permiso para editar este ejercicio');
        }
        if (!isset($data['imagen'])) {
            unset($data['imagen']);
        }
        $model->update($id, $data);
        echo json_encode(['success' => true, 'message' => 'Ejercicio actualizado']);
    } else {
        $data['creado_por'] = $user['correo'];
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
