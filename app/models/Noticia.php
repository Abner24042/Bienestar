<?php
require_once __DIR__ . '/../config/database.php';

class Noticia {
    private $db;
    private $table = 'noticias';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getPublished() {
        try {
            // Asegurar columna destacado (idempotente)
            try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN destacado TINYINT(1) NOT NULL DEFAULT 0"); } catch (PDOException $e) {}
            $query = "SELECT * FROM {$this->table} WHERE publicado = 1 ORDER BY destacado DESC, fecha_publicacion DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error en getPublished noticias: ' . $e->getMessage());
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
            error_log('Error en getAll noticias: ' . $e->getMessage());
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
            error_log('Error en findById noticia: ' . $e->getMessage());
            return false;
        }
    }

    public function getByCreator($email) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE creado_por = :email ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error en getByCreator noticias: ' . $e->getMessage());
            return [];
        }
    }

    public function create($data) {
        try {
            $query = "INSERT INTO {$this->table} (titulo, contenido, resumen, imagen, categoria, autor, publicado, creado_por) VALUES (:titulo, :contenido, :resumen, :imagen, :categoria, :autor, :publicado, :creado_por)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':titulo' => $data['titulo'],
                ':contenido' => $data['contenido'],
                ':resumen' => $data['resumen'] ?? null,
                ':imagen' => $data['imagen'] ?? null,
                ':categoria' => $data['categoria'] ?? 'general',
                ':autor' => $data['autor'] ?? null,
                ':publicado' => $data['publicado'] ?? 0,
                ':creado_por' => $data['creado_por'] ?? null
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error en create noticia: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $fields = [];
            $params = [':id' => $id];

            $allowed = ['titulo', 'contenido', 'resumen', 'imagen', 'categoria', 'autor', 'publicado'];
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
            error_log('Error en update noticia: ' . $e->getMessage());
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
            error_log('Error en delete noticia: ' . $e->getMessage());
            return false;
        }
    }

    public function togglePublished($id, $publicado) {
        try {
            $query = "UPDATE {$this->table} SET publicado = :publicado WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':publicado' => $publicado ? 1 : 0, ':id' => $id]);
            return true;
        } catch (PDOException $e) {
            error_log('Error en togglePublished noticia: ' . $e->getMessage());
            return false;
        }
    }

    // ─── Auto-fetch ───────────────────────────────────────────────

    /**
     * Crea las columnas extra si no existen (idempotente)
     */
    public function ensureColumns() {
        try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN fuente_auto TINYINT(1) NOT NULL DEFAULT 0"); } catch (PDOException $e) {}
        try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN url_fuente VARCHAR(600) DEFAULT NULL");     } catch (PDOException $e) {}
        try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN destacado TINYINT(1) NOT NULL DEFAULT 0");  } catch (PDOException $e) {}
    }

    /**
     * Marca una noticia como destacada y quita el destacado de las demás
     */
    public function setDestacado($id) {
        try {
            try { $this->db->exec("ALTER TABLE {$this->table} ADD COLUMN destacado TINYINT(1) NOT NULL DEFAULT 0"); } catch (PDOException $e) {}
            $this->db->exec("UPDATE {$this->table} SET destacado = 0");
            $stmt = $this->db->prepare("UPDATE {$this->table} SET destacado = 1 WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            error_log('Error en setDestacado: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina artículos auto-generados más viejos que $days días (libera espacio en BD)
     */
    public function unpublishOldAuto($days = 3) {
        try {
            $query = "DELETE FROM {$this->table}
                      WHERE fuente_auto = 1
                        AND fecha_publicacion < DATE_SUB(NOW(), INTERVAL :days DAY)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':days' => $days]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('Error en unpublishOldAuto: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Comprueba si ya existe un artículo con esa URL fuente
     */
    public function existsByUrl($url) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE url_fuente = :url LIMIT 1");
            $stmt->execute([':url' => $url]);
            return (bool)$stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Inserta un artículo auto-generado (fuente_auto = 1)
     */
    public function createAuto($data) {
        try {
            $query = "INSERT INTO {$this->table}
                        (titulo, contenido, resumen, imagen, categoria, autor, publicado, creado_por, fuente_auto, url_fuente, fecha_publicacion)
                      VALUES
                        (:titulo, :contenido, :resumen, :imagen, :categoria, :autor, 1, 'auto', 1, :url_fuente, :fecha)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':titulo'     => $data['titulo'],
                ':contenido'  => $data['contenido'],
                ':resumen'    => $data['resumen']    ?? null,
                ':imagen'     => $data['imagen']     ?? null,
                ':categoria'  => $data['categoria'],
                ':autor'      => $data['autor']      ?? null,
                ':url_fuente' => $data['url_fuente'],
                ':fecha'      => $data['fecha']      ?? date('Y-m-d H:i:s'),
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error en createAuto noticia: ' . $e->getMessage());
            return false;
        }
    }
}
