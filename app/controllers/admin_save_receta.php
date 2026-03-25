<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Receta.php';
require_once __DIR__ . '/../helpers/file_helper.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

try {
    $model = new Receta();

    $data = [
        'titulo' => $_POST['titulo'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? null,
        'ingredientes' => $_POST['ingredientes'] ?? null,
        'instrucciones' => $_POST['instrucciones'] ?? null,
        'tiempo_preparacion' => $_POST['tiempo_preparacion'] ?? null,
        'porciones' => $_POST['porciones'] ?? null,
        'calorias' => $_POST['calorias'] ?? null,
        'categoria' => $_POST['categoria'] ?? 'comida',
        'proteinas' => $_POST['proteinas'] !== '' ? ($_POST['proteinas'] ?? null) : null,
        'carbohidratos' => $_POST['carbohidratos'] !== '' ? ($_POST['carbohidratos'] ?? null) : null,
        'grasas' => $_POST['grasas'] !== '' ? ($_POST['grasas'] ?? null) : null,
        'fibra' => $_POST['fibra'] !== '' ? ($_POST['fibra'] ?? null) : null,
    ];

    if (empty($data['titulo'])) {
        throw new Exception('El título es requerido');
    }

    // Manejar imagen
    if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $result = uploadFile($_FILES['imagen'], 'recetas');
        if ($result['success']) {
            $data['imagen'] = $result['url'];
        }
    }

    $id = $_POST['id'] ?? null;

    if (!empty($id)) {
        $existing = $model->findById($id);
        if (!$existing) {
            throw new Exception('Receta no encontrada');
        }
        if (!isset($data['imagen'])) {
            unset($data['imagen']);
        }
        $model->update($id, $data);
        echo json_encode(['success' => true, 'message' => 'Receta actualizada']);
    } else {
        $data['creado_por'] = $_POST['creado_por'] ?? null;
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
