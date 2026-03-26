<?php
/**
 * Verificar si el usuario está autenticado
 */
function isAuthenticated()
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Verificar si el usuario es administrador
 */
function requireAdmin()
{
    requireAuth();

    if (!isAdmin()) {
        redirect('dashboard');
        exit;
    }
}

/**
 * Verificar si el usuario es admin
 */
function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Administrador';
}

/**
 * Verificar si el usuario es profesional (coach, nutriologo, psicologo)
 */
function isProfessional()
{
    $professionalRoles = ['coach', 'nutriologo', 'psicologo'];
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], $professionalRoles);
}

/**
 * Requerir rol profesional
 */
function requireProfessional()
{
    requireAuth();
    if (!isProfessional()) {
        redirect('dashboard');
        exit;
    }
}

/**
 * Obtener etiqueta legible del rol
 */
function getRoleLabel($role)
{
    $labels = [
        'usuario' => 'Usuario',
        'Administrador' => 'Administrador',
        'coach' => 'Coach',
        'nutriologo' => 'Nutriologo',
        'psicologo' => 'Psicologo'
    ];
    return $labels[$role] ?? $role;
}

/**
 * Obtener ID del usuario actual
 */
function getUserId()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Obtener email del usuario actual
 */
function getUserEmail()
{
    return $_SESSION['user_email'] ?? null;
}

/**
 * Obtener nombre del usuario actual
 */
function getUserName()
{
    return $_SESSION['user_name'] ?? null;
}

/**
 * Obtener usuario actual completo
 */
function currentUser()
{
    return $_SESSION['user'] ?? null;
}

/**
 * Verificar si el usuario está autenticado
 */
function requireAuth()
{
    if (!isAuthenticated()) {
        redirect('login');
        exit;
    }
}

/**
 * Redireccionar usando rutas limpias
 */
function redirect($path)
{
    // Si ya es una URL absoluta, usar directamente
    if (strpos($path, 'http') === 0) {
        header('Location: ' . $path);
        exit;
    }
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}
