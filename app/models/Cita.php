<?php
require_once __DIR__ . '/../config/database.php';

class Cita {
    private $db;
    private $table = 'citas_bieniestar';
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->ensureColumns();
    }

    private function ensureColumns() {
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM {$this->table}")->fetchAll(PDO::FETCH_COLUMN);
            $toAdd = [
                'es_solicitud'    => "TINYINT(1) NOT NULL DEFAULT 0",
                'sol_profesional' => "VARCHAR(255) NULL",
                'sol_tipo'        => "VARCHAR(50) NULL",
                'sol_estado'      => "VARCHAR(20) NOT NULL DEFAULT 'pendiente'",
                'sol_motivo'      => "TEXT NULL",
                'sol_reasignado'  => "VARCHAR(255) NULL",
            ];
            foreach ($toAdd as $col => $def) {
                if (!in_array($col, $cols)) {
                    $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN {$col} {$def}");
                }
            }
        } catch (PDOException $e) {
            error_log('Cita ensureColumns: ' . $e->getMessage());
        }
    }
    
    /**
     * Crear nueva cita
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . "
                     (fecha, hora, titulo, descripcion, correo, profesional_correo)
                     VALUES (:fecha, :hora, :titulo, :descripcion, :correo, :profesional_correo)";

            $stmt = $this->db->prepare($query);

            $stmt->bindParam(':fecha', $data['fecha']);
            $stmt->bindParam(':hora', $data['hora']);
            $stmt->bindParam(':titulo', $data['titulo']);

            $descripcion = $data['descripcion'] ?? null;
            $stmt->bindParam(':descripcion', $descripcion);

            $stmt->bindParam(':correo', $data['correo']);

            $profesional = $data['profesional_correo'] ?? null;
            $stmt->bindParam(':profesional_correo', $profesional);

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log('Error en create cita: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener citas por correo
     */
    public function getByEmail($email) {
        try {
            $query = "SELECT * FROM " . $this->table . "
                     WHERE correo = :email
                       AND (es_solicitud = 0 OR sol_estado = 'aceptada')
                     ORDER BY fecha DESC, hora DESC";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error en getByEmail cita: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener citas donde el usuario es paciente O profesional
     */
    public function getByEmailOrProfessional($email) {
        try {
            $query = "SELECT * FROM " . $this->table . "
                     WHERE (correo = :email OR profesional_correo = :email2)
                       AND (es_solicitud = 0 OR sol_estado = 'aceptada')
                     ORDER BY fecha DESC, hora DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email, ':email2' => $email]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error en getByEmailOrProfessional cita: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener cita por ID
     */
    public function findById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Error en findById cita: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar cita
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " 
                     SET fecha = :fecha, 
                         hora = :hora,
                         titulo = :titulo
                     WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':fecha', $data['fecha']);
            $stmt->bindParam(':hora', $data['hora']);
            $stmt->bindParam(':titulo', $data['titulo']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en update cita: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar cita
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en delete cita: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener citas por profesional
     */
    public function getByProfessional($profesionalEmail) {
        try {
            $query = "SELECT c.*, u.nombre as usuario_nombre, u.correo as usuario_correo
                     FROM " . $this->table . " c
                     LEFT JOIN usuarios u ON c.correo = u.correo
                     WHERE c.profesional_correo = :email
                     ORDER BY c.fecha ASC, c.hora ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $profesionalEmail);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error en getByProfessional: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener citas futuras de un profesional
     */
    public function getUpcomingByProfessional($profesionalEmail) {
        try {
            $query = "SELECT c.*, u.nombre as usuario_nombre, u.correo as usuario_correo
                     FROM " . $this->table . " c
                     LEFT JOIN usuarios u ON c.correo = u.correo
                     WHERE c.profesional_correo = :email
                     AND c.fecha >= CURDATE()
                     ORDER BY c.fecha ASC, c.hora ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $profesionalEmail);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error en getUpcomingByProfessional: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener la próxima cita futura del usuario (como paciente o profesional)
     */
    public function getNextUpcoming($email) {
        try {
            $query = "SELECT * FROM " . $this->table . "
                     WHERE (correo = :email OR profesional_correo = :email2)
                       AND (es_solicitud = 0 OR sol_estado = 'aceptada')
                       AND fecha >= CURDATE()
                     ORDER BY fecha ASC, hora ASC
                     LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email, ':email2' => $email]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Error en getNextUpcoming: ' . $e->getMessage());
            return false;
        }
    }

    /* ── SOLICITUDES ─────────────────────────────────────────────── */

    public function createSolicitud($data) {
        try {
            $query = "INSERT INTO {$this->table}
                     (fecha, hora, titulo, descripcion, correo,
                      es_solicitud, sol_profesional, sol_tipo, sol_estado)
                     VALUES (:fecha, '09:00', :titulo, :desc, :correo,
                      1, :sol_pro, :sol_tipo, 'pendiente')";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':fecha'    => $data['fecha'],
                ':titulo'   => 'Solicitud: ' . $data['tipo'],
                ':desc'     => $data['motivo'] ?? '',
                ':correo'   => $data['correo'],
                ':sol_pro'  => $data['especialista'],
                ':sol_tipo' => $data['tipo'],
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('createSolicitud: ' . $e->getMessage());
            return false;
        }
    }

    public function getSolicitudesPorProfesional($correo) {
        try {
            $query = "SELECT c.*, u.nombre AS usuario_nombre
                     FROM {$this->table} c
                     LEFT JOIN usuarios u ON c.correo = u.correo
                     WHERE c.es_solicitud = 1
                       AND c.sol_profesional = :correo
                       AND c.sol_estado = 'pendiente'
                     ORDER BY c.id DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':correo' => $correo]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getSolicitudesPorProfesional: ' . $e->getMessage());
            return [];
        }
    }

    public function getSolicitudesCount($correo) {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM {$this->table}
                 WHERE es_solicitud = 1 AND sol_profesional = :correo AND sol_estado = 'pendiente'"
            );
            $stmt->execute([':correo' => $correo]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function aceptarSolicitud($id, $correo_profesional, $titulo, $fecha, $hora, $notas = null) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE {$this->table}
                 SET sol_estado = 'aceptada',
                     profesional_correo = :correo,
                     titulo = :titulo,
                     fecha  = :fecha,
                     hora   = :hora,
                     descripcion = :notas
                 WHERE id = :id AND sol_profesional = :correo2 AND es_solicitud = 1"
            );
            return $stmt->execute([
                ':id'     => $id,
                ':correo' => $correo_profesional,
                ':correo2'=> $correo_profesional,
                ':titulo' => $titulo,
                ':fecha'  => $fecha,
                ':hora'   => $hora,
                ':notas'  => $notas,
            ]);
        } catch (PDOException $e) {
            error_log('aceptarSolicitud: ' . $e->getMessage());
            return false;
        }
    }

    public function denegarSolicitud($id, $motivo, $reasignado_a = null) {
        try {
            $estado = $reasignado_a ? 'reasignada' : 'denegada';
            $stmt = $this->db->prepare(
                "UPDATE {$this->table}
                 SET sol_estado = :estado, sol_motivo = :motivo, sol_reasignado = :rea
                 WHERE id = :id AND es_solicitud = 1"
            );
            return $stmt->execute([
                ':id'     => $id,
                ':estado' => $estado,
                ':motivo' => $motivo,
                ':rea'    => $reasignado_a,
            ]);
        } catch (PDOException $e) {
            error_log('denegarSolicitud: ' . $e->getMessage());
            return false;
        }
    }

    public function getSolicitudesUsuario($correo_usuario) {
        try {
            $query = "SELECT c.*,
                        pro.nombre  AS nombre_profesional,
                        rea.nombre  AS nombre_reasignado
                     FROM {$this->table} c
                     LEFT JOIN usuarios pro ON pro.correo = c.sol_profesional
                     LEFT JOIN usuarios rea ON rea.correo = c.sol_reasignado
                     WHERE c.correo = :correo AND c.es_solicitud = 1
                       AND c.sol_estado != 'vista'
                     ORDER BY c.id DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':correo' => $correo_usuario]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function marcarSolicitudVista($id, $correo_usuario) {
        try {
            // Solo marcar como 'vista' las denegadas/reasignadas.
            // Las aceptadas NO se tocan porque son citas reales en el calendario.
            $stmt = $this->db->prepare(
                "UPDATE {$this->table} SET sol_estado = 'vista'
                 WHERE id = :id AND correo = :correo AND es_solicitud = 1
                   AND sol_estado IN ('denegada','reasignada')"
            );
            return $stmt->execute([':id' => $id, ':correo' => $correo_usuario]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtener todas las citas
     */
    public function getAll() {
        try {
            $query = "SELECT c.*, u.nombre 
                     FROM " . $this->table . " c
                     LEFT JOIN usuarios u ON c.correo = u.correo
                     ORDER BY c.fecha DESC, c.hora DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error en getAll citas: ' . $e->getMessage());
            return [];
        }
    }
}