<?php
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/models/User.php';

echo "<h1>Instalación de BIENIESTAR</h1>";
echo "<hr>";

function instalarUsuario($userModel, $nombre, $correo, $password, $rol, $area) {
    // Si ya existe, se borra y se vuelve a crear con el hash correcto
    $existente = $userModel->findByEmail($correo);
    if ($existente) {
        $userModel->delete($existente['id']);
        echo "<p style='color: #888;'>🔄 Usuario <strong>$correo</strong> ya existía — eliminado para recrearlo con hash correcto.</p>";
    }

    $id = $userModel->create([
        'nombre'   => $nombre,
        'correo'   => $correo,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'rol'      => $rol,
        'foto'     => null,
        'area'     => $area,
    ]);

    if ($id) {
        echo "<p style='color: green;'>✅ <strong>$correo</strong> creado correctamente.</p>";
        echo "<p>&nbsp;&nbsp;&nbsp;Contraseña: <strong>$password</strong></p>";
    } else {
        echo "<p style='color: red;'>❌ Error al crear <strong>$correo</strong></p>";
    }
}

try {
    $userModel = new User();

    instalarUsuario($userModel, 'Administrador', 'admin@bieniestar.com',  'admin123',   'admin',  'Sistemas');
    echo "<hr>";
    instalarUsuario($userModel, 'Usuario Prueba', 'usuario@test.com',     'usuario123', 'usuario', 'Estudiante');

    echo "<hr>";
    echo "<p style='color: green;'><strong>✅ Listo. Ya puedes iniciar sesión.</strong></p>";
    echo "<p><a href='public/'>Ir al inicio</a></p>";
    echo "<hr>";
    echo "<p style='color: red;'><strong>⚠️ Borra este archivo (install.php) después de usarlo.</strong></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
