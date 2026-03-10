<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';

    $authController = new AuthController();
    $result = $authController->login($email, $password);

    if ($result['success']) {
        // Redirigir al dashboard
        redirect('dashboard');
        exit;
    } else {
        // Redirigir al login con error
        redirect('login?error=' . urlencode($result['message']));
        exit;
    }
} else {
    // Si no es POST, redirigir al login
    redirect('login');
    exit;
}
