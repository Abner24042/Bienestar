<?php
require_once __DIR__ . '/../config/database.php';

class Rutina {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->ensureTables();
    }

    private function ensureTables() {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS rutinas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(200) NOT NULL,
                descripcion TEXT NULL,
                nivel VARCHAR(50) DEFAULT 'principiante',
                duracion_total INT NULL COMMENT 'minutos estimados',
                coach_correo VARCHAR(200) NOT NULL,
                activo TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS rutina_ejercicios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                rutina_id INT NOT NULL,
                ejercicio_id INT NOT NULL,
                series INT DEFAULT 3,
                repeticiones VARCHAR(50) NULL,
                duracion_min INT NULL,
                descanso_seg INT DEFAULT 60,
                orden INT DEFAULT 0,
                notas TEXT NULL,
                FOREIGN KEY (rutina_id) REFERENCES rutinas(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function getByCoach($correo) {
        $stmt = $this->db->prepare("
            SELECT r.*, COUNT(re.id) AS num_ejercicios
            FROM rutinas r
            LEFT JOIN rutina_ejercicios re ON re.rutina_id = r.id
            WHERE r.coach_correo = :correo
            GROUP BY r.id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([':correo' => $correo]);
        return $stmt->fetchAll();
    }

    public function getDetail($id) {
        $stmt = $this->db->prepare("SELECT * FROM rutinas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $rutina = $stmt->fetch();
        if (!$rutina) return null;

        $stmt2 = $this->db->prepare("
            SELECT re.*, e.titulo AS ejercicio_titulo, e.tipo, e.nivel AS ejercicio_nivel,
                   e.calorias_quemadas, e.imagen, e.descripcion AS ejercicio_descripcion,
                   e.musculo_objetivo
            FROM rutina_ejercicios re
            JOIN ejercicios e ON e.id = re.ejercicio_id
            WHERE re.rutina_id = :id
            ORDER BY re.orden ASC, re.id ASC
        ");
        $stmt2->execute([':id' => $id]);
        $rutina['ejercicios'] = $stmt2->fetchAll();
        return $rutina;
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO rutinas (nombre, descripcion, nivel, duracion_total, coach_correo)
            VALUES (:nombre, :descripcion, :nivel, :duracion, :correo)
        ");
        $stmt->execute([
            ':nombre'      => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':nivel'       => $data['nivel'] ?? 'principiante',
            ':duracion'    => $data['duracion_total'] ?? null,
            ':correo'      => $data['coach_correo'],
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE rutinas SET nombre=:nombre, descripcion=:descripcion, nivel=:nivel, duracion_total=:duracion
            WHERE id=:id
        ");
        return $stmt->execute([
            ':nombre'      => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':nivel'       => $data['nivel'] ?? 'principiante',
            ':duracion'    => $data['duracion_total'] ?? null,
            ':id'          => $id,
        ]);
    }

    public function setEjercicios($rutinaId, $ejercicios) {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM rutina_ejercicios WHERE rutina_id = :id")->execute([':id' => $rutinaId]);
            if (!empty($ejercicios)) {
                $stmt = $this->db->prepare("
                    INSERT INTO rutina_ejercicios (rutina_id, ejercicio_id, series, repeticiones, duracion_min, descanso_seg, orden, notas)
                    VALUES (:rutina_id, :ejercicio_id, :series, :repeticiones, :duracion_min, :descanso_seg, :orden, :notas)
                ");
                foreach ($ejercicios as $i => $ej) {
                    $stmt->execute([
                        ':rutina_id'    => $rutinaId,
                        ':ejercicio_id' => $ej['ejercicio_id'],
                        ':series'       => $ej['series'] ?: 3,
                        ':repeticiones' => $ej['repeticiones'] ?: null,
                        ':duracion_min' => $ej['duracion_min'] ?: null,
                        ':descanso_seg' => $ej['descanso_seg'] ?: 60,
                        ':orden'        => $i,
                        ':notas'        => $ej['notas'] ?: null,
                    ]);
                }
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM rutinas WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM rutinas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
