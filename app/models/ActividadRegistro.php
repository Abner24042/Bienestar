<?php
require_once __DIR__ . '/../config/database.php';

class ActividadRegistro {
    private $db;
    private $table = 'actividad_registro';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists() {
        $query = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            correo VARCHAR(255) NOT NULL,
            tipo ENUM('ejercicio', 'receta') NOT NULL,
            referencia_id INT,
            titulo VARCHAR(255),
            calorias INT DEFAULT 0,
            fecha DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_correo_fecha (correo, fecha)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->exec($query);
    }

    public function logEjercicio($correo, $referencia_id, $titulo, $calorias) {
        $query = "INSERT INTO {$this->table} (correo, tipo, referencia_id, titulo, calorias, fecha)
                  VALUES (:correo, 'ejercicio', :ref_id, :titulo, :calorias, CURDATE())";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':correo'   => $correo,
            ':ref_id'   => $referencia_id,
            ':titulo'   => $titulo,
            ':calorias' => (int)$calorias
        ]);
        return $this->db->lastInsertId();
    }

    public function logReceta($correo, $referencia_id, $titulo, $calorias) {
        $query = "INSERT INTO {$this->table} (correo, tipo, referencia_id, titulo, calorias, fecha)
                  VALUES (:correo, 'receta', :ref_id, :titulo, :calorias, CURDATE())";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':correo'   => $correo,
            ':ref_id'   => $referencia_id,
            ':titulo'   => $titulo,
            ':calorias' => (int)$calorias
        ]);
        return $this->db->lastInsertId();
    }

    public function getCaloriasHoy($correo) {
        $query = "SELECT COALESCE(SUM(calorias), 0) as total
                  FROM {$this->table}
                  WHERE correo = :correo AND fecha = CURDATE()";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':correo' => $correo]);
        return (int)$stmt->fetchColumn();
    }

    public function getEjerciciosEstaSemana($correo) {
        $query = "SELECT COUNT(*) as total
                  FROM {$this->table}
                  WHERE correo = :correo
                    AND tipo = 'ejercicio'
                    AND YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':correo' => $correo]);
        return (int)$stmt->fetchColumn();
    }
}
