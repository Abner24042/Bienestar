<?php
/**
 * Helpers de Validación
 */

/**
 * Validar email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validar contraseña
 * Mínimo 6 caracteres
 */
function validatePassword($password) {
    return strlen($password) >= 6;
}

/**
 * Sanitizar string
 */
function sanitizeString($string) {
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validar fecha
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Validar hora
 */
function validateTime($time) {
    return preg_match("/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/", $time);
}

/**
 * Limpiar nombre de archivo
 */
function sanitizeFilename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    return substr($filename, 0, 200);
}

/**
 * Validar archivo de imagen
 */
function validateImage($file) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['valid' => false, 'error' => 'Tipo de archivo no permitido'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'El archivo es demasiado grande (máx. 5MB)'];
    }
    
    return ['valid' => true];
}

/**
 * Generar token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}