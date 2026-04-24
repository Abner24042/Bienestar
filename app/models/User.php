<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    private $table = 'usuarios';
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->ensureColumns();
    }

    private function ensureColumns() {
        $cols = [
            'peso'        => 'DECIMAL(5,2) DEFAULT NULL',
            'altura'      => 'DECIMAL(4,2) DEFAULT NULL',
            'login_count' => 'INT DEFAULT 0',
        ];
        foreach ($cols as $col => $def) {
            try {
                $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN {$col} {$def}");
            } catch (PDOException $e) {
                // Column already exists — ignore
            }
        }
        $this->ensureHistorialSaludTable();
    }

    public function incrementLoginCount($id) {
        try {
            $this->db->prepare("UPDATE {$this->table} SET login_count = COALESCE(login_count, 0) + 1 WHERE id = :id")
                     ->execute([':id' => $id]);
            $stmt = $this->db->prepare("SELECT login_count FROM {$this->table} WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('incrementLoginCount: ' . $e->getMessage());
            return 1;
        }
    }

    private function ensureHistorialSaludTable() {
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS historial_salud (
                id              INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id      INT NOT NULL,
                profesional_email VARCHAR(255),
                peso            DECIMAL(5,2),
                altura          DECIMAL(4,2),
                imc             DECIMAL(4,1),
                fecha           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY idx_usuario (usuario_id)
            )");
        } catch (PDOException $e) {}
    }

    public function registrarSalud($usuarioId, $profesionalEmail, $peso, $altura, $imc) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO historial_salud (usuario_id, profesional_email, peso, altura, imc)
                 VALUES (:uid, :email, :peso, :altura, :imc)"
            );
            $stmt->execute([
                ':uid'    => $usuarioId,
                ':email'  => $profesionalEmail,
                ':peso'   => $peso,
                ':altura' => $altura,
                ':imc'    => $imc,
            ]);
        } catch (PDOException $e) {
            error_log('registrarSalud: ' . $e->getMessage());
        }
    }

    public function getHistorialSalud($usuarioId) {
        try {
            $stmt = $this->db->prepare("
                SELECT hs.*,
                       u.nombre AS profesional_nombre
                FROM historial_salud hs
                LEFT JOIN usuarios u ON u.correo = hs.profesional_email
                WHERE hs.usuario_id = :uid
                ORDER BY hs.fecha DESC
                LIMIT 100
            ");
            $stmt->execute([':uid' => $usuarioId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
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
                         area = :area,
                         peso = :peso,
                         altura = :altura
                     WHERE id = :id";

            $stmt = $this->db->prepare($query);

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':correo', $data['correo']);
            $stmt->bindParam(':foto', $data['foto']);
            $stmt->bindParam(':area', $data['area']);
            $stmt->bindValue(':peso',   isset($data['peso'])   && $data['peso']   !== '' ? (float)$data['peso']   : null, PDO::PARAM_STR);
            $stmt->bindValue(':altura', isset($data['altura']) && $data['altura'] !== '' ? (float)$data['altura'] : null, PDO::PARAM_STR);
            
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