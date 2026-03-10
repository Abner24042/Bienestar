<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    private $table = 'usuarios';
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Buscar usuario por email
     */
    public function findByEmail($email) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE correo = :email LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Error en findByEmail: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar usuario por ID
     */
    public function findById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Error en findById: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear nuevo usuario
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                     (nombre, correo, contrasena, foto, rol, area) 
                     VALUES (:nombre, :correo, :contrasena, :foto, :rol, :area)";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':correo', $data['correo']);
            $stmt->bindParam(':contrasena', $data['password']);
            
            $foto = $data['foto'] ?? null;
            $stmt->bindParam(':foto', $foto);
            
            $rol = $data['rol'] ?? 'usuario';
            $stmt->bindParam(':rol', $rol);
            
            $area = $data['area'] ?? null;
            $stmt->bindParam(':area', $area);
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log('Error en create: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar usuario
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " 
                     SET nombre = :nombre, 
                         correo = :correo,
                         foto = :foto,
                         area = :area
                     WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':correo', $data['correo']);
            $stmt->bindParam(':foto', $data['foto']);
            $stmt->bindParam(':area', $data['area']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en update: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar usuario
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en delete: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los usuarios
     */
    public function getAll() {
        try {
            $query = "SELECT id, nombre, correo, foto, rol, area, fecha 
                     FROM " . $this->table . " 
                     ORDER BY fecha DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error en getAll: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener todos los usuarios regulares (para profesionales)
     */
    public function getAllRegularUsers() {
        try {
            $query = "SELECT id, nombre, correo, foto, area, fecha
                     FROM " . $this->table . "
                     WHERE rol = 'usuario' AND activo = 1
                     ORDER BY nombre ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error en getAllRegularUsers: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar rol de usuario
     */
    public function updateRole($id, $role) {
        try {
            $query = "UPDATE " . $this->table . " SET rol = :rol WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':rol', $role);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en updateRole: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword($id, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $query = "UPDATE " . $this->table . " 
                     SET contrasena = :password 
                     WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':password', $hashedPassword);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en changePassword: ' . $e->getMessage());
            return false;
        }
    }
}