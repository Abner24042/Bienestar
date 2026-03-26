<?php
// proteccion contra DDoS y fuerza bruta
// la idea es contar cuantas peticiones llegan de la misma IP en un rango de tiempo
// si pasa el limite la bloqueamos por un rato segun que tipo de ruta sea
// lo mas importante: la IP NUNCA se guarda tal cual en la BD
// se guarda como un hash HMAC-SHA256 que es de un solo sentido (no se puede revertir)
// asi si alguien hackea la BD no sabe de que IP vienen las peticiones

require_once __DIR__ . '/../config/database.php';

class RateLimiter
{

    private $db;

    // limites por tipo de ruta: [max_peticiones, ventana_segundos, bloqueo_segundos]
    // auth es mas estricto porque es donde se puede hacer fuerza bruta al login
    // chat es mas permisivo porque el polling manda muchas peticiones seguidas
    private const LIMITS = [
        'auth' => [10, 60, 300],
        'api' => [120, 60, 60],
        'chat' => [300, 60, 30],
        'page' => [60, 60, 60],
    ];

    // estas ips nunca se limitan (localhost durante desarrollo)
    private const WHITELIST = ['127.0.0.1', '::1', '::ffff:127.0.0.1'];

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->ensureTable();
    }

    // crea la tabla si no existe, con esto no hay que correr migraciones manualmente
    private function ensureTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS rate_limit (
                ip_hash      VARCHAR(64)  NOT NULL,
                route_type   VARCHAR(10)  NOT NULL,
                requests     INT          NOT NULL DEFAULT 1,
                window_start INT          NOT NULL,
                blocked_until INT         NOT NULL DEFAULT 0,
                PRIMARY KEY (ip_hash, route_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    // regresa true si la peticion se permite, false si hay que bloquearla
    public function check(string $ip, string $type = 'page'): bool
    {
        // localhost siempre pasa, si no bloqueariamos el servidor durante desarrollo
        if (in_array($ip, self::WHITELIST, true))
            return true;

        // convertimos la IP a hash - la IP real nunca toca la BD
        $ipHash = hash_hmac('sha256', $ip, RATE_LIMIT_SECRET);
        $now = time();

        [$maxReq, $windowSec, $blockSec] = self::LIMITS[$type] ?? self::LIMITS['page'];

        // limpieza de registros viejos: solo 1% de las veces para no hacer DELETE en cada peticion
        // esto es para que la tabla no crezca infinito
        if (mt_rand(1, 100) === 1) {
            $this->cleanup();
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "SELECT requests, window_start, blocked_until
                 FROM rate_limit
                 WHERE ip_hash = ? AND route_type = ?"
            );
            $stmt->execute([$ipHash, $type]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // si la IP esta en periodo de bloqueo activo, rechazar
                if ((int) $row['blocked_until'] > $now) {
                    $this->db->rollBack();
                    return false;
                }

                // si ya paso el tiempo de la ventana, reiniciamos el contador
                if ($now - (int) $row['window_start'] >= $windowSec) {
                    $this->db->prepare(
                        "UPDATE rate_limit
                         SET requests = 1, window_start = ?, blocked_until = 0
                         WHERE ip_hash = ? AND route_type = ?"
                    )->execute([$now, $ipHash, $type]);
                } else {
                    $newCount = (int) $row['requests'] + 1;

                    if ($newCount > $maxReq) {
                        // supero el limite: bloquear hasta blocked_until
                        $this->db->prepare(
                            "UPDATE rate_limit
                             SET requests = ?, blocked_until = ?
                             WHERE ip_hash = ? AND route_type = ?"
                        )->execute([$newCount, $now + $blockSec, $ipHash, $type]);
                        $this->db->commit();
                        return false;
                    }

                    $this->db->prepare(
                        "UPDATE rate_limit SET requests = ?
                         WHERE ip_hash = ? AND route_type = ?"
                    )->execute([$newCount, $ipHash, $type]);
                }
            } else {
                // primera vez que vemos esta IP, insertar con contador en 1
                $this->db->prepare(
                    "INSERT INTO rate_limit (ip_hash, route_type, requests, window_start, blocked_until)
                     VALUES (?, ?, 1, ?, 0)"
                )->execute([$ipHash, $type, $now]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            try {
                $this->db->rollBack();
            } catch (Exception $e2) {
            }
            error_log('RateLimiter error: ' . $e->getMessage());
            // fail-open: si la BD falla dejamos pasar la peticion para no romper el sitio
            // es mejor no bloquear que dejar el sitio caido por un error interno
            return true;
        }
    }

    // borra registros que ya expiraron para que la tabla no crezca para siempre
    private function cleanup(): void
    {
        try {
            $cutoff = time() - 3600;
            $this->db->prepare(
                "DELETE FROM rate_limit
                 WHERE window_start < ? AND blocked_until < ?"
            )->execute([$cutoff, time()]);
        } catch (Exception $e) {
            error_log('RateLimiter cleanup error: ' . $e->getMessage());
        }
    }
}
