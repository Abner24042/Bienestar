<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAuthenticated()) {
    $userId = getUserId();
    $currentUser = currentUser();
    $isAdmin = isAdmin();

    // Solo administradores pueden modificar datos del perfil
    if (!$isAdmin) {
        redirect('perfil');
        exit;
    }

    $nombre = sanitizeString($_POST['nombre'] ?? '');
    $correo = sanitizeString($_POST['correo'] ?? '');
    $area   = sanitizeString($_POST['area'] ?? '');
    $peso   = isset($_POST['peso'])   && $_POST['peso']   !== '' ? (float)$_POST['peso']   : null;
    $altura = isset($_POST['altura']) && $_POST['altura'] !== '' ? (float)$_POST['altura'] : null;

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
        'area'   => $area,
        'foto'   => $currentUser['foto'],
        'peso'   => $peso,
        'altura' => $altura,
    ];

    if ($userModel->update($userId, $data)) {
        // Actualizar sesión
        $_SESSION['user_name'] = $nombre;
        $_SESSION['user_email'] = $correo;
        $_SESSION['user']['nombre'] = $nombre;
        $_SESSION['user']['correo'] = $correo;
        $_SESSION['user']['area'] = $area;

        redirect('perfil?success=' . urlencode('Perfil actualizado correctamente'));
    } else {
        redirect('perfil?error=' . urlencode('Error al actualizar perfil'));
    }
} else {
    redirect('login');
}