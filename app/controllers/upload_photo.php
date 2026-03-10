<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAuthenticated()) {
    $userId = getUserId();
    
    if (!isset($_FILES['photo'])) {
        redirect('perfil?error=' . urlencode('No se recibió ninguna foto'));
        exit;
    }
    
    // Validar imagen
    $validation = validateImage($_FILES['photo']);
    if (!$validation['valid']) {
        redirect('perfil?error=' . urlencode($validation['error']));
        exit;
    }
    
    // Subir archivo
    $upload = uploadFile($_FILES['photo'], 'perfiles');
    
    if (!$upload['success']) {
        redirect('perfil?error=' . urlencode($upload['error']));
        exit;
    }
    
    // Actualizar en la base de datos
    $userModel = new User();
    $user = $userModel->findById($userId);
    
    // Eliminar foto anterior si existe
    if ($user['foto'] && file_exists($user['foto'])) {
        deleteFile($user['foto']);
    }
    
    $data = [
        'nombre' => $user['nombre'],
        'correo' => $user['correo'],
        'area' => $user['area'],
        'foto' => $upload['url']
    ];
    
    if ($userModel->update($userId, $data)) {
        // Actualizar sesión
        $_SESSION['user']['foto'] = $upload['url'];
        
        redirect('perfil?success=' . urlencode('Foto actualizada correctamente'));
    } else {
        redirect('perfil?error=' . urlencode('Error al actualizar foto'));
    }
} else {
    redirect('login');
}