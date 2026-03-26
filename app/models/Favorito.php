<?php
require_once __DIR__ . '/../config/database.php';

class Favorito {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->ensureTable();
        $this->ensureColumns();
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

    private function ensureColumns() {
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM favoritos LIKE 'snapshot_data'")->fetchAll();
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE favoritos ADD COLUMN snapshot_data LONGTEXT NULL");
            }
        } catch (PDOException $e) {}
    }

    public function toggle($userId, $tipo, $referenciaId, $snapshotData = null) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM favoritos WHERE usuario_id = :uid AND tipo = :tipo AND referencia_id = :rid");
            $stmt->execute([':uid' => $userId, ':tipo' => $tipo, ':rid' => $referenciaId]);
            if ($stmt->fetch()) {
                $this->db->prepare("DELETE FROM favoritos WHERE usuario_id = :uid AND tipo = :tipo AND referencia_id = :rid")
                         ->execute([':uid' => $userId, ':tipo' => $tipo, ':rid' => $referenciaId]);
                return 'removed';
            } else {
                $snap = $snapshotData ? json_encode($snapshotData, JSON_UNESCAPED_UNICODE) : null;
                $this->db->prepare("INSERT INTO favoritos (usuario_id, tipo, referencia_id, snapshot_data) VALUES (:uid, :tipo, :rid, :snap)")
                         ->execute([':uid' => $userId, ':tipo' => $tipo, ':rid' => $referenciaId, ':snap' => $snap]);
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
            // LEFT JOIN: muestra recetas aunque hayan sido borradas (usa snapshot)
            $stmt = $this->db->prepare("
                SELECT r.*, f.created_at AS fav_at, f.snapshot_data, f.referencia_id AS fav_ref_id
                FROM favoritos f
                LEFT JOIN recetas r ON r.id = f.referencia_id
                WHERE f.usuario_id = :uid AND f.tipo = 'receta'
                ORDER BY f.created_at DESC
            ");
            $stmt->execute([':uid' => $userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $recetas = [];
            foreach ($rows as $row) {
                if ($row['id'] === null) {
                    // Receta eliminada — reconstruir desde snapshot
                    $snap = !empty($row['snapshot_data']) ? json_decode($row['snapshot_data'], true) : null;
                    if ($snap) {
                        $snap['id']       = (int)$row['fav_ref_id'];
                        $snap['fav_at']   = $row['fav_at'];
                        $snap['_deleted'] = true;
                        $recetas[] = $snap;
                    }
                    // Si no hay snapshot, simplemente no se muestra
                } else {
                    $row['_deleted'] = false;
                    $recetas[] = $row;
                }
            }

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
