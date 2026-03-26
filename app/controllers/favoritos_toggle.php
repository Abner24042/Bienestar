<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Favorito.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false]);
    exit;
}

$user  = $authController->getCurrentUser();
$input = json_decode(file_get_contents('php://input'), true);

$tipo = $input['tipo'] ?? '';
$id   = (int)($input['id'] ?? 0);

if (!in_array($tipo, ['receta', 'ejercicio']) || !$id) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Capturar snapshot de la receta al momento de agregarla a favoritos
$snapshotData = null;
if ($tipo === 'receta') {
    try {
        $db   = (new Database())->getConnection();
        $stmt = $db->prepare("SELECT * FROM recetas WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $receta = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($receta) {
            $snapshotData = $receta;
        }
    } catch (Exception $e) {}
}

$model  = new Favorito();
$result = $model->toggle((int)$user['id'], $tipo, $id, $snapshotData);

echo json_encode($result !== false
    ? ['success' => true, 'action' => $result]
    : ['success' => false]
);
