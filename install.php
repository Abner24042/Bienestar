<?php
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/models/User.php';

echo "<h1>Instalación de BIENIESTAR</h1>";
echo "<hr>";

try {
    $userModel = new User();
    
    // Verificar si ya existe el admin
    $existingAdmin = $userModel->findByEmail('admin@bieniestar.com');
    
    if ($existingAdmin) {
        echo "<p style='color: orange;'>⚠️ El usuario admin@bieniestar.com ya existe.</p>";
        echo "<p>Si quieres recrearlo, primero elimínalo de la base de datos.</p>";
    } else {
        // Crear usuario administrador
        $adminData = [
    'nombre' => 'Administrador',
    'correo' => 'admin@bieniestar.com',
    'password' => password_hash('admin123', PASSWORD_DEFAULT),  // ← HASHEAR AQUÍ
    'rol' => 'Administrador',
    'foto' => null,
    'area' => 'Sistemas'
];
        
        $adminId = $userModel->create($adminData);
        
        if ($adminId) {
            echo "<p style='color: green;'>✅ Usuario administrador creado exitosamente!</p>";
            echo "<p><strong>Email:</strong> admin@bieniestar.com</p>";
            echo "<p><strong>Password:</strong> admin123</p>";
            echo "<p><strong>ID:</strong> $adminId</p>";
        } else {
            echo "<p style='color: red;'>❌ Error al crear usuario administrador</p>";
        }
    }
    
    echo "<hr>";
    
    // Verificar si ya existe el usuario normal
    $existingUser = $userModel->findByEmail('usuario@test.com');
    
    if ($existingUser) {
        echo "<p style='color: orange;'>⚠️ El usuario usuario@test.com ya existe.</p>";
    } else {
        // Crear usuario normal
        $userData = [
    'nombre' => 'Usuario Prueba',
    'correo' => 'usuario@test.com',
    'password' => password_hash('usuario123', PASSWORD_DEFAULT),  // ← HASHEAR AQUÍ
    'rol' => 'usuario',
    'foto' => null,
    'area' => 'Estudiante'
];
        
        $userId = $userModel->create($userData);
        
        if ($userId) {
            echo "<p style='color: green;'>✅ Usuario normal creado exitosamente!</p>";
            echo "<p><strong>Email:</strong> usuario@test.com</p>";
            echo "<p><strong>Password:</strong> usuario123</p>";
            echo "<p><strong>ID:</strong> $userId</p>";
        } else {
            echo "<p style='color: red;'>❌ Error al crear usuario normal</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>Prueba de hash de contraseña:</h3>";
    echo "<p>Hash de 'admin123': <code>" . password_hash('admin123', PASSWORD_DEFAULT) . "</code></p>";
    echo "<p>Hash de 'usuario123': <code>" . password_hash('usuario123', PASSWORD_DEFAULT) . "</code></p>";
    
    echo "<hr>";
    echo "<p><strong>✅ Instalación completada!</strong></p>";
    echo "<p><a href='public/pages/login.php'>Ir al Login</a></p>";
    
    echo "<hr>";
    echo "<p style='color: red;'><strong>⚠️ IMPORTANTE:</strong> Elimina este archivo (install.php) después de usarlo por seguridad.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
