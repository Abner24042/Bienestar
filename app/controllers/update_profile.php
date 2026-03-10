<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAuthenticated()) {
    $userId = getUserId();
    $currentUser = currentUser();
    $isAdmin = isAdmin();

    // Solo los administradores pueden modificar nombre, correo y área
    if ($isAdmin) {
        $nombre = sanitizeString($_POST['nombre'] ?? '');
        $correo = sanitizeString($_POST['correo'] ?? '');
        $area = sanitizeString($_POST['area'] ?? '');
    } else {
        // Si no es admin, mantener los valores actuales
        $nombre = $currentUser['nombre'];
        $correo = $currentUser['correo'];
        $area = $currentUser['area'] ?? '';
    }

    if (empty($nombre) || empty($correo)) {
        redirect('perfil?error=' . urlencode('Todos los campos son requeridos'));
        exit;
    }

    if (!validateEmail($correo)) {
        redirect('perfil?error=' . urlencode('Email inválido'));
        exit;
    }

    $userModel = new User();

    $data = [
        'nombre' => $nombre,
        'correo' => $correo,
        'area' => $area,
        'foto' => $currentUser['foto']
    ];

    if ($userModel->update($userId, $data)) {
        // Actualizar sesión
        $_SESSION['user_name'] = $nombre;
        $_SESSION['user_email'] = $correo;
        $_SESSION['user']['nombre'] = $nombre;
        $_SESSION['user']['correo'] = $correo;
        $_SESSION['user']['area'] = $area;

        $successMsg = $isAdmin
            ? 'Perfil actualizado correctamente'
            : 'Solo los administradores pueden modificar datos personales';
        redirect('perfil?success=' . urlencode($successMsg));
    } else {
        redirect('perfil?error=' . urlencode('Error al actualizar perfil'));
    }
} else {
    redirect('login');
}