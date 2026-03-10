<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAuthenticated()) {
    $userId = getUserId();

    // Obtener usuario actual
    $userModel = new User();
    $user = $userModel->findById($userId);

    // Eliminar foto física si existe
    if ($user['foto'] && file_exists(PUBLIC_PATH . $user['foto'])) {
        unlink(PUBLIC_PATH . $user['foto']);
    }

    // Actualizar en la base de datos (foto = NULL)
    $data = [
        'nombre' => $user['nombre'],
        'correo' => $user['correo'],
        'area' => $user['area'],
        'foto' => null
    ];

    if ($userModel->update($userId, $data)) {
        // Actualizar sesión
        $_SESSION['user']['foto'] = null;

        redirect('perfil?success=' . urlencode('Foto eliminada correctamente'));
    } else {
        redirect('perfil?error=' . urlencode('Error al eliminar foto'));
    }
} else {
    redirect('login');
}
