<?php
/**
 * BIENIESTAR - Router
 * Maneja las rutas limpias sin extensiones .php
 */
class Router {
    private $routes = [];
    private $basePath = '';

    public function __construct($basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }

    public function get($path, $handler) {
        $this->routes['GET'][$path] = $handler;
    }

    public function post($path, $handler) {
        $this->routes['POST'][$path] = $handler;
    }

    public function any($path, $handler) {
        $this->routes['GET'][$path] = $handler;
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch($uri, $method) {
        // Quitar query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Quitar basePath
        if ($this->basePath && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }

        $uri = '/' . trim($uri, '/');
        if ($uri === '/') $uri = '/';

        // Backward compatibility: si termina en .php, redirigir a URL limpia
        if (preg_match('/\.php$/', $uri)) {
            $clean = preg_replace('/\.php$/', '', $uri);
            $clean = str_replace('/pages/', '/', $clean);
            $clean = preg_replace('/\/index$/', '', $clean);
            // Preservar query string
            $qs = $_SERVER['QUERY_STRING'] ?? '';
            $redirect = $this->basePath . $clean;
            if ($qs) $redirect .= '?' . $qs;
            header('Location: ' . $redirect, true, 301);
            exit;
        }

        // Buscar ruta
        $handler = isset($this->routes[$method][$uri]) ? $this->routes[$method][$uri] : null;

        if ($handler === null) {
            http_response_code(404);
            include PUBLIC_PATH . '/pages/404.php';
            return;
        }

        if (is_string($handler)) {
            // Cambiar CWD al directorio del handler para que require con rutas relativas funcionen
            chdir(dirname($handler));
            include $handler;
        } elseif (is_callable($handler)) {
            call_user_func($handler);
        }
    }
}
