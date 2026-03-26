<?php

require_once APP_PATH . '/models/Ejercicio.php';
require_once APP_PATH . '/helpers/file_helper.php';

class EjercicioController extends BaseController
{

    private Ejercicio $model;

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->model = new Ejercicio();
    }

    // ---- usuario normal ----

    public function index(): void
    {
        $this->requireAuth();
        $this->success(['ejercicios' => $this->model->getActive()]);
    }

    // ---- admin ----

    public function adminIndex(): void
    {
        $this->requireAdmin();
        $this->success(['ejercicios' => $this->model->getAll()]);
    }

    // crea o actualiza, si viene 'id' en el form es edicion, si no es creacion
    public function adminStore(): void
    {
        $this->requireAdmin();

        $titulo = trim($this->post('titulo', ''));
        if ($titulo === '')
            $this->error('El título es requerido');

        $id = (int) $this->post('id', 0);

        $data = [
            'titulo' => $titulo,
            'descripcion' => $this->toNull($this->post('descripcion')),
            'duracion' => $this->toNull($this->post('duracion')),
            'nivel' => $this->post('nivel', 'principiante'),
            'tipo' => $this->post('tipo', 'cardio'),
            'calorias_quemadas' => $this->toNull($this->post('calorias_quemadas')),
            'musculo_objetivo' => $this->toNull($this->post('musculo_objetivo')),
            'musculos_secundarios' => $this->toNull($this->post('musculos_secundarios')),
            'equipamiento' => $this->toNull($this->post('equipamiento')),
            'video_url' => $this->toNull($this->post('video_url')),
            'instrucciones' => $this->toNull($this->post('instrucciones')),
        ];

        // si viene imagen la subimos, si no la dejamos como estaba (en edicion)
        if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $result = uploadFile($_FILES['imagen'], 'ejercicios');
            if ($result['success']) {
                $data['imagen'] = $result['url'];
            }
        }

        if ($id) {
            if (!$this->model->findById($id))
                $this->error('Ejercicio no encontrado', 404);
            $this->model->update($id, $data);
            $this->success([], 'Ejercicio actualizado');
        } else {
            $data['creado_por'] = $this->post('creado_por');
            $data['imagen'] = $data['imagen'] ?? null;
            $newId = $this->model->create($data);
            if (!$newId)
                $this->error('Error al guardar el ejercicio');
            $this->success(['id' => $newId], 'Ejercicio creado');
        }
    }

    // recibe {activo: 0 o 1} en el body JSON y cambia el estado del ejercicio
    public function adminToggle(): void
    {
        $this->requireAdmin();
        $id = (int) $this->param('id');
        $body = $this->getJsonBody();
        if (!$id || !isset($body['activo']))
            $this->error('Datos inválidos');

        $activo = (int) $body['activo'];
        $this->model->toggleActive($id, $activo);
        $this->success([], $activo ? 'Ejercicio activado' : 'Ejercicio desactivado');
    }

    public function adminDestroy(): void
    {
        $this->requireAdmin();
        $id = (int) $this->param('id');
        if (!$id)
            $this->error('ID requerido');
        if (!$this->model->findById($id))
            $this->error('Ejercicio no encontrado', 404);
        $this->model->delete($id);
        $this->success([], 'Ejercicio eliminado');
    }

    // ---- profesional ----

    // getForCoach devuelve ejercicios manuales (no auto-generados) disponibles para asignar
    public function proIndex(): void
    {
        $this->requireProfessional();
        $this->success(['ejercicios' => $this->model->getForCoach()]);
    }

    // el profesional solo puede editar ejercicios que el mismo creo
    // el form del panel manda 'pro_ejercicio_id', tambien aceptamos 'id' por compatibilidad
    public function proStore(): void
    {
        $this->requireProfessional();
        $user = $this->currentUser();

        $titulo = trim($this->post('titulo', ''));
        if ($titulo === '')
            $this->error('El título es requerido');

        $id = (int) ($this->post('pro_ejercicio_id', 0) ?: $this->post('id', 0));

        $data = [
            'titulo' => $titulo,
            'descripcion' => $this->toNull($this->post('descripcion')),
            'duracion' => $this->toNull($this->post('duracion')),
            'nivel' => $this->post('nivel', 'principiante'),
            'tipo' => $this->post('tipo', 'cardio'),
            'calorias_quemadas' => $this->toNull($this->post('calorias_quemadas')),
            'musculo_objetivo' => $this->toNull(trim($this->post('musculo_objetivo', ''))),
            'musculos_secundarios' => $this->toNull(trim($this->post('musculos_secundarios', ''))),
            'equipamiento' => $this->toNull(trim($this->post('equipamiento', ''))),
            'video_url' => $this->toNull($this->post('video_url')),
            'instrucciones' => $this->toNull($this->post('instrucciones')),
        ];

        if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $result = uploadFile($_FILES['imagen'], 'ejercicios');
            if ($result['success']) {
                $data['imagen'] = $result['url'];
            }
        }

        if ($id) {
            $existing = $this->model->findById($id);
            if (!$existing)
                $this->error('Ejercicio no encontrado', 404);
            // verificacion de propiedad: no puede editar ejercicios de otro profesional
            if ($existing['creado_por'] !== $user['correo']) {
                $this->error('Sin permisos para editar este ejercicio', 403);
            }
            $this->model->update($id, $data);
            $this->success([], 'Ejercicio actualizado');
        } else {
            $data['creado_por'] = $user['correo'];
            $data['imagen'] = $data['imagen'] ?? null;
            $newId = $this->model->create($data);
            if (!$newId)
                $this->error('Error al guardar el ejercicio');
            $this->success(['id' => $newId], 'Ejercicio creado');
        }
    }

    public function proDestroy(): void
    {
        $this->requireProfessional();
        $user = $this->currentUser();
        $id = (int) $this->param('id');
        if (!$id)
            $this->error('ID requerido');

        $existing = $this->model->findById($id);
        if (!$existing)
            $this->error('Ejercicio no encontrado', 404);
        if ($existing['creado_por'] !== $user['correo']) {
            $this->error('Sin permisos para eliminar este ejercicio', 403);
        }

        $this->model->delete($id);
        $this->success([], 'Ejercicio eliminado');
    }

    // ---- plan ----

    // asigna un ejercicio al plan de un usuario especifico
    // recibe usuario_id y ejercicio_id en el body JSON
    public function proAsignar(): void
    {
        $this->requireProfessional();
        $user = $this->currentUser();
        $body = $this->getJsonBody();

        $usuarioId = $body['usuario_id'] ?? null;
        $ejercicioId = $body['ejercicio_id'] ?? null;
        $notas = trim($body['notas'] ?? '') ?: null;

        if (!$usuarioId || !$ejercicioId)
            $this->error('Datos incompletos');

        require_once APP_PATH . '/models/Plan.php';
        $plan = new Plan();
        $ok = $plan->asignarEjercicio($usuarioId, $ejercicioId, $user['correo'], $notas);

        if ($ok) {
            $this->success([], 'Ejercicio asignado al plan');
        } else {
            $this->error('Error al asignar');
        }
    }

    // ---- actividad ----

    // se llama cuando el usuario hace click en "iniciar entrenamiento"
    // guarda el registro en actividad_registro para calcular calorias del dashboard
    public function logEjercicio(): void
    {
        $this->requireAuth();
        $user = $this->currentUser();
        $body = $this->getJsonBody();

        $ejercicioId = (int) ($body['ejercicio_id'] ?? 0);
        $titulo = trim($body['titulo'] ?? '');
        $calorias = (int) ($body['calorias'] ?? 0);

        if (!$ejercicioId || $calorias < 0)
            $this->error('Datos inválidos');

        require_once APP_PATH . '/models/ActividadRegistro.php';
        $model = new ActividadRegistro();
        $id = $model->logEjercicio($user['correo'], $ejercicioId, $titulo, $calorias);

        $this->success(['logged' => (bool) $id]);
    }
}
