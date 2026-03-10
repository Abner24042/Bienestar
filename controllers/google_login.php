<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';

// Construir URL de autenticación de Google OAuth
$params = http_build_query([
    'client_id'     => $_ENV['GOOGLE_CLIENT_ID'],
    'redirect_uri'  => $_ENV['GOOGLE_REDIRECT_URI'],
    'response_type' => 'code',
    'scope'         => 'email profile',
    'access_type'   => 'offline',
    'prompt'        => 'select_account',
]);

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;

// Redirigir a Google
header('Location: ' . $authUrl);
exit;
