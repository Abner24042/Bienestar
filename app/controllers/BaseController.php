<?php
/**
 * BIENESTAR — BaseController
 * Clase base para todos los ResourceControllers.
 * Centraliza: auth, roles, respuestas JSON, lectura de input.
 */
abstract class BaseController {

    /** Parámetros de ruta extraídos por el Router (ej: {id}) */
    protected array $params;

    public function __construct(array $params = []) {
        $this->params = $params;
        // Todos los controllers devuelven JSON por defecto
        header('Content-Type: application/json');
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    protected function requireAuth(): void {
        require_once APP_PATH . '/controllers/AuthController.php';
        if (!(new AuthController())->isAuthenticated()) {
            $this->error('No autenticado', 401);
        }
    }

    /**
     * Verifica que el usuario tenga uno de los roles dados.
     * Uso: $this->requireRole('admin')
     *      $this->requireRole('coach', 'nutriologo', 'psicologo')
     */
    protected function requireRole(string ...$roles): void {
        $this->requireAuth();
        $user = $this->currentUser();
        if (!$user || !in_array($user['rol'], $roles, true)) {
            $this->error('Sin permisos', 403);
        }
    }

    protected function requireAdmin(): void {
        $this->requireRole('Administrador');
    }

    protected function requireProfessional(): void {
        $this->requireRole('coach', 'nutriologo', 'psicologo');
    }

    // ── Respuestas JSON ───────────────────────────────────────────────────────

    /**
     * Respuesta exitosa. $data se fusiona con { success: true }.
     * Ejemplo: $this->success(['recetas' => $list], 'OK')
     */
    protected function success(array $data = [], string $message = ''): void {
        $response = ['success' => true];
        if ($message !== '') $response['message'] = $message;
        echo json_encode(array_merge($response, $data));
        exit;
    }

    /**
     * Respuesta de error. Termina la ejecución.
     */
    protected function error(string $message, int $code = 400): void {
        http_response_code($code);
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }

    // ── Lectura de input ──────────────────────────────────────────────────────

    /** Parsea el body JSON de la petición (para PUT/DELETE/POST con JSON) */
    protected function getJsonBody(): array {
        static $body = null;
        if ($body === null) {
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
        }
        return $body;
    }

    /** Parámetro de ruta (ej: {id} en /api/recetas/{id}) */
    protected function param(string $key, $default = null) {
        return $this->params[$key] ?? $default;
    }

    /** Valor de $_POST */
    protected function post(string $key, $default = null) {
        return $_POST[$key] ?? $default;
    }

    /** Convierte cadena vacía a null (útil para campos opcionales) */
    protected function toNull($value) {
        return ($value === '' || $value === null) ? null : $value;
    }

    // ── Usuario actual ────────────────────────────────────────────────────────

    protected function currentUser(): ?array {
        return $_SESSION['user'] ?? null;
    }
}
