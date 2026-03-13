<?php
require_once __DIR__ . '/../config/database.php';

class Favorito {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->ensureTable();
    }

    private function ensureTable() {
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS favoritos (
                id            INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id    INT NOT NULL,
                tipo          VARCHAR(10) NOT NULL,
                referencia_id INT NOT NULL,
                created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_fav (usuario_id, tipo, referencia_id),
                INDEX idx_usuario_tipo (usuario_id, tipo)
            )");
        } catch (PDOException $e) {}
    }

    public function toggle($userId, $tipo, $referenciaId) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM favoritos WHERE usuario_id = :uid AND tipo = :tipo AND referencia_id = :rid");
            $stmt->execute([':uid' => $userId, ':tipo' => $tipo, ':rid' => $referenciaId]);
            if ($stmt->fetch()) {
                $this->db->prepare("DELETE FROM favoritos WHERE usuario_id = :uid AND tipo = :tipo AND referencia_id = :rid")
                         ->execute([':uid' => $userId, ':tipo' => $tipo, ':rid' => $referenciaId]);
                return 'removed';
            } else {
                $this->db->prepare("INSERT INTO favoritos (usuario_id, tipo, referencia_id) VALUES (:uid, :tipo, :rid)")
                         ->execute([':uid' => $userId, ':tipo' => $tipo, ':rid' => $referenciaId]);
                return 'added';
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getIds($userId) {
        try {
            $stmt = $this->db->prepare("SELECT tipo, referencia_id FROM favoritos WHERE usuario_id = :uid");
            $stmt->execute([':uid' => $userId]);
            $rows = $stmt->fetchAll();
            $recetaIds = [];
            $ejercicioIds = [];
            foreach ($rows as $row) {
                if ($row['tipo'] === 'receta') $recetaIds[] = (int)$row['referencia_id'];
                else $ejercicioIds[] = (int)$row['referencia_id'];
            }
            return ['receta_ids' => $recetaIds, 'ejercicio_ids' => $ejercicioIds];
        } catch (PDOException $e) {
            return ['receta_ids' => [], 'ejercicio_ids' => []];
        }
    }

    public function getWithData($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, f.created_at AS fav_at FROM recetas r
                JOIN favoritos f ON f.referencia_id = r.id AND f.tipo = 'receta'
                WHERE f.usuario_id = :uid AND r.activo = 1
                ORDER BY f.created_at DESC
            ");
            $stmt->execute([':uid' => $userId]);
            $recetas = $stmt->fetchAll();

            $stmt = $this->db->prepare("
                SELECT e.*, f.created_at AS fav_at FROM ejercicios e
                JOIN favoritos f ON f.referencia_id = e.id AND f.tipo = 'ejercicio'
                WHERE f.usuario_id = :uid AND e.activo = 1
                ORDER BY f.created_at DESC
            ");
            $stmt->execute([':uid' => $userId]);
            $ejercicios = $stmt->fetchAll();

            return ['recetas' => $recetas, 'ejercicios' => $ejercicios];
        } catch (PDOException $e) {
            return ['recetas' => [], 'ejercicios' => []];
        }
    }
}
