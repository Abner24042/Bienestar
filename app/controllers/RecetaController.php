<?php
/**
 * BIENESTAR — RecetaController
 *
 * Consolida 9 archivos sueltos en un solo controlador:
 *   get_recetas.php, admin_get_recetas.php, admin_save_receta.php,
 *   admin_delete_receta.php, pro_get_recetas.php, pro_save_receta.php,
 *   pro_delete_receta.php, pro_approve_receta.php, pro_get_pending_recetas.php
 *
 * Rutas en index.php:
 *   GET    /api/recetas                        → index()
 *   GET    /api/admin/recetas                  → adminIndex()
 *   POST   /api/admin/recetas                  → adminStore()
 *   POST   /api/admin/recetas/{id}/toggle      → adminToggle()
 *   DELETE /api/admin/recetas/{id}             → adminDestroy()
 *   GET    /api/pro/recetas                    → proIndex()
 *   GET    /api/pro/recetas/pending            → proPending()
 *   POST   /api/pro/recetas                    → proStore()
 *   DELETE /api/pro/recetas/{id}               → proDestroy()
 *   POST   /api/pro/recetas/{id}/approve       → proApprove()
 */

require_once APP_PATH . '/models/Receta.php';
require_once APP_PATH . '/helpers/file_helper.php';

class RecetaController extends BaseController {

    private Receta $model;

    public function __construct(array $params = []) {
        parent::__construct($params);
        $this->model = new Receta();
    }

    // ── USUARIO ───────────────────────────────────────────────────────────────

    /** GET /api/recetas — recetas activas (cualquier usuario autenticado) */
    public function index(): void {
        $this->requireAuth();
        $this->success(['recetas' => $this->model->getActive()]);
    }

    // ── ADMIN ─────────────────────────────────────────────────────────────────

    /** GET /api/admin/recetas */
    public function adminIndex(): void {
        $this->requireAdmin();
        $this->success(['recetas' => $this->model->getAll()]);
    }

    /**
     * POST /api/admin/recetas
     * Crea o actualiza según si viene receta_id en el body.
     */
    public function adminStore(): void {
        $this->requireAdmin();

        $titulo = trim($this->post('titulo', ''));
        if ($titulo === '') $this->error('El título es requerido');

        // Acepta 'receta_id' (nuevo) o 'id' (legacy admin-recetas.js)
        $id = (int)($this->post('receta_id', 0) ?: $this->post('id', 0));

        $imagen = null;
        if (!empty($_FILES['imagen']['name'])) {
            $imagen = uploadFile('imagen', 'recetas');
            if (!$imagen) $this->error('Error al subir la imagen');
        }

        $data = [
            'titulo'             => $titulo,
            'descripcion'        => $this->toNull($this->post('descripcion')),
            'ingredientes'       => $this->toNull($this->post('ingredientes')),
            'instrucciones'      => $this->toNull($this->post('instrucciones')),
            'tiempo_preparacion' => $this->toNull($this->post('tiempo_preparacion')),
            'calorias'           => $this->toNull($this->post('calorias')),
            'categoria'          => $this->post('categoria', 'general'),
            'activo'             => 1,
        ];

        if ($id) {
            $existing = $this->model->findById($id);
            if (!$existing) $this->error('Receta no encontrada', 404);

            $data['imagen']    = $imagen ?? $existing['imagen'];
            $data['creado_por'] = $this->post('creado_por') ?: $existing['creado_por'];
            $this->model->update($id, $data);
            $this->success([], 'Receta actualizada');
        } else {
            $data['imagen']    = $imagen;
            $data['creado_por'] = $this->post('creado_por');
            $newId = $this->model->create($data);
            if (!$newId) $this->error('Error al guardar la receta');
            $this->success(['id' => $newId], 'Receta creada');
        }
    }

    /** POST /api/admin/recetas/{id}/toggle */
    public function adminToggle(): void {
        $this->requireAdmin();
        $id   = (int)$this->param('id');
        $body = $this->getJsonBody();
        if (!$id || !isset($body['activo'])) $this->error('Datos inválidos');

        $activo = (int)$body['activo'];
        $this->model->toggleActive($id, $activo);
        $this->success([], $activo ? 'Receta activada' : 'Receta desactivada');
    }

    /** DELETE /api/admin/recetas/{id} */
    public function adminDestroy(): void {
        $this->requireAdmin();
        $id = (int)$this->param('id');
        if (!$id) $this->error('ID requerido');
        if (!$this->model->findById($id)) $this->error('Receta no encontrada', 404);
        $this->model->delete($id);
        $this->success([], 'Receta eliminada');
    }

    // ── PROFESIONAL ───────────────────────────────────────────────────────────

    /** GET /api/pro/recetas */
    public function proIndex(): void {
        $this->requireProfessional();
        $this->success(['recetas' => $this->model->getForProfessional()]);
    }

    /** GET /api/pro/recetas/pending */
    public function proPending(): void {
        $this->requireRole('nutriologo');
        $this->success(['recetas' => $this->model->getAutoUnapproved()]);
    }

    /**
     * POST /api/pro/recetas
     * Crea o actualiza con verificación de propiedad.
     */
    public function proStore(): void {
        $this->requireProfessional();
        $user = $this->currentUser();

        $titulo = trim($this->post('titulo', ''));
        if ($titulo === '') $this->error('El título es requerido');

        // Acepta 'receta_id' (nuevo) o 'pro_receta_id' (campo del form en panel.php)
        $id = (int)($this->post('receta_id', 0) ?: $this->post('pro_receta_id', 0));

        $imagen = null;
        if (!empty($_FILES['imagen']['name'])) {
            $imagen = uploadFile('imagen', 'recetas');
            if (!$imagen) $this->error('Error al subir la imagen');
        }

        $data = [
            'titulo'             => $titulo,
            'descripcion'        => $this->toNull($this->post('descripcion')),
            'ingredientes'       => $this->toNull($this->post('ingredientes')),
            'instrucciones'      => $this->toNull($this->post('instrucciones')),
            'tiempo_preparacion' => $this->toNull($this->post('tiempo_preparacion')),
            'calorias'           => $this->toNull($this->post('calorias')),
            'categoria'          => $this->post('categoria', 'general'),
        ];

        if ($id) {
            $existing = $this->model->findById($id);
            if (!$existing) $this->error('Receta no encontrada', 404);
            if ($existing['creado_por'] !== $user['correo']) {
                $this->error('Sin permisos para editar esta receta', 403);
            }
            $data['imagen'] = $imagen ?? $existing['imagen'];
            $this->model->update($id, $data);
            $this->success([], 'Receta actualizada');
        } else {
            $data['imagen']    = $imagen;
            $data['creado_por'] = $user['correo'];
            $newId = $this->model->create($data);
            if (!$newId) $this->error('Error al guardar la receta');
            $this->success(['id' => $newId], 'Receta creada');
        }
    }

    /** DELETE /api/pro/recetas/{id} */
    public function proDestroy(): void {
        $this->requireProfessional();
        $user = $this->currentUser();
        $id   = (int)$this->param('id');
        if (!$id) $this->error('ID requerido');

        $existing = $this->model->findById($id);
        if (!$existing) $this->error('Receta no encontrada', 404);

        $isOwner   = $existing['creado_por'] === $user['correo'];
        $isPending = $existing['auto_generada'] && !$existing['aprobada']
                     && $user['rol'] === 'nutriologo';

        if (!$isOwner && !$isPending) {
            $this->error('Sin permisos para eliminar esta receta', 403);
        }

        $this->model->delete($id);
        $this->success([], 'Receta eliminada');
    }

    /** POST /api/pro/recetas/{id}/approve */
    public function proApprove(): void {
        $this->requireRole('nutriologo');
        $id = (int)$this->param('id');
        if (!$id) $this->error('ID requerido');
        if (!$this->model->findById($id)) $this->error('Receta no encontrada', 404);
        $this->model->aprobar($id);
        $this->success([], 'Receta aprobada');
    }
}
