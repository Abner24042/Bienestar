<?php
// clase base que heredan todos los controladores nuevos del proyecto
// centraliza el auth, validacion de roles y las respuestas JSON para no repetir lo mismo en cada archivo
// si cambias algo aqui se afecta TODOS los controladores que hereden de esta clase, cuidado con eso

abstract class BaseController
{

    // parametros extraidos de la URL, ej: si la ruta es /api/recetas/{id} aqui viene ['id' => '5']
    protected array $params;

    public function __construct(array $params = [])
    {
        $this->params = $params;
        // todos los endpoints son API y regresan JSON, lo forzamos desde el constructor
        // asi no hay que poner el header en cada controlador individualmente
        header('Content-Type: application/json');
    }

    // ---- verificacion de sesion y roles ----

    // si el usuario no tiene sesion activa devuelve 401 y termina la ejecucion
    protected function requireAuth(): void
    {
        require_once APP_PATH . '/controllers/AuthController.php';
        if (!(new AuthController())->isAuthenticated()) {
            $this->error('No autenticado', 401);
        }
    }

    // verifica que el usuario tenga alguno de los roles que se pasan como argumento
    // usa el operador splat (...$roles) para poder pasar multiples roles: requireRole('coach', 'nutriologo')
    // lee el rol de $_SESSION['user']['rol'] que se guarda cuando hace login
    protected function requireRole(string ...$roles): void
    {
        $this->requireAuth();
        $user = $this->currentUser();
        if (!$user || !in_array($user['rol'], $roles, true)) {
            $this->error('Sin permisos', 403);
        }
    }

    protected function requireAdmin(): void
    {
        // importante: en la base de datos el rol se guarda como 'Administrador' con A mayuscula
        // no como 'admin' en minusculas - eso rompio todo cuando lo puse mal la primera vez
        $this->requireRole('Administrador');
    }

    protected function requireProfessional(): void
    {
        $this->requireRole('coach', 'nutriologo', 'psicologo');
    }

    // ---- respuestas JSON ----

    // manda respuesta exitosa, mezcla $data con {success: true} y termina
    // el exit() es necesario para que no se siga ejecutando codigo despues de responder
    protected function success(array $data = [], string $message = ''): void
    {
        $response = ['success' => true];
        if ($message !== '')
            $response['message'] = $message;
        echo json_encode(array_merge($response, $data));
        exit;
    }

    // manda respuesta de error con el codigo HTTP correspondiente
    protected function error(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }

    // ---- lectura de datos entrantes ----

    // lee el body de la peticion como JSON (para POST/PUT con Content-Type: application/json)
    // usa static para cachearlo y no leer php://input mas de una vez por request
    protected function getJsonBody(): array
    {
        static $body = null;
        if ($body === null) {
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
        }
        return $body;
    }

    // lee un parametro de la URL definido con {keys} en la ruta
    protected function param(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    // lee un campo de $_POST (para formularios y FormData)
    protected function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    // convierte string vacio a null - util para campos opcionales
    // sin esto los campos vacios del form se guardan como "" en la BD en vez de NULL
    protected function toNull($value)
    {
        return ($value === '' || $value === null) ? null : $value;
    }

    // regresa el arreglo del usuario actual desde la sesion
    protected function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }
}
