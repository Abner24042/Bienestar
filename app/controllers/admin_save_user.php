<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
if (!$authController->isAuthenticated() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Datos invalidos');
    }

    $userModel = new User();
    $validRoles = ['usuario', 'Administrador', 'coach', 'nutriologo', 'psicologo'];

    if (!empty($data['rol']) && !in_array($data['rol'], $validRoles)) {
        throw new Exception('Rol no valido');
    }

    if (!empty($data['id'])) {
        // Actualizar usuario existente
        $existing = $userModel->findById($data['id']);
        if (!$existing) {
            throw new Exception('Usuario no encontrado');
        }

        $updateData = [
            'nombre' => $data['nombre'] ?? $existing['nombre'],
            'correo' => $data['correo'] ?? $existing['correo'],
            'foto' => $existing['foto'],
            'area' => $data['area'] ?? $existing['area']
        ];
        $userModel->update($data['id'], $updateData);

        if (!empty($data['rol'])) {
            $userModel->updateRole($data['id'], $data['rol']);
        }
        if (!empty($data['password'])) {
            $userModel->changePassword($data['id'], $data['password']);
        }

        echo json_encode(['success' => true, 'message' => 'Usuario actualizado']);
    } else {
        // Crear nuevo usuario
        if (empty($data['nombre']) || empty($data['correo'])) {
            throw new Exception('Nombre y correo son requeridos');
        }
        if (empty($data['password'])) {
            throw new Exception('Contrasena requerida para nuevo usuario');
        }

        $existing = $userModel->findByEmail($data['correo']);
        if ($existing) {
            throw new Exception('El correo ya esta registrado');
        }

        $createData = [
            'nombre' => $data['nombre'],
            'correo' => $data['correo'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'rol' => $data['rol'] ?? 'usuario',
            'area' => $data['area'] ?? null
        ];

        $userId = $userModel->create($createData);
        if ($userId) {
            echo json_encode(['success' => true, 'message' => 'Usuario creado', 'userId' => $userId]);
        } else {
            throw new Exception('Error al crear usuario');
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
