<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Receta.php';
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
    $model = new Receta();

    $toNull = fn($v) => ($v !== '' && $v !== null) ? $v : null;
    $data = [
        'titulo'             => $_POST['titulo'] ?? '',
        'descripcion'        => $_POST['descripcion'] ?? null,
        'ingredientes'       => $_POST['ingredientes'] ?? null,
        'instrucciones'      => $_POST['instrucciones'] ?? null,
        'tiempo_preparacion' => $toNull($_POST['tiempo_preparacion'] ?? null),
        'porciones'          => $toNull($_POST['porciones'] ?? null),
        'calorias'           => $toNull($_POST['calorias'] ?? null),
        'categoria'          => $_POST['categoria'] ?? 'comida',
        'proteinas'          => $toNull($_POST['proteinas'] ?? null),
        'carbohidratos'      => $toNull($_POST['carbohidratos'] ?? null),
        'grasas'             => $toNull($_POST['grasas'] ?? null),
        'fibra'              => $toNull($_POST['fibra'] ?? null),
    ];

    if (empty($data['titulo'])) {
        throw new Exception('El título es requerido');
    }

    if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $result = uploadFile($_FILES['imagen'], 'recetas');
        if ($result['success']) {
            $data['imagen'] = $result['url'];
        }
    }

    $id = $_POST['id'] ?? null;

    if (!empty($id)) {
        $existing = $model->findById($id);
        if (!$existing || $existing['creado_por'] !== $user['correo']) {
            throw new Exception('No tienes permiso para editar esta receta');
        }
        if (!isset($data['imagen'])) {
            unset($data['imagen']);
        }
        $model->update($id, $data);
        echo json_encode(['success' => true, 'message' => 'Receta actualizada']);
    } else {
        $data['creado_por'] = $user['correo'];
        $newId = $model->create($data);
        if ($newId) {
            echo json_encode(['success' => true, 'message' => 'Receta creada', 'id' => $newId]);
        } else {
            throw new Exception('Error al crear receta');
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
