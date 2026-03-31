<?php

require_once APP_PATH . '/models/Receta.php';
require_once APP_PATH . '/helpers/file_helper.php';

class RecetaController extends BaseController
{

    private Receta $model;

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->model = new Receta();
    }

    // ---- usuario normal ----

    // cualquier usuario con sesion puede ver las recetas activas
    public function index(): void
    {
        $this->requireAuth();
        $this->success(['recetas' => $this->model->getActive()]);
    }

    // ---- admin ----

    public function adminIndex(): void
    {
        $this->requireAdmin();
        $this->success(['recetas' => $this->model->getAll()]);
    }

    // crea o actualiza segun si viene id en el body
    // el FormData del front manda el campo como 'id' (admin) o 'receta_id' (pro), los dos funcionan
    public function adminStore(): void
    {
        $this->requireAdmin();

        $titulo = trim($this->post('titulo', ''));
        if ($titulo === '')
            $this->error('El título es requerido');

        $id = (int) ($this->post('receta_id', 0) ?: $this->post('id', 0));

        $imagen = null;
        if (!empty($_FILES['imagen']['name'])) {
            $imagen = uploadFile('imagen', 'recetas');
            if (!$imagen)
                $this->error('Error al subir la imagen');
        }

        $data = [
            'titulo' => $titulo,
            'descripcion' => $this->toNull($this->post('descripcion')),
            'ingredientes' => $this->toNull($this->post('ingredientes')),
            'instrucciones' => $this->toNull($this->post('instrucciones')),
            'tiempo_preparacion' => $this->toNull($this->post('tiempo_preparacion')),
            'calorias' => $this->toNull($this->post('calorias')),
            'categoria' => $this->post('categoria', 'general'),
            'activo' => 1,
        ];

        if ($id) {
            $existing = $this->model->findById($id);
            if (!$existing)
                $this->error('Receta no encontrada', 404);

            // si no se subio imagen nueva conservamos la que ya tenia
            $data['imagen'] = $imagen ?? $existing['imagen'];
            $data['creado_por'] = $this->post('creado_por') ?: $existing['creado_por'];
            $this->model->update($id, $data);
            $this->success([], 'Receta actualizada');
        } else {
            $data['imagen'] = $imagen;
            $data['creado_por'] = $this->post('creado_por');
            $newId = $this->model->create($data);
            if (!$newId)
                $this->error('Error al guardar la receta');
            $this->success(['id' => $newId], 'Receta creada');
        }
    }

    // activa o desactiva la receta segun el valor de 'activo' que llega en el body JSON
    public function adminToggle(): void
    {
        $this->requireAdmin();
        $id = (int) $this->param('id');
        $body = $this->getJsonBody();
        if (!$id || !isset($body['activo']))
            $this->error('Datos inválidos');

        $activo = (int) $body['activo'];
        $this->model->toggleActive($id, $activo);
        $this->success([], $activo ? 'Receta activada' : 'Receta desactivada');
    }

    public function adminDestroy(): void
    {
        $this->requireAdmin();
        $id = (int) $this->param('id');
        if (!$id)
            $this->error('ID requerido');
        if (!$this->model->findById($id))
            $this->error('Receta no encontrada', 404);
        $this->model->delete($id);
        $this->success([], 'Receta eliminada');
    }

    // ---- profesional ----

    public function proIndex(): void
    {
        $this->requireProfessional();
        $this->success(['recetas' => $this->model->getForProfessional()]);
    }

    // solo el nutriologo ve las recetas generadas por la PI que estan pendientes de aprobar
    public function proPending(): void
    {
        $this->requireRole('nutriologo');
        $this->success(['recetas' => $this->model->getAutoUnapproved()]);
    }

    // el profesional solo puede editar recetas que el mismo creo
    // si el creado_por no coincide con su correo devuelve 403
    public function proStore(): void
    {
        $this->requireProfessional();
        $user = $this->currentUser();

        $titulo = trim($this->post('titulo', ''));
        if ($titulo === '')
            $this->error('El título es requerido');

        // el form del panel usa 'pro_receta_id', aceptamos los dos por si acaso
        $id = (int) ($this->post('receta_id', 0) ?: $this->post('pro_receta_id', 0));

        $imagen = null;
        if (!empty($_FILES['imagen']['name'])) {
            $imagen = uploadFile('imagen', 'recetas');
            if (!$imagen)
                $this->error('Error al subir la imagen');
        }

        $data = [
            'titulo' => $titulo,
            'descripcion' => $this->toNull($this->post('descripcion')),
            'ingredientes' => $this->toNull($this->post('ingredientes')),
            'instrucciones' => $this->toNull($this->post('instrucciones')),
            'tiempo_preparacion' => $this->toNull($this->post('tiempo_preparacion')),
            'calorias' => $this->toNull($this->post('calorias')),
            'categoria' => $this->post('categoria', 'general'),
        ];

        if ($id) {
            $existing = $this->model->findById($id);
            if (!$existing)
                $this->error('Receta no encontrada', 404);
            if ($existing['creado_por'] !== $user['correo']) {
                $this->error('Sin permisos para editar esta receta', 403);
            }
            $data['imagen'] = $imagen ?? $existing['imagen'];
            $this->model->update($id, $data);
            $this->success([], 'Receta actualizada');
        } else {
            $data['imagen'] = $imagen;
            $data['creado_por'] = $user['correo'];
            $newId = $this->model->create($data);
            if (!$newId)
                $this->error('Error al guardar la receta');
            $this->success(['id' => $newId], 'Receta creada');
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
            $this->error('Receta no encontrada', 404);

        // puede borrar si es el dueño, o si es nutriologo y la receta esta pendiente de aprobar
        $isOwner = $existing['creado_por'] === $user['correo'];
        $isPending = $existing['auto_generada'] && !$existing['aprobada']
            && $user['rol'] === 'nutriologo';

        if (!$isOwner && !$isPending) {
            $this->error('Sin permisos para eliminar esta receta', 403);
        }

        $this->model->delete($id);
        $this->success([], 'Receta eliminada');
    }

    // el nutriologo aprueba una receta generada por la API para que se quede permanente
    public function proApprove(): void
    {
        $this->requireRole('nutriologo');
        $id = (int) $this->param('id');
        if (!$id)
            $this->error('ID requerido');
        if (!$this->model->findById($id))
            $this->error('Receta no encontrada', 404);
        $this->model->aprobar($id);
        $this->success([], 'Receta aprobada');
    }
}

//este codigo fue echo para poder bajar la cantidad de archivos de el proyecto y para que no tuviera el DRY, profe si ve esto 
//sigue en proceso de ser terminado para hacerlo como a usted le gustaria :D 