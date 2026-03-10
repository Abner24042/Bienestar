<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/models/User.php';

// Verificar código de autorización
if (!isset($_GET['code'])) {
    redirect('login?error=' . urlencode('Error al autenticar con Google'));
    exit;
}

try {
    // 1. Intercambiar código por token de acceso
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'code'          => $_GET['code'],
            'client_id'     => $_ENV['GOOGLE_CLIENT_ID'],
            'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'],
            'redirect_uri'  => $_ENV['GOOGLE_REDIRECT_URI'],
            'grant_type'    => 'authorization_code',
        ]),
    ]);
    $tokenResponse = curl_exec($ch);
    curl_close($ch);

    $token = json_decode($tokenResponse, true);

    if (isset($token['error'])) {
        throw new Exception('Error al obtener token: ' . $token['error']);
    }

    // 2. Obtener información del usuario con el access_token
    $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token['access_token']],
    ]);
    $userResponse = curl_exec($ch);
    curl_close($ch);

    $google_account_info = json_decode($userResponse, true);

    if (!isset($google_account_info['email'])) {
        throw new Exception('No se pudo obtener información del usuario');
    }

    $email = $google_account_info['email'];
    $name = $google_account_info['name'];
    $picture = $google_account_info['picture'] ?? null;

    // Mejorar calidad de la imagen de Google (cambiar s96-c a s1024-c)
    if ($picture && strpos($picture, 'googleusercontent.com') !== false) {
        $picture = preg_replace('/=s\d+-c/', '=s1024-c', $picture);
    }

    // Buscar o crear usuario
    $userModel = new User();
    $user = $userModel->findByEmail($email);

    if (!$user) {
        // Crear nuevo usuario
        $userData = [
            'nombre' => $name,
            'correo' => $email,
            'foto' => $picture,
            'password' => password_hash(uniqid(), PASSWORD_DEFAULT),
            'rol' => 'usuario'
        ];

        $userId = $userModel->create($userData);
        $user = $userModel->findById($userId);
    } else {
        // Actualizar foto si cambió o si es necesario mejorar la calidad
        $needsUpdate = false;
        $currentPicture = $user['foto'];

        // Verificar si la foto actual tiene baja resolución
        if ($currentPicture && strpos($currentPicture, 'googleusercontent.com') !== false) {
            if (preg_match('/=s\d+-c/', $currentPicture)) {
                // Actualizar a alta resolución si no está ya
                if (!preg_match('/=s1024-c/', $currentPicture)) {
                    $needsUpdate = true;
                }
            }
        }

        // Actualizar si la foto cambió o necesita mejor resolución
        if ($user['foto'] !== $picture || $needsUpdate) {
            $userModel->update($user['id'], [
                'nombre' => $user['nombre'],
                'correo' => $user['correo'],
                'foto' => $picture,
                'area' => $user['area']
            ]);
            $user['foto'] = $picture;
        }
    }

    // Crear sesión
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['correo'];
    $_SESSION['user_name'] = $user['nombre'];
    $_SESSION['user_role'] = $user['rol'];
    $_SESSION['user'] = $user;
    $_SESSION['logged_in'] = true;
    $_SESSION['login_method'] = 'google';
    $_SESSION['google_token'] = $token;

    // Redirigir al dashboard
    redirect('dashboard');
    exit;

} catch (Exception $e) {
    error_log('Error en Google OAuth: ' . $e->getMessage());
    redirect('login?error=' . urlencode('Error al iniciar sesión con Google'));
}
