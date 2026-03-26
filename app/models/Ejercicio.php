<?php
require_once __DIR__ . '/../config/database.php';

class Ejercicio {
    private $db;
    private $table = 'ejercicios';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->ensureColumns();
    }

    // Agrega columnas nuevas si no existen (para ExerciseDB)
    public function ensureColumns() {
        $extras = [
            "ALTER TABLE {$this->table} ADD COLUMN ejercicio_api_id VARCHAR(100) NULL",
            "ALTER TABLE {$this->table} ADD COLUMN musculo_objetivo VARCHAR(100) NULL",
            "ALTER TABLE {$this->table} ADD COLUMN equipamiento VARCHAR(100) NULL",
            "ALTER TABLE {$this->table} ADD COLUMN musculos_secundarios TEXT NULL",
            "ALTER TABLE {$this->table} ADD COLUMN auto_generado TINYINT(1) NOT NULL DEFAULT 0",
            "ALTER TABLE {$this->table} ADD COLUMN aprobado TINYINT(1) NOT NULL DEFAULT 0",
            "ALTER TABLE {$this->table} ADD COLUMN solo_asignado TINYINT(1) NOT NULL DEFAULT 0",
        ];
        foreach ($extras as $sql) {
            try { $this->db->exec($sql); } catch (PDOException $e) { /* columna ya existe */ }
        }
    }

    public function getActive() {
        try {
            $query = "SELECT * FROM {$this->table} WHERE activo = 1 AND solo_asignado = 0 ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error en getActive ejercicios: ' . $e->getMessage());
            return [];
        }
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error en getAll ejercicios: ' . $e->getMessage());
            return [];
        }
    }

    public function findById($id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Error en findById ejercicio: ' . $e->getMessage());
            return false;
        }
    }

    public function getByCreator($email) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE creado_por = :email AND activo = 1 ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error en getByCreator ejercicios: ' . $e->getMessage());
            return [];
        }
    }

    public function create($data) {
        try {
            $query = "INSERT INTO {$this->table} (titulo, descripcion, duracion, nivel, tipo, calorias_quemadas, video_url, imagen, instrucciones, creado_por, musculo_objetivo, equipamiento, musculos_secundarios) VALUES (:titulo, :descripcion, :duracion, :nivel, :tipo, :calorias, :video_url, :imagen, :instrucciones, :creado_por, :musculo_objetivo, :equipamiento, :musculos_secundarios)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':titulo'               => $data['titulo'],
                ':descripcion'          => $data['descripcion'] ?? null,
                ':duracion'             => $data['duracion'] ?? null,
                ':nivel'                => $data['nivel'] ?? 'principiante',
                ':tipo'                 => $data['tipo'] ?? 'cardio',
                ':calorias'             => $data['calorias_quemadas'] ?? null,
                ':video_url'            => $data['video_url'] ?? null,
                ':imagen'               => $data['imagen'] ?? null,
                ':instrucciones'        => $data['instrucciones'] ?? null,
                ':creado_por'           => $data['creado_por'] ?? null,
                ':musculo_objetivo'     => $data['musculo_objetivo'] ?? null,
                ':equipamiento'         => $data['equipamiento'] ?? null,
                ':musculos_secundarios' => $data['musculos_secundarios'] ?? null,
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error en create ejercicio: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $fields = [];
            $params = [':id' => $id];

            $allowed = ['titulo', 'descripcion', 'duracion', 'nivel', 'tipo', 'calorias_quemadas', 'video_url', 'imagen', 'instrucciones', 'musculo_objetivo', 'equipamiento', 'musculos_secundarios', 'solo_asignado', 'activo'];
            foreach ($allowed as $field) {
                if (array_key_exists($field, $data)) {
                    $fields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }

            if (empty($fields)) return false;

            $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Error en update ejercicio: ' . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en delete ejercicio: ' . $e->getMessage());
            return false;
        }
    }

    public function toggleActive($id, $activo) {
        try {
            $query = "UPDATE {$this->table} SET activo = :activo WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':activo' => $activo ? 1 : 0, ':id' => $id]);
            return true;
        } catch (PDOException $e) {
            error_log('Error en toggleActive ejercicio: ' . $e->getMessage());
            return false;
        }
    }

    // Todos los ejercicios manuales (auto_generado=0): manuales + aprobados
    public function getForCoach() {
        try {
            $query = "SELECT * FROM {$this->table} WHERE activo = 1 AND auto_generado = 0 AND solo_asignado = 0 ORDER BY titulo ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // ── ExerciseDB integration ──────────────────────────────────────────────

    // Guarda un ejercicio de la API; retorna id insertado, false=duplicado, 'ERROR:msg'=error
    public function saveFromExerciseDB($data) {
        try {
            // Evitar duplicados por ejercicio_api_id
            $check = $this->db->prepare("SELECT id FROM {$this->table} WHERE ejercicio_api_id = :api_id LIMIT 1");
            $check->execute([':api_id' => $data['ejercicio_api_id']]);
            if ($check->fetch()) return false; // ya existe

            $query = "INSERT INTO {$this->table}
                (titulo, descripcion, instrucciones, nivel, tipo,
                 imagen, ejercicio_api_id, musculo_objetivo,
                 equipamiento, musculos_secundarios,
                 auto_generado, aprobado, activo)
                VALUES
                (:titulo, :descripcion, :instrucciones, :nivel, :tipo,
                 :imagen, :ejercicio_api_id, :musculo_objetivo,
                 :equipamiento, :musculos_secundarios,
                 1, 0, 1)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':titulo'              => $data['titulo'],
                ':descripcion'         => $data['descripcion'] ?? '',
                ':instrucciones'       => $data['instrucciones'] ?? '',
                ':nivel'               => $data['nivel'] ?? 'principiante',
                ':tipo'                => $data['tipo'] ?? 'fuerza',
                ':imagen'              => $data['imagen'] ?? null,
                ':ejercicio_api_id'    => $data['ejercicio_api_id'],
                ':musculo_objetivo'    => $data['musculo_objetivo'] ?? null,
                ':equipamiento'        => $data['equipamiento'] ?? null,
                ':musculos_secundarios'=> $data['musculos_secundarios'] ?? null,
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error en saveFromExerciseDB: ' . $e->getMessage());
            return 'ERROR:' . $e->getMessage();
        }
    }

    // Ejercicios auto-generados que aún no han sido aprobados por el coach
    public function getAutoUnapproved() {
        try {
            $query = "SELECT * FROM {$this->table}
                      WHERE auto_generado = 1 AND aprobado = 0 AND activo = 1
                      ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error en getAutoUnapproved ejercicios: ' . $e->getMessage());
            return [];
        }
    }

    // Aprobar un ejercicio: queda permanente (auto_generado=0 + aprobado=1)
    public function aprobar($id) {
        try {
            $query = "UPDATE {$this->table} SET aprobado = 1, auto_generado = 0 WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('Error en aprobar ejercicio: ' . $e->getMessage());
            return false;
        }
    }

    // Eliminar ejercicios auto-generados no aprobados más viejos que N horas
    public function deleteOldUnapproved($hours = 48) {
        try {
            $query = "DELETE FROM {$this->table}
                      WHERE auto_generado = 1 AND aprobado = 0
                      AND created_at < DATE_SUB(NOW(), INTERVAL :hours HOUR)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':hours' => $hours]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('Error en deleteOldUnapproved ejercicios: ' . $e->getMessage());
            return 0;
        }
    }

    // Eliminar ejercicios auto-generados aprobados más viejos que N días
    public function deleteOldAuto($days = 60) {
        try {
            $query = "DELETE FROM {$this->table}
                      WHERE auto_generado = 1
                      AND created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':days' => $days]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('Error en deleteOldAuto ejercicios: ' . $e->getMessage());
            return 0;
        }
    }
}
