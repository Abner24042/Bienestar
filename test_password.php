<?php
// Simular el hash que está en la base de datos
$hash_db = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

// Password que estás ingresando
$password_input = 'admin123';

// Verificar
if (password_verify($password_input, $hash_db)) {
    echo "✅ La contraseña coincide!";
} else {
    echo "❌ La contraseña NO coincide";
}

echo "<br><br>";
echo "Hash en DB: $hash_db<br>";
echo "Password probado: $password_input<br>";
echo "Hash nuevo: " . password_hash($password_input, PASSWORD_DEFAULT);
?>
```

Accede a:
```
http://localhost/a/Bienestar/test_password.php