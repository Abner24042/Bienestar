<?php
require_once __DIR__ . '/../config/database.php';

class PlanAlimenticio {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->ensureTables();
    }

    private function ensureTables() {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS planes_alimenticios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(200) NOT NULL,
                descripcion TEXT NULL,
                objetivo VARCHAR(200) NULL,
                duracion_semanas INT DEFAULT 1,
                nutriologo_correo VARCHAR(200) NOT NULL,
                activo TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS plan_alimenticio_recetas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                plan_id INT NOT NULL,
                receta_id INT NOT NULL,
                dia_semana TINYINT DEFAULT 1 COMMENT '1=Lunes 7=Domingo',
                tiempo_comida VARCHAR(50) DEFAULT 'comida' COMMENT 'desayuno|almuerzo|merienda|cena',
                porciones DECIMAL(4,1) DEFAULT 1,
                notas TEXT NULL,
                orden INT DEFAULT 0,
                FOREIGN KEY (plan_id) REFERENCES planes_alimenticios(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function getByNutriologo($correo) {
        $stmt = $this->db->prepare("
            SELECT p.*, COUNT(pr.id) AS num_recetas
            FROM planes_alimenticios p
            LEFT JOIN plan_alimenticio_recetas pr ON pr.plan_id = p.id
            WHERE p.nutriologo_correo = :correo
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([':correo' => $correo]);
        return $stmt->fetchAll();
    }

    public function getDetail($id) {
        $stmt = $this->db->prepare("SELECT * FROM planes_alimenticios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $plan = $stmt->fetch();
        if (!$plan) return null;

        $stmt2 = $this->db->prepare("
            SELECT pr.*, r.titulo AS receta_titulo, r.calorias, r.imagen,
                   r.proteinas, r.carbohidratos, r.grasas, r.categoria, r.descripcion AS receta_descripcion
            FROM plan_alimenticio_recetas pr
            JOIN recetas r ON r.id = pr.receta_id
            WHERE pr.plan_id = :id
            ORDER BY pr.dia_semana ASC,
                     FIELD(pr.tiempo_comida, 'desayuno', 'almuerzo', 'merienda', 'cena', 'comida'),
                     pr.orden ASC
        ");
        $stmt2->execute([':id' => $id]);
        $plan['recetas'] = $stmt2->fetchAll();
        return $plan;
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO planes_alimenticios (nombre, descripcion, objetivo, duracion_semanas, nutriologo_correo)
            VALUES (:nombre, :descripcion, :objetivo, :duracion, :correo)
        ");
        $stmt->execute([
            ':nombre'      => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':objetivo'    => $data['objetivo'] ?? null,
            ':duracion'    => $data['duracion_semanas'] ?? 1,
            ':correo'      => $data['nutriologo_correo'],
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE planes_alimenticios
            SET nombre=:nombre, descripcion=:descripcion, objetivo=:objetivo, duracion_semanas=:duracion
            WHERE id=:id
        ");
        return $stmt->execute([
            ':nombre'      => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':objetivo'    => $data['objetivo'] ?? null,
            ':duracion'    => $data['duracion_semanas'] ?? 1,
            ':id'          => $id,
        ]);
    }

    public function setRecetas($planId, $recetas) {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM plan_alimenticio_recetas WHERE plan_id = :id")->execute([':id' => $planId]);
            if (!empty($recetas)) {
                $stmt = $this->db->prepare("
                    INSERT INTO plan_alimenticio_recetas (plan_id, receta_id, dia_semana, tiempo_comida, porciones, notas, orden)
                    VALUES (:plan_id, :receta_id, :dia, :tiempo, :porciones, :notas, :orden)
                ");
                foreach ($recetas as $i => $r) {
                    $stmt->execute([
                        ':plan_id'   => $planId,
                        ':receta_id' => $r['receta_id'],
                        ':dia'       => $r['dia_semana'] ?? 1,
                        ':tiempo'    => $r['tiempo_comida'] ?? 'comida',
                        ':porciones' => $r['porciones'] ?? 1,
                        ':notas'     => $r['notas'] ?: null,
                        ':orden'     => $i,
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
        $stmt = $this->db->prepare("DELETE FROM planes_alimenticios WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM planes_alimenticios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
