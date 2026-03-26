<?php
/**
 * BIENESTAR — Router
 *
 * Cambios respecto a la versión anterior:
 *  - Añadidos: put(), delete()
 *  - Soporte para rutas con parámetros: /api/recetas/{id}
 *  - Soporte para handlers tipo 'Controller@method'
 *  - _method override para formularios HTML (PUT/DELETE)
 *  - Toda la lógica de inclusión de archivos sigue igual (100 % compatible)
 */
class Router {

    private array  $routes   = [];
    private string $basePath = '';

    public function __construct(string $basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }

    // ── Registro de rutas ──────────────────────────────────────────────────────

    public function get(string $path, $handler): void {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, $handler): void {
        $this->add('POST', $path, $handler);
    }

    public function put(string $path, $handler): void {
        $this->add('PUT', $path, $handler);
    }

    public function delete(string $path, $handler): void {
        $this->add('DELETE', $path, $handler);
    }

    public function any(string $path, $handler): void {
        foreach (['GET', 'POST', 'PUT', 'DELETE'] as $m) {
            $this->add($m, $path, $handler);
        }
    }

    private function add(string $method, string $path, $handler): void {
        $this->routes[$method][$path] = $handler;
    }

    // ── Dispatch ───────────────────────────────────────────────────────────────

    public function dispatch(string $uri, string $method): void {
        // Quitar query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Quitar basePath
        if ($this->basePath && str_starts_with($uri, $this->basePath)) {
            $uri = substr($uri, strlen($this->basePath));
        }

        $uri = '/' . trim($uri, '/');
        if ($uri === '') $uri = '/';

        // Backward compat: .php → redirect 301
        if (preg_match('/\.php$/', $uri)) {
            $clean = preg_replace('/\.php$/', '', $uri);
            $clean = str_replace('/pages/', '/', $clean);
            $clean = preg_replace('/\/index$/', '', $clean) ?: '/';
            $qs    = $_SERVER['QUERY_STRING'] ?? '';
            $loc   = $this->basePath . $clean . ($qs ? "?$qs" : '');
            header('Location: ' . $loc, true, 301);
            exit;
        }

        // _method override: permite PUT/DELETE desde formularios HTML
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper($_POST['_method']);
            if (in_array($override, ['PUT', 'DELETE', 'PATCH'], true)) {
                $method = $override;
            }
        }

        // Buscar ruta (exacta → paramétrica)
        $match = $this->match($uri, $method);

        if ($match === null) {
            http_response_code(404);
            include PUBLIC_PATH . '/pages/404.php';
            return;
        }

        // Exponer params a código legacy que no use BaseController
        $GLOBALS['_ROUTE_PARAMS'] = $match['params'];

        $this->handle($match['handler'], $match['params']);
    }

    // ── Matching ───────────────────────────────────────────────────────────────

    private function match(string $uri, string $method): ?array {
        $routes = $this->routes[$method] ?? [];

        // 1. Coincidencia exacta (comportamiento original)
        if (isset($routes[$uri])) {
            return ['handler' => $routes[$uri], 'params' => []];
        }

        // 2. Rutas con parámetros: /api/recetas/{id}
        foreach ($routes as $pattern => $handler) {
            if (!str_contains($pattern, '{')) continue;

            // Convertir {param} en grupo de captura nombrado
            $regex = preg_replace(
                '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
                '(?P<$1>[^/]+)',
                $pattern
            );

            if (preg_match('#^' . $regex . '$#', $uri, $m)) {
                // Extraer solo las claves con nombre (no índices numéricos)
                $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
                return ['handler' => $handler, 'params' => $params];
            }
        }

        return null;
    }

    // ── Ejecución del handler ──────────────────────────────────────────────────

    private function handle($handler, array $params): void {
        // Callable (closure)
        if (is_callable($handler)) {
            call_user_func($handler, $params);
            return;
        }

        if (!is_string($handler)) return;

        // 'RecetaController@adminIndex' — nuevo estilo
        if (str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $file = APP_PATH . '/controllers/' . $class . '.php';

            if (!file_exists($file)) {
                http_response_code(500);
                error_log("[Router] Controller no encontrado: $file");
                echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
                return;
            }

            require_once APP_PATH . '/controllers/BaseController.php';
            require_once $file;

            (new $class($params))->$method();
            return;
        }

        // '/path/to/file.php' — estilo original (100 % compatible)
        chdir(dirname($handler));
        include $handler;
    }
}
