<?php
/**
 * Pro Controller — maneja todas las rutas /api/pro/*
 * Determina la acción a partir de la URL y el método HTTP.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/../models/Noticia.php';
require_once __DIR__ . '/../models/Plan.php';
require_once __DIR__ . '/../models/PlanAlimenticio.php';
require_once __DIR__ . '/../models/Rutina.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/file_helper.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$auth = new AuthController();
if (!$auth->isAuthenticated() || !isProfessional()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

$yo     = $auth->getCurrentUser();
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$segment = preg_replace('#^.*/api/pro/?#', '', $uri);
$segment = trim($segment, '/');

$ACTION = match(true) {
    // ── Plan (recetas/ejercicios/recomendaciones del usuario) ──
    $method === 'GET'  && $segment === 'plan/get-usuario'           => 'plan_get_usuario',
    $method === 'POST' && $segment === 'plan/asignar-receta'        => 'plan_asignar_receta',
    $method === 'POST' && $segment === 'plan/recomendar'            => 'plan_recomendar',
    $method === 'POST' && $segment === 'plan/remove'                => 'plan_remove',
    // ── Usuarios ───────────────────────────────────────────────
    $method === 'GET'  && $segment === 'usuarios-list'              => 'usuarios_list',
    $method === 'GET'  && $segment === 'recomendaciones'            => 'recomendaciones',
    // ── Solicitudes de cita ────────────────────────────────────
    $method === 'GET'  && $segment === 'solicitudes'                => 'solicitudes_get',
    $method === 'GET'  && $segment === 'solicitudes/count'          => 'solicitudes_count',
    $method === 'POST' && $segment === 'solicitudes/accion'         => 'solicitudes_accion',
    // ── Noticias ───────────────────────────────────────────────
    $method === 'GET'  && $segment === 'noticias'                   => 'noticias_get',
    $method === 'POST' && $segment === 'noticias/save'              => 'noticias_save',
    $method === 'POST' && $segment === 'noticias/delete'            => 'noticias_delete',
    // ── Rutinas ────────────────────────────────────────────────
    $method === 'GET'  && $segment === 'rutinas'                    => 'rutinas_get',
    $method === 'GET'  && $segment === 'rutinas/detail'             => 'rutinas_detail',
    $method === 'POST' && $segment === 'rutinas/save'               => 'rutinas_save',
    $method === 'POST' && $segment === 'rutinas/delete'             => 'rutinas_delete',
    $method === 'POST' && $segment === 'rutinas/asignar'            => 'rutinas_asignar',
    // ── Planes alimenticios ────────────────────────────────────
    $method === 'GET'  && $segment === 'planes-alimenticios'        => 'planes_get',
    $method === 'GET'  && $segment === 'planes-alimenticios/detail' => 'planes_detail',
    $method === 'POST' && $segment === 'planes-alimenticios/save'   => 'planes_save',
    $method === 'POST' && $segment === 'planes-alimenticios/delete' => 'planes_delete',
    $method === 'POST' && $segment === 'planes-alimenticios/asignar'=> 'planes_asignar',
    default                                                         => 'unknown',
};

// ════════════════════════════════════════════════════════════════
try {
    switch ($ACTION) {

        // ── Plan: obtener datos de un usuario ────────────────────
        case 'plan_get_usuario': {
            $usuarioId = $_GET['usuario_id'] ?? null;
            if (!$usuarioId) throw new Exception('ID requerido');
            $model     = new Plan();
            $userModel = new User();
            $userData  = $userModel->findById((int)$usuarioId);
            $plan      = $model->getMiPlan($usuarioId);
            $plan['recomendaciones'] = $model->getRecomendacionesPorProEnPlan($usuarioId, $yo['correo']);
            $peso   = $userData['peso']   ?? null;
            $altura = $userData['altura'] ?? null;
            $imc    = ($peso && $altura && $altura > 0) ? round($peso / ($altura * $altura), 1) : null;
            echo json_encode([
                'success'                => true,
                'plan'                   => $plan,
                'ejercicios_disponibles' => $model->getEjerciciosDisponibles(),
                'recetas_disponibles'    => $yo['rol'] === 'nutriologo'
                    ? $model->getRecetasDisponibles($yo['correo'])
                    : $model->getRecetasDisponibles(),
                'salud' => [
                    'peso'   => $peso   ? (float)$peso   : null,
                    'altura' => $altura ? (float)$altura : null,
                    'imc'    => $imc,
                ],
            ]);
            break;
        }

        // ── Plan: asignar receta a usuario ───────────────────────
        case 'plan_asignar_receta': {
            $data      = json_decode(file_get_contents('php://input'), true);
            $usuarioId = $data['usuario_id'] ?? null;
            $recetaId  = $data['receta_id']  ?? null;
            $notas     = trim($data['notas'] ?? '') ?: null;
            if (!$usuarioId || !$recetaId) throw new Exception('Datos incompletos');
            $ok = (new Plan())->asignarReceta($usuarioId, $recetaId, $yo['correo'], $notas);
            echo json_encode($ok
                ? ['success' => true,  'message' => 'Receta asignada al plan']
                : ['success' => false, 'message' => 'Error al asignar']);
            break;
        }

        // ── Plan: agregar recomendación ──────────────────────────
        case 'plan_recomendar': {
            $data      = json_decode(file_get_contents('php://input'), true);
            $usuarioId = $data['usuario_id'] ?? null;
            $titulo    = trim($data['titulo']    ?? '');
            $contenido = trim($data['contenido'] ?? '');
            $tipo      = $data['tipo'] ?? 'general';
            if (!$usuarioId || !$titulo) throw new Exception('Datos incompletos');
            $id = (new Plan())->addRecomendacion($usuarioId, $yo['correo'], $titulo, $contenido, $tipo);
            echo json_encode($id
                ? ['success' => true,  'message' => 'Recomendación agregada']
                : ['success' => false, 'message' => 'Error al agregar']);
            break;
        }

        // ── Plan: eliminar elemento ──────────────────────────────
        case 'plan_remove': {
            $data  = json_decode(file_get_contents('php://input'), true);
            $tipo  = $data['tipo'] ?? '';
            $id    = $data['id']   ?? null;
            if (!$tipo || !$id) throw new Exception('Datos incompletos');
            $model = new Plan();
            $ok = match ($tipo) {
                'ejercicio'     => $model->removeEjercicio($id),
                'receta'        => $model->removeReceta($id),
                'recomendacion' => $model->removeRecomendacion($id, $yo['correo']),
                default         => false,
            };
            echo json_encode($ok
                ? ['success' => true,  'message' => 'Eliminado del plan']
                : ['success' => false, 'message' => 'No encontrado o sin permisos']);
            break;
        }

        // ── Usuarios list ────────────────────────────────────────
        case 'usuarios_list': {
            echo json_encode(['success' => true, 'usuarios' => (new Plan())->getUsuarios()]);
            break;
        }

        // ── Recomendaciones del profesional ──────────────────────
        case 'recomendaciones': {
            echo json_encode(['success' => true, 'recomendaciones' => (new Plan())->getRecomendacionesByPro($yo['correo'])]);
            break;
        }

        // ── Solicitudes: listar ──────────────────────────────────
        case 'solicitudes_get': {
            $sols = (new Cita())->getSolicitudesPorProfesional($yo['correo']);
            echo json_encode(['success' => true, 'solicitudes' => $sols]);
            break;
        }

        // ── Solicitudes: contar ──────────────────────────────────
        case 'solicitudes_count': {
            echo json_encode(['count' => (new Cita())->getSolicitudesCount($yo['correo'])]);
            break;
        }

        // ── Solicitudes: aceptar / denegar ───────────────────────
        case 'solicitudes_accion': {
            $input  = json_decode(file_get_contents('php://input'), true);
            $id     = (int)($input['id']     ?? 0);
            $accion = $input['accion'] ?? '';
            if (!$id || !$accion) throw new Exception('Faltan datos');
            $model  = new Cita();

            if ($accion === 'aceptar') {
                $titulo = trim($input['titulo'] ?? '');
                $fecha  = trim($input['fecha']  ?? '');
                $hora   = trim($input['hora']   ?? '');
                $notas  = trim($input['notas']  ?? '') ?: null;
                if (!$titulo || !$fecha || !$hora) throw new Exception('Título, fecha y hora son obligatorios');
                $ok = $model->aceptarSolicitud($id, $yo['correo'], $titulo, $fecha, $hora, $notas);
                echo json_encode(['success' => (bool)$ok]);
                break;
            }

            if ($accion === 'denegar') {
                $motivo = trim($input['motivo'] ?? '');
                $reasig = $input['reasignado_a'] ?? null;
                if (!$motivo) throw new Exception('Debes indicar el motivo');
                if ($reasig) {
                    $stmt = (new Database())->getConnection()->prepare(
                        "SELECT correo FROM usuarios WHERE correo = :c AND rol IN ('nutriologo','coach','psicologo') LIMIT 1"
                    );
                    $stmt->execute([':c' => $reasig]);
                    if (!$stmt->fetch()) $reasig = null;
                }
                $ok = $model->denegarSolicitud($id, $motivo, $reasig ?: null);
                echo json_encode(['success' => (bool)$ok]);
                break;
            }

            throw new Exception('Acción no reconocida');
        }

        // ── Noticias: listar propias ─────────────────────────────
        case 'noticias_get': {
            echo json_encode(['success' => true, 'noticias' => (new Noticia())->getByCreator($yo['correo'])]);
            break;
        }

        // ── Noticias: guardar ────────────────────────────────────
        case 'noticias_save': {
            $model = new Noticia();
            $data  = [
                'titulo'    => $_POST['titulo']    ?? '',
                'contenido' => $_POST['contenido'] ?? '',
                'resumen'   => $_POST['resumen']   ?? null,
                'categoria' => $_POST['categoria'] ?? 'general',
                'autor'     => $yo['nombre'],
                'publicado' => isset($_POST['publicado']) ? (int)$_POST['publicado'] : 0,
            ];
            if (empty($data['titulo']) || empty($data['contenido'])) throw new Exception('Título y contenido son requeridos');
            if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $result = uploadFile($_FILES['imagen'], 'noticias');
                if ($result['success']) $data['imagen'] = $result['url'];
            }
            $id = $_POST['id'] ?? null;
            if (!empty($id)) {
                $existing = $model->findById($id);
                if (!$existing || $existing['creado_por'] !== $yo['correo']) throw new Exception('Sin permiso para editar');
                if (!isset($data['imagen'])) unset($data['imagen']);
                $model->update($id, $data);
                echo json_encode(['success' => true, 'message' => 'Noticia actualizada']);
            } else {
                $data['creado_por'] = $yo['correo'];
                $newId = $model->create($data);
                if (!$newId) throw new Exception('Error al crear noticia');
                echo json_encode(['success' => true, 'message' => 'Noticia creada', 'id' => $newId]);
            }
            break;
        }

        // ── Noticias: eliminar ───────────────────────────────────
        case 'noticias_delete': {
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['id'])) throw new Exception('ID requerido');
            $model    = new Noticia();
            $existing = $model->findById($data['id']);
            if (!$existing || $existing['creado_por'] !== $yo['correo']) throw new Exception('Sin permiso para eliminar');
            $model->delete($data['id']);
            echo json_encode(['success' => true, 'message' => 'Publicación eliminada']);
            break;
        }

        // ── Rutinas: listar ──────────────────────────────────────
        case 'rutinas_get': {
            if ($yo['rol'] !== 'coach') throw new Exception('Solo para coaches');
            echo json_encode(['success' => true, 'rutinas' => (new Rutina())->getByCoach($yo['correo'])]);
            break;
        }

        // ── Rutinas: detalle ─────────────────────────────────────
        case 'rutinas_detail': {
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) throw new Exception('ID requerido');
            $model  = new Rutina();
            $rutina = $model->getDetail($id);
            if (!$rutina || $rutina['coach_correo'] !== $yo['correo']) throw new Exception('No encontrado');
            echo json_encode(['success' => true, 'rutina' => $rutina]);
            break;
        }

        // ── Rutinas: guardar ─────────────────────────────────────
        case 'rutinas_save': {
            if ($yo['rol'] !== 'coach') throw new Exception('Solo para coaches');
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['nombre'])) throw new Exception('El nombre es requerido');
            $model = new Rutina();
            if (!empty($input['id'])) {
                $existing = $model->findById($input['id']);
                if (!$existing || $existing['coach_correo'] !== $yo['correo']) throw new Exception('Sin permiso para editar');
                $model->update($input['id'], $input);
                $id = $input['id'];
            } else {
                $input['coach_correo'] = $yo['correo'];
                $id = $model->create($input);
            }
            $model->setEjercicios($id, $input['ejercicios'] ?? []);
            echo json_encode(['success' => true, 'id' => $id]);
            break;
        }

        // ── Rutinas: eliminar ────────────────────────────────────
        case 'rutinas_delete': {
            $input  = json_decode(file_get_contents('php://input'), true);
            if (empty($input['id'])) throw new Exception('ID requerido');
            $model  = new Rutina();
            $rutina = $model->findById($input['id']);
            if (!$rutina || $rutina['coach_correo'] !== $yo['correo']) throw new Exception('Sin permisos');
            $model->delete($input['id']);
            echo json_encode(['success' => true]);
            break;
        }

        // ── Rutinas: asignar a usuario ───────────────────────────
        case 'rutinas_asignar': {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['usuario_id']) || empty($input['rutina_id'])) throw new Exception('Datos incompletos');
            $rutina = (new Rutina())->getDetail($input['rutina_id']);
            if (!$rutina) throw new Exception('Rutina no encontrada');
            if (empty($rutina['ejercicios'])) throw new Exception('La rutina no tiene ejercicios');
            $planModel = new Plan();
            $asignados = 0;
            foreach ($rutina['ejercicios'] as $ej) {
                $notas = 'Rutina: ' . $rutina['nombre'];
                if (!empty($input['notas'])) $notas .= ' — ' . $input['notas'];
                $planModel->asignarEjercicio($input['usuario_id'], $ej['ejercicio_id'], $yo['correo'], $notas);
                $asignados++;
            }
            echo json_encode(['success' => true, 'asignados' => $asignados, 'message' => "$asignados ejercicio(s) agregados al plan del usuario"]);
            break;
        }

        // ── Planes alimenticios: listar ──────────────────────────
        case 'planes_get': {
            if ($yo['rol'] !== 'nutriologo') throw new Exception('Solo para nutriólogos');
            echo json_encode(['success' => true, 'planes' => (new PlanAlimenticio())->getByNutriologo($yo['correo'])]);
            break;
        }

        // ── Planes alimenticios: detalle ─────────────────────────
        case 'planes_detail': {
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) throw new Exception('ID requerido');
            $model = new PlanAlimenticio();
            $plan  = $model->getDetail($id);
            if (!$plan || $plan['nutriologo_correo'] !== $yo['correo']) throw new Exception('No encontrado');
            echo json_encode(['success' => true, 'plan' => $plan]);
            break;
        }

        // ── Planes alimenticios: guardar ─────────────────────────
        case 'planes_save': {
            if ($yo['rol'] !== 'nutriologo') throw new Exception('Solo para nutriólogos');
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['nombre'])) throw new Exception('El nombre es requerido');
            $model = new PlanAlimenticio();
            if (!empty($input['id'])) {
                $existing = $model->findById($input['id']);
                if (!$existing || $existing['nutriologo_correo'] !== $yo['correo']) throw new Exception('Sin permiso para editar');
                $model->update($input['id'], $input);
                $id = $input['id'];
            } else {
                $input['nutriologo_correo'] = $yo['correo'];
                $id = $model->create($input);
            }
            $model->setRecetas($id, $input['recetas'] ?? []);
            echo json_encode(['success' => true, 'id' => $id]);
            break;
        }

        // ── Planes alimenticios: eliminar ────────────────────────
        case 'planes_delete': {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['id'])) throw new Exception('ID requerido');
            $model = new PlanAlimenticio();
            $plan  = $model->findById($input['id']);
            if (!$plan || $plan['nutriologo_correo'] !== $yo['correo']) throw new Exception('Sin permisos');
            $model->delete($input['id']);
            echo json_encode(['success' => true]);
            break;
        }

        // ── Planes alimenticios: asignar a usuario ───────────────
        case 'planes_asignar': {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['usuario_id']) || empty($input['plan_id'])) throw new Exception('Datos incompletos');
            $plan = (new PlanAlimenticio())->getDetail($input['plan_id']);
            if (!$plan) throw new Exception('Plan no encontrado');
            if (empty($plan['recetas'])) throw new Exception('El plan no tiene recetas');
            $dias      = ['','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
            $planModel = new Plan();
            $asignados = 0;
            foreach ($plan['recetas'] as $r) {
                $dia   = $dias[(int)$r['dia_semana']] ?? 'Día ' . $r['dia_semana'];
                $notas = 'Plan: ' . $plan['nombre'] . ' — ' . $dia . ' (' . ucfirst($r['tiempo_comida']) . ')';
                if (!empty($input['notas'])) $notas .= ' — ' . $input['notas'];
                $planModel->asignarReceta($input['usuario_id'], $r['receta_id'], $yo['correo'], $notas, (int)$r['dia_semana']);
                $asignados++;
            }
            echo json_encode(['success' => true, 'asignados' => $asignados, 'message' => "$asignados receta(s) agregadas al plan del usuario"]);
            break;
        }

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
    }
} catch (Exception $e) {
    error_log('pro_controller: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
