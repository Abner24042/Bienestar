<?php
// el router - recibe cada peticion HTTP y decide a que archivo o controlador mandarla
// es basicamente el cerebro del proyecto, si algo no carga de 10/10 es culpa de aqui
// agregamos soporte para rutas con parametros tipo {id} y el formato Controlador@metodo
class Router
{

    private array $routes = [];
    private string $basePath = '';

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    // estos metodos registran rutas segun el verbo HTTP
    // get = solo leer datos, post = enviar datos, put = actualizar, delete = borrar
    // la diferencia importa porque el mismo /api/recetas puede hacer cosas distintas
    // segun si es GET (listar) o POST (crear) - eso es REST basicamente

    public function get(string $path, $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function put(string $path, $handler): void
    {
        $this->add('PUT', $path, $handler);
    }

    public function delete(string $path, $handler): void
    {
        $this->add('DELETE', $path, $handler);
    }

    // any = acepta cualquier metodo, lo uso para logout nada mas
    public function any(string $path, $handler): void
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE'] as $m) {
            $this->add($m, $path, $handler);
        }
    }

    // guarda la ruta en un arreglo indexado por metodo y path
    // ojo: si defines la misma ruta 2 veces, la segunda pisa a la primera
    // tarde como 2 horas en encontrar ese bug, no volver a hacer eso
    private function add(string $method, string $path, $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    // dispatch analiza la URL que llego y ejecuta lo que corresponde
    public function dispatch(string $uri, string $method): void
    {
        // quitamos el query string (?page=1 etc) porque el router no lo necesita
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // si el proyecto esta en un subfolder (ej /Bienestar/) lo quitamos del inicio
        // para que las rutas en index.php queden como /dashboard y no /Bienestar/dashboard
        if ($this->basePath && str_starts_with($uri, $this->basePath)) {
            $uri = substr($uri, strlen($this->basePath));
        }

        $uri = '/' . trim($uri, '/');
        if ($uri === '')
            $uri = '/';

        // si alguien llego con .php al final lo redirigimos a la version limpia (301)
        // esto es para no romper bookmarks viejos que guardaron la URL con extension
        if (preg_match('/\.php$/', $uri)) {
            $clean = preg_replace('/\.php$/', '', $uri);
            $clean = str_replace('/pages/', '/', $clean);
            $clean = preg_replace('/\/index$/', '', $clean) ?: '/';
            $qs = $_SERVER['QUERY_STRING'] ?? '';
            $loc = $this->basePath . $clean . ($qs ? "?$qs" : '');
            header('Location: ' . $loc, true, 301);
            exit;
        }

        // los formularios HTML solo soportan GET y POST, nunca DELETE ni PUT
        // con este truco mandamos un campo oculto _method=DELETE y aqui lo leemos
        // y sobreescribimos el metodo real - la neta no lo iba a usar pero alfin sirvio
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper($_POST['_method']);
            if (in_array($override, ['PUT', 'DELETE', 'PATCH'], true)) {
                $method = $override;
            }
        }

        $match = $this->match($uri, $method);

        if ($match === null) {
            http_response_code(404);
            include PUBLIC_PATH . '/pages/404.php';
            return;
        }

        // guardamos los params en global para que el codigo viejo (sin BaseController) tambien pueda usarlos
        $GLOBALS['_ROUTE_PARAMS'] = $match['params'];

        $this->handle($match['handler'], $match['params']);
    }

    // busca cual ruta registrada coincide con la URL que llego
    private function match(string $uri, string $method): ?array
    {
        $routes = $this->routes[$method] ?? [];

        // primero intenta coincidencia exacta, es mas rapido y cubre el 90% de los casos
        if (isset($routes[$uri])) {
            return ['handler' => $routes[$uri], 'params' => []];
        }

        // si no encontro exacta, revisa las rutas con {parametros}
        // convierte /api/recetas/{id} en un regex y ve si hace match con la URL real
        foreach ($routes as $pattern => $handler) {
            if (!str_contains($pattern, '{'))
                continue;

            // esto transforma {id} en (?P<id>[^/]+) que es un grupo de captura con nombre en regex
            // el (?P<nombre>...) permite recuperar el valor por nombre despues con $m['id']
            $regex = preg_replace(
                '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
                '(?P<$1>[^/]+)',
                $pattern
            );

            if (preg_match('#^' . $regex . '$#', $uri, $m)) {
                // preg_match devuelve tanto indices numericos como con nombre, solo queremos los nombres
                $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
                return ['handler' => $handler, 'params' => $params];
            }
        }

        return null;
    }

    // ejecuta el handler que encontro match
    private function handle($handler, array $params): void
    {
        // si es un closure/funcion anonima la llama directo
        if (is_callable($handler)) {
            call_user_func($handler, $params);
            return;
        }

        if (!is_string($handler))
            return;

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


        chdir(dirname($handler));
        include $handler;
    }
}
