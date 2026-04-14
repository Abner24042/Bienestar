<?php
/**
 * Get Controller — maneja GETs generales de la app
 * Rutas: /api/appointments, /api/appointments/next, /api/appointments/professional,
 *        /api/noticias, /api/mi-plan, /api/especialistas,
 *        /api/users, /api/test/last, /api/dashboard/stats
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/../models/Noticia.php';
require_once __DIR__ . '/../models/Plan.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/TestResult.php';
require_once __DIR__ . '/../models/ActividadRegistro.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$yo    = $authController->getCurrentUser();
$uri   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$segment = preg_replace('#^.*/api/#', '', $uri);
$segment = trim($segment, '/');

$ACTION = match($segment) {
    'appointments'              => 'appointments',
    'appointments/next'         => 'appointments_next',
    'professional-appointments' => 'professional_appointments',
    'noticias'                  => 'noticias',
    'mi-plan'                   => 'mi_plan',
    'especialistas'             => 'especialistas',
    'users'                     => 'users',
    'test/last'                 => 'test_last',
    'dashboard/stats'           => 'dashboard_stats',
    default                     => 'unknown',
};

// ════════════════════════════════════════════════════════════════
try {
    switch ($ACTION) {

        // ── Citas del usuario/profesional (calendario) ───────────
        case 'appointments': {
            $citas = (new Cita())->getByEmailOrProfessional($yo['correo']);
            $citasFormateadas = array_map(fn($c) => [
                'id'                 => $c['id'],
                'fecha'              => $c['fecha'],
                'hora'               => $c['hora'],
                'titulo'             => $c['titulo'],
                'descripcion'        => $c['descripcion'] ?? null,
                'profesional_correo' => $c['profesional_correo'] ?? null,
            ], $citas);
            echo json_encode(['success' => true, 'appointments' => $citasFormateadas]);
            break;
        }

        // ── Próxima cita ─────────────────────────────────────────
        case 'appointments_next': {
            $cita = (new Cita())->getNextUpcoming($yo['correo']);
            if (!$cita) { echo json_encode(['success' => true, 'found' => false]); break; }
            $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                      7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
            $fecha = new DateTime($cita['fecha']);
            echo json_encode([
                'success'     => true,
                'found'       => true,
                'dia'         => $fecha->format('j'),
                'mes'         => $meses[(int)$fecha->format('n')],
                'titulo'      => $cita['titulo'],
                'hora'        => date('g:i A', strtotime($cita['hora'])),
                'descripcion' => $cita['descripcion'] ?? null,
            ]);
            break;
        }

        // ── Citas del profesional (Mi Agenda) ────────────────────
        case 'professional_appointments': {
            if (!isProfessional()) {
                echo json_encode(['success' => false, 'message' => 'Solo profesionales']);
                break;
            }
            $upcoming = isset($_GET['upcoming']) && $_GET['upcoming'] === 'true';
            $model    = new Cita();
            $citas    = $upcoming
                ? $model->getUpcomingByProfessional($yo['correo'])
                : $model->getByProfessional($yo['correo']);
            echo json_encode(['success' => true, 'appointments' => $citas]);
            break;
        }

        // ── Noticias publicadas ──────────────────────────────────
        case 'noticias': {
            echo json_encode(['success' => true, 'noticias' => (new Noticia())->getPublished()]);
            break;
        }

        // ── Mi plan (recetas + recomendaciones) ──────────────────
        case 'mi_plan': {
            $todas = isset($_GET['todas']) && $_GET['todas'] == '1';
            $plan  = (new Plan())->getMiPlan($yo['id'], !$todas);
            echo json_encode(['success' => true, 'plan' => $plan]);
            break;
        }

        // ── Lista de especialistas por tipo ──────────────────────
        case 'especialistas': {
            $tipo   = $_GET['tipo'] ?? '';
            $rolMap = [
                'Nutrición'         => 'nutriologo',
                'Ejercicio / Coach' => 'coach',
                'Psicología'        => 'psicologo',
            ];
            $rol  = $rolMap[$tipo] ?? null;
            $db   = (new Database())->getConnection();
            if ($rol) {
                $stmt = $db->prepare(
                    "SELECT nombre, correo, area FROM usuarios WHERE rol = :rol ORDER BY nombre ASC"
                );
                $stmt->execute([':rol' => $rol]);
            } else {
                $stmt = $db->query(
                    "SELECT nombre, correo, area, rol FROM usuarios
                     WHERE rol IN ('nutriologo','coach','psicologo') ORDER BY nombre ASC"
                );
            }
            echo json_encode(['success' => true, 'especialistas' => $stmt->fetchAll()]);
            break;
        }

        // ── Lista de usuarios (pro + admin) ──────────────────────
        case 'users': {
            if (!isProfessional() && !isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Sin permisos']);
                break;
            }
            echo json_encode(['success' => true, 'users' => (new User())->getAllRegularUsers()]);
            break;
        }

        // ── Último resultado de test ─────────────────────────────
        case 'test_last': {
            $result = (new TestResult())->getLastResult($yo['correo']);
            if (!$result) { echo json_encode(['success' => true, 'result' => null]); break; }
            $diff = (new DateTime())->diff(new DateTime($result['created_at']));
            $hace = $diff->days === 0 ? 'Hoy' : ($diff->days === 1 ? 'Hace 1 día' : "Hace {$diff->days} días");
            echo json_encode([
                'success' => true,
                'result'  => ['puntaje' => (int)$result['puntaje'], 'nivel' => $result['nivel'], 'hace' => $hace],
            ]);
            break;
        }

        // ── Stats del dashboard ──────────────────────────────────
        case 'dashboard_stats': {
            $model = new ActividadRegistro();
            echo json_encode([
                'success'           => true,
                'calorias_hoy'      => $model->getCaloriasHoy($yo['correo']),
                'ejercicios_semana' => $model->getEjerciciosEstaSemana($yo['correo']),
            ]);
            break;
        }

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
    }
} catch (Exception $e) {
    error_log('get_controller: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
