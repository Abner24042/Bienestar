<?php
require_once __DIR__ . '/../config/database.php';

class TestResult {
    private $db;
    private $table = 'test_resultados';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists() {
        $query = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            correo VARCHAR(255) NOT NULL,
            puntaje INT NOT NULL,
            nivel VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_correo (correo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->exec($query);
    }

    public function saveResult($correo, $puntaje, $nivel) {
        try {
            $query = "INSERT INTO {$this->table} (correo, puntaje, nivel) VALUES (:correo, :puntaje, :nivel)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':correo' => $correo, ':puntaje' => $puntaje, ':nivel' => $nivel]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error en saveResult: ' . $e->getMessage());
            return false;
        }
    }

    public function getLastResult($correo) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE correo = :correo ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':correo' => $correo]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Error en getLastResult: ' . $e->getMessage());
            return null;
        }
    }
}
