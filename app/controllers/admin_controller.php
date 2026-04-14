<?php
/**
 * Admin Controller — maneja todas las rutas /api/admin/*
 * Determina la acción a partir de la URL y el método HTTP.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/../models/Noticia.php';
require_once __DIR__ . '/../helpers/file_helper.php';
require_once __DIR__ . '/AuthController.php';

$authController = new AuthController();

// Determinar acción desde la URI
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Quitar BASE_URL prefix si aplica (ej. /Bienestar/api/admin/users/save → users_save)
$segment = preg_replace('#^.*/api/admin/?#', '', $uri);  // "users/save", "noticias", etc.
$segment = trim($segment, '/');

$ADMIN_ACTION = match(true) {
    $method === 'GET'  && $segment === 'stats'          => 'stats',
    $method === 'GET'  && $segment === 'appointments'   => 'appointments',
    $method === 'GET'  && $segment === 'users'          => 'users',
    $method === 'POST' && $segment === 'users/save'     => 'users_save',
    $method === 'GET'  && $segment === 'noticias'       => 'noticias',
    $method === 'POST' && $segment === 'noticias/save'  => 'noticias_save',
    $method === 'POST' && $segment === 'noticias/delete'=> 'noticias_delete',
    $method === 'GET'  && $segment === 'export'         => 'export',
    default                                             => 'unknown',
};

// ── Export devuelve CSV, el resto JSON ──────────────────────────
$isExport = ($ADMIN_ACTION === 'export');
if (!$isExport) {
    header('Content-Type: application/json');
}

// ── Auth ────────────────────────────────────────────────────────
if (!$authController->isAuthenticated() || !isAdmin()) {
    if ($isExport) { http_response_code(403); exit('Sin permisos'); }
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

// ════════════════════════════════════════════════════════════════
try {
    switch ($ADMIN_ACTION) {

        // ── GET stats ───────────────────────────────────────────
        case 'stats': {
            $db = (new Database())->getConnection();
            $q  = fn($sql) => (int)$db->query($sql)->fetchColumn();
            echo json_encode([
                'success' => true,
                'stats'   => [
                    'usuarios'            => $q("SELECT COUNT(*) FROM usuarios"),
                    'usuarios_nuevos_mes' => $q("SELECT COUNT(*) FROM usuarios WHERE MONTH(fecha)=MONTH(NOW()) AND YEAR(fecha)=YEAR(NOW())"),
                    'citas'               => $q("SELECT COUNT(*) FROM citas_bieniestar"),
                    'citas_futuras'       => $q("SELECT COUNT(*) FROM citas_bieniestar WHERE fecha >= CURDATE()"),
                    'citas_semana'        => $q("SELECT COUNT(*) FROM citas_bieniestar WHERE YEARWEEK(fecha,1)=YEARWEEK(NOW(),1)"),
                    'ejercicios'          => $q("SELECT COUNT(*) FROM ejercicios WHERE activo=1"),
                    'recetas'             => $q("SELECT COUNT(*) FROM recetas"),
                    'noticias'            => $q("SELECT COUNT(*) FROM noticias WHERE publicado=1"),
                ]
            ]);
            break;
        }

        // ── GET appointments ────────────────────────────────────
        case 'appointments': {
            $appointments = (new Cita())->getAll();
            echo json_encode(['success' => true, 'appointments' => $appointments]);
            break;
        }

        // ── GET users ───────────────────────────────────────────
        case 'users': {
            echo json_encode(['success' => true, 'users' => (new User())->getAll()]);
            break;
        }

        // ── POST users/save ─────────────────────────────────────
        case 'users_save': {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) throw new Exception('Datos inválidos');

            $validRoles = ['usuario', 'Administrador', 'coach', 'nutriologo', 'psicologo'];
            if (!empty($data['rol']) && !in_array($data['rol'], $validRoles)) {
                throw new Exception('Rol no válido');
            }

            $userModel = new User();

            if (!empty($data['id'])) {
                $existing = $userModel->findById($data['id']);
                if (!$existing) throw new Exception('Usuario no encontrado');
                $userModel->update($data['id'], [
                    'nombre' => $data['nombre'] ?? $existing['nombre'],
                    'correo' => $data['correo'] ?? $existing['correo'],
                    'foto'   => $existing['foto'],
                    'area'   => $data['area']   ?? $existing['area'],
                ]);
                if (!empty($data['rol']))      $userModel->updateRole($data['id'], $data['rol']);
                if (!empty($data['password'])) $userModel->changePassword($data['id'], $data['password']);
                echo json_encode(['success' => true, 'message' => 'Usuario actualizado']);
            } else {
                if (empty($data['nombre']) || empty($data['correo'])) throw new Exception('Nombre y correo son requeridos');
                if (empty($data['password'])) throw new Exception('Contraseña requerida para nuevo usuario');
                if ($userModel->findByEmail($data['correo'])) throw new Exception('El correo ya está registrado');
                $userId = $userModel->create([
                    'nombre'   => $data['nombre'],
                    'correo'   => $data['correo'],
                    'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                    'rol'      => $data['rol']  ?? 'usuario',
                    'area'     => $data['area'] ?? null,
                ]);
                if (!$userId) throw new Exception('Error al crear usuario');
                echo json_encode(['success' => true, 'message' => 'Usuario creado', 'userId' => $userId]);
            }
            break;
        }

        // ── GET noticias ────────────────────────────────────────
        case 'noticias': {
            echo json_encode(['success' => true, 'noticias' => (new Noticia())->getAll()]);
            break;
        }

        // ── POST noticias/save ──────────────────────────────────
        case 'noticias_save': {
            $model = new Noticia();
            $data  = [
                'titulo'    => $_POST['titulo']    ?? '',
                'contenido' => $_POST['contenido'] ?? '',
                'resumen'   => $_POST['resumen']   ?? null,
                'categoria' => $_POST['categoria'] ?? 'general',
                'autor'     => $_POST['autor']     ?? null,
                'publicado' => isset($_POST['publicado']) ? (int)$_POST['publicado'] : 0,
            ];
            if (empty($data['titulo']) || empty($data['contenido'])) {
                throw new Exception('Título y contenido son requeridos');
            }
            if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $result = uploadFile($_FILES['imagen'], 'noticias');
                if ($result['success']) $data['imagen'] = $result['url'];
            }
            $id = $_POST['id'] ?? null;
            if (!empty($id)) {
                if (!$model->findById($id)) throw new Exception('Noticia no encontrada');
                if (!isset($data['imagen'])) unset($data['imagen']);
                $model->update($id, $data);
                echo json_encode(['success' => true, 'message' => 'Noticia actualizada']);
            } else {
                $data['creado_por'] = $_POST['creado_por'] ?? null;
                $newId = $model->create($data);
                if (!$newId) throw new Exception('Error al crear noticia');
                echo json_encode(['success' => true, 'message' => 'Noticia creada', 'id' => $newId]);
            }
            break;
        }

        // ── POST noticias/delete ────────────────────────────────
        case 'noticias_delete': {
            $data   = json_decode(file_get_contents('php://input'), true);
            if (empty($data['id'])) throw new Exception('ID requerido');
            $model  = new Noticia();
            $action = $data['action'] ?? 'delete';
            if ($action === 'toggle') {
                $model->togglePublished($data['id'], $data['publicado'] ?? 0);
                echo json_encode(['success' => true, 'message' => ($data['publicado'] ?? 0) ? 'Noticia publicada' : 'Noticia despublicada']);
            } elseif ($action === 'destacar') {
                $model->setDestacado($data['id']);
                echo json_encode(['success' => true, 'message' => 'Noticia marcada como destacada']);
            } else {
                $model->delete($data['id']);
                echo json_encode(['success' => true, 'message' => 'Noticia eliminada']);
            }
            break;
        }

        // ── GET export (CSV) ────────────────────────────────────
        case 'export': {
            $type    = $_GET['type'] ?? '';
            $allowed = ['usuarios', 'citas', 'ejercicios', 'recetas', 'noticias'];
            if (!in_array($type, $allowed)) { http_response_code(400); exit('Tipo inválido'); }

            $db   = (new Database())->getConnection();
            $date = date('Y-m-d');

            $map = [
                'usuarios'   => ["SELECT id, nombre, correo, rol, fecha FROM usuarios ORDER BY fecha DESC",
                                 ['ID','Nombre','Correo','Rol','Fecha Registro'], "usuarios_{$date}.csv"],
                'citas'      => ["SELECT id, fecha, hora, titulo, descripcion, correo, profesional_correo FROM citas_bieniestar ORDER BY fecha DESC, hora DESC",
                                 ['ID','Fecha','Hora','Título','Descripción','Usuario','Profesional'], "citas_{$date}.csv"],
                'ejercicios' => ["SELECT id, titulo, tipo, nivel, duracion, calorias_quemadas, activo, creado_por, created_at FROM ejercicios ORDER BY created_at DESC",
                                 ['ID','Título','Tipo','Nivel','Duración (min)','Calorías','Activo','Creado Por','Fecha'], "ejercicios_{$date}.csv"],
                'recetas'    => ["SELECT id, titulo, categoria, tiempo_preparacion, porciones, calorias, activo, creado_por, created_at FROM recetas ORDER BY created_at DESC",
                                 ['ID','Título','Categoría','Tiempo (min)','Porciones','Calorías','Activo','Creado Por','Fecha'], "recetas_{$date}.csv"],
                'noticias'   => ["SELECT id, titulo, categoria, autor, publicado, destacado, fecha_publicacion, url_fuente FROM noticias ORDER BY fecha_publicacion DESC",
                                 ['ID','Título','Categoría','Autor','Publicado','Destacada','Fecha Publicación','URL Fuente'], "noticias_{$date}.csv"],
            ];

            [$sql, $headers, $filename] = $map[$type];
            $rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers, ';');
            foreach ($rows as $row) fputcsv($out, array_values($row), ';');
            fclose($out);
            break;
        }

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
    }
} catch (Exception $e) {
    error_log('admin_controller: ' . $e->getMessage());
    if ($isExport) { http_response_code(500); exit('Error al exportar'); }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
