<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Login con email y contraseña
     */
    public function login($email, $password) {
        try {
            // Buscar usuario
            $user = $this->userModel->findByEmail($email);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Credenciales incorrectas'
                ];
            }
            
            // Verificar contraseña
            if (!password_verify($password, $user['contrasena'])) {
                return [
                    'success' => false,
                    'message' => 'Credenciales incorrectas'
                ];
            }
            
            // Crear sesión
            $this->createSession($user);
            
            return [
                'success' => true,
                'message' => 'Login exitoso',
                'user' => $user
            ];
            
        } catch (Exception $e) {
            error_log('Error en login: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al iniciar sesión'
            ];
        }
    }
    
    /**
     * Registrar nuevo usuario
     */
    public function register($data) {
        try {
            // Validar datos
            if (empty($data['nombre']) || empty($data['correo']) || empty($data['password'])) {
                return [
                    'success' => false,
                    'message' => 'Todos los campos son requeridos'
                ];
            }
            
            // Verificar si el email ya existe
            if ($this->userModel->findByEmail($data['correo'])) {
                return [
                    'success' => false,
                    'message' => 'El correo ya está registrado'
                ];
            }
            
            // Hash de la contraseña
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Crear usuario
            $userId = $this->userModel->create($data);
            
            if ($userId) {
                // Obtener usuario recién creado
                $user = $this->userModel->findById($userId);
                
                // Crear sesión
                $this->createSession($user);
                
                return [
                    'success' => true,
                    'message' => 'Registro exitoso',
                    'user' => $user
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Error al crear usuario'
            ];
            
        } catch (Exception $e) {
            error_log('Error en registro: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al registrar usuario'
            ];
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000, '/');
        }
        
        session_destroy();
        
        return [
            'success' => true,
            'message' => 'Sesión cerrada'
        ];
    }
    
    /**
     * Crear sesión
     */
    private function createSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['correo'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_role'] = $user['rol'];
        $_SESSION['user'] = $user;
        $_SESSION['logged_in'] = true;
    }
    
    /**
     * Verificar si está autenticado
     */
    public function isAuthenticated() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Obtener usuario actual
     */
    public function getCurrentUser() {
        if ($this->isAuthenticated()) {
            return $_SESSION['user'];
        }
        return null;
    }
}