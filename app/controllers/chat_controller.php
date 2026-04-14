<?php
/**
 * Chat Controller — maneja todas las rutas /api/chat/* y /api/admin/todos-usuarios
 * Determina la acción a partir de la URL y el método HTTP.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Chat.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$yo     = $authController->getCurrentUser();
$yoId   = (int)$yo['id'];
$yoRol  = strtolower($yo['rol']);
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Segmento tras /api/  → "chat/conversaciones", "admin/todos-usuarios", etc.
$segment = preg_replace('#^.*/api/#', '', $uri);
$segment = trim($segment, '/');

$CHAT_ACTION = match(true) {
    $method === 'GET'  && $segment === 'chat/conversaciones'      => 'conversaciones',
    $method === 'GET'  && $segment === 'chat/mensajes'            => 'mensajes',
    $method === 'GET'  && $segment === 'chat/no-leidos'           => 'no_leidos',
    $method === 'GET'  && $segment === 'chat/usuarios-disponibles'=> 'usuarios_disponibles',
    $method === 'GET'  && $segment === 'admin/todos-usuarios'     => 'todos_usuarios',
    $method === 'POST' && $segment === 'chat/enviar'              => 'enviar',
    $method === 'POST' && $segment === 'chat/marcar-leido'        => 'marcar_leido',
    $method === 'POST' && $segment === 'chat/eliminar-mensaje'    => 'eliminar_mensaje',
    $method === 'POST' && $segment === 'chat/eliminar-chat'       => 'eliminar_chat',
    $method === 'POST' && $segment === 'chat/subir-archivo'       => 'subir_archivo',
    default                                                       => 'unknown',
};

// ════════════════════════════════════════════════════════════════
try {
    $model = new Chat();

    switch ($CHAT_ACTION) {

        // ── GET conversaciones ───────────────────────────────────
        case 'conversaciones': {
            $conversaciones = $model->getConversaciones($yoId);
            echo json_encode(['success' => true, 'conversaciones' => $conversaciones]);
            break;
        }

        // ── GET mensajes ─────────────────────────────────────────
        case 'mensajes': {
            $convId  = (int)($_GET['conversacion_id'] ?? 0);
            $desdeId = (int)($_GET['desde_id'] ?? 0);
            if (!$convId) throw new Exception('ID requerido');
            if (!$model->validarParticipante($convId, $yoId)) throw new Exception('Sin acceso');
            $mensajes = $model->getMensajes($convId, $desdeId);
            echo json_encode(['success' => true, 'mensajes' => $mensajes]);
            break;
        }

        // ── GET no-leidos ────────────────────────────────────────
        case 'no_leidos': {
            echo json_encode(['success' => true, 'total' => $model->getNoLeidosTotal($yoId)]);
            break;
        }

        // ── GET usuarios-disponibles ─────────────────────────────
        case 'usuarios_disponibles': {
            $db = (new Database())->getConnection();
            $profesionales = ['coach', 'nutriologo', 'psicologo'];
            if ($yoRol === 'usuario') {
                $stmt = $db->prepare(
                    "SELECT id, nombre, correo, rol FROM usuarios
                     WHERE activo = 1 AND id != :me AND LOWER(rol) != 'administrador'
                     ORDER BY rol ASC, nombre ASC"
                );
            } else {
                $stmt = $db->prepare(
                    "SELECT id, nombre, correo, rol FROM usuarios
                     WHERE activo = 1 AND id != :me
                     ORDER BY rol ASC, nombre ASC"
                );
            }
            $stmt->execute([':me' => $yoId]);
            echo json_encode(['success' => true, 'usuarios' => $stmt->fetchAll()]);
            break;
        }

        // ── GET todos-usuarios (solo admin) ──────────────────────
        case 'todos_usuarios': {
            if (!isAdmin()) { echo json_encode(['success' => false]); exit; }
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare(
                "SELECT id, nombre, correo, rol FROM usuarios
                 WHERE activo = 1 AND id != :me ORDER BY nombre ASC"
            );
            $stmt->execute([':me' => $yoId]);
            echo json_encode(['success' => true, 'usuarios' => $stmt->fetchAll()]);
            break;
        }

        // ── POST enviar ──────────────────────────────────────────
        case 'enviar': {
            $data           = json_decode(file_get_contents('php://input'), true);
            $destinatarioId = (int)($data['destinatario_id'] ?? 0);
            $contenido      = trim($data['contenido'] ?? '');

            if (!$destinatarioId || !$contenido) throw new Exception('Datos incompletos');
            if (strlen($contenido) > 2000) throw new Exception('Mensaje demasiado largo (max 2000 caracteres)');
            if ($yoId === $destinatarioId) throw new Exception('No puedes enviarte mensajes a ti mismo');

            $destRol = $model->getRolDestinatario($destinatarioId);
            if (!$destRol) throw new Exception('Usuario no encontrado');

            if ($yoRol === 'usuario' && strtolower($destRol) === 'administrador') {
                $uid = min($yoId, $destinatarioId);
                $pid = max($yoId, $destinatarioId);
                if (!$model->existeConversacion($uid, $pid)) {
                    throw new Exception('No puedes iniciar una conversación con el administrador');
                }
            }

            $usuarioId      = min($yoId, $destinatarioId);
            $profesionalId  = max($yoId, $destinatarioId);
            $convId         = $model->getOCrearConversacion($usuarioId, $profesionalId);
            $msgId          = $model->enviarMensaje($convId, $yoId, $contenido);
            if (!$msgId) throw new Exception('Error al enviar mensaje');

            echo json_encode(['success' => true, 'mensaje_id' => $msgId, 'conversacion_id' => $convId]);
            break;
        }

        // ── POST marcar-leido ────────────────────────────────────
        case 'marcar_leido': {
            $data   = json_decode(file_get_contents('php://input'), true);
            $convId = (int)($data['conversacion_id'] ?? 0);
            if (!$convId) { echo json_encode(['success' => false]); exit; }
            if (!$model->validarParticipante($convId, $yoId)) { echo json_encode(['success' => false]); exit; }
            $model->marcarLeido($convId, $yoId);
            echo json_encode(['success' => true]);
            break;
        }

        // ── POST eliminar-mensaje ────────────────────────────────
        case 'eliminar_mensaje': {
            $data      = json_decode(file_get_contents('php://input'), true);
            $mensajeId = (int)($data['mensaje_id'] ?? 0);
            if (!$mensajeId) throw new Exception('ID inválido');
            echo json_encode(['success' => $model->eliminarMensaje($mensajeId, $yoId)]);
            break;
        }

        // ── POST eliminar-chat ───────────────────────────────────
        case 'eliminar_chat': {
            $data   = json_decode(file_get_contents('php://input'), true);
            $convId = (int)($data['conversacion_id'] ?? 0);
            if (!$convId) throw new Exception('ID inválido');
            echo json_encode(['success' => $model->eliminarChat($convId, $yoId)]);
            break;
        }

        // ── POST subir-archivo ───────────────────────────────────
        case 'subir_archivo': {
            $destinatarioId = (int)($_POST['destinatario_id'] ?? 0);
            if (!$destinatarioId) throw new Exception('Destinatario requerido');

            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                $errCode = $_FILES['archivo']['error'] ?? -1;
                if ($errCode === UPLOAD_ERR_INI_SIZE || $errCode === UPLOAD_ERR_FORM_SIZE) {
                    throw new Exception('El archivo excede el tamaño permitido');
                }
                throw new Exception('No se recibió ningún archivo');
            }

            $file = $_FILES['archivo'];
            if ($file['size'] > 10 * 1024 * 1024) throw new Exception('El archivo excede el límite de 10 MB');

            $allowedExt  = ['pdf','doc','docx','xls','xlsx','ppt','pptx','jpg','jpeg','png','gif','zip','txt','mp4','mp3','csv'];
            $originalName = basename($file['name']);
            $ext         = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt)) throw new Exception('Tipo de archivo no permitido');

            if ($yoId === $destinatarioId) throw new Exception('No puedes enviarte archivos a ti mismo');

            $destRol = $model->getRolDestinatario($destinatarioId);
            if (!$destRol) throw new Exception('Usuario no encontrado');

            if ($yoRol === 'usuario' && strtolower($destRol) === 'administrador') {
                $uid = min($yoId, $destinatarioId);
                $pid = max($yoId, $destinatarioId);
                if (!$model->existeConversacion($uid, $pid)) {
                    throw new Exception('No puedes iniciar una conversación con el administrador');
                }
            }

            $uploadDir  = UPLOAD_PATH . '/chat/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $safeName   = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $uniqueName = time() . '_' . $yoId . '_' . $safeName;
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $uniqueName)) {
                throw new Exception('Error al guardar el archivo');
            }

            $archivoUrl    = ASSETS_URL . '/uploads/chat/' . $uniqueName;
            $usuarioId     = min($yoId, $destinatarioId);
            $profesionalId = max($yoId, $destinatarioId);
            $convId        = $model->getOCrearConversacion($usuarioId, $profesionalId);
            $msgId         = $model->enviarArchivo($convId, $yoId, $originalName, $archivoUrl);
            if (!$msgId) throw new Exception('Error al registrar el archivo');

            echo json_encode(['success' => true, 'mensaje_id' => $msgId, 'conversacion_id' => $convId]);
            break;
        }

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
