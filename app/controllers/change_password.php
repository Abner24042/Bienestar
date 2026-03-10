<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAuthenticated()) {
    $userId = getUserId();
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        redirect('perfil?error=' . urlencode('Todos los campos son requeridos'));
        exit;
    }
    
    if ($newPassword !== $confirmPassword) {
        redirect('perfil?error=' . urlencode('Las contraseñas no coinciden'));
        exit;
    }
    
    if (strlen($newPassword) < 6) {
        redirect('perfil?error=' . urlencode('La contraseña debe tener al menos 6 caracteres'));
        exit;
    }
    
    $userModel = new User();
    $user = $userModel->findById($userId);
    
    // Verificar contraseña actual
    if (!password_verify($currentPassword, $user['contrasena'])) {
        redirect('perfil?error=' . urlencode('Contraseña actual incorrecta'));
        exit;
    }
    
    // Cambiar contraseña
    if ($userModel->changePassword($userId, $newPassword)) {
        redirect('perfil?success=' . urlencode('Contraseña actualizada correctamente'));
    } else {
        redirect('perfil?error=' . urlencode('Error al cambiar contraseña'));
    }
} else {
    redirect('login');
}