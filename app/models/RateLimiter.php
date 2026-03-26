<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Rate Limiter — protección contra DDoS / fuerza bruta
 *
 * IPs almacenadas como HMAC-SHA256 (hash de un solo sentido, no reversible).
 * Límites por tipo de ruta:
 *   auth  → 10 req/min   → bloqueo 5 min   (login, Google OAuth)
 *   api   → 120 req/min  → bloqueo 1 min   (endpoints JSON)
 *   chat  → 300 req/min  → bloqueo 30 seg  (polling frecuente)
 *   page  → 60 req/min   → bloqueo 1 min   (páginas HTML)
 */
class RateLimiter {

    private $db;

    // [max_requests, window_seconds, block_seconds]
    private const LIMITS = [
        'auth' => [10,   60, 300],
        'api'  => [120,  60,  60],
        'chat' => [300,  60,  30],
        'page' => [60,   60,  60],
    ];

    // IPs locales que nunca se limitan
    private const WHITELIST = ['127.0.0.1', '::1', '::ffff:127.0.0.1'];

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->ensureTable();
    }

    // ── Tabla auto-creada ──────────────────────────────────────────────────────

    private function ensureTable(): void {
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

    // ── Método principal ───────────────────────────────────────────────────────

    /**
     * Retorna true si la solicitud está permitida, false si debe ser bloqueada.
     */
    public function check(string $ip, string $type = 'page'): bool {
        if (in_array($ip, self::WHITELIST, true)) return true;

        // Hash de un solo sentido — la IP original nunca se almacena
        $ipHash = hash_hmac('sha256', $ip, RATE_LIMIT_SECRET);
        $now    = time();

        [$maxReq, $windowSec, $blockSec] = self::LIMITS[$type] ?? self::LIMITS['page'];

        // Limpieza probabilística 1% de las veces para no acumular registros
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
                // ¿Está bloqueado aún?
                if ((int)$row['blocked_until'] > $now) {
                    $this->db->rollBack();
                    return false;
                }

                // ¿Venció la ventana de tiempo?
                if ($now - (int)$row['window_start'] >= $windowSec) {
                    // Reiniciar ventana
                    $this->db->prepare(
                        "UPDATE rate_limit
                         SET requests = 1, window_start = ?, blocked_until = 0
                         WHERE ip_hash = ? AND route_type = ?"
                    )->execute([$now, $ipHash, $type]);
                } else {
                    $newCount = (int)$row['requests'] + 1;

                    if ($newCount > $maxReq) {
                        // Supera el límite → bloquear
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
                // Primera solicitud de esta IP para este tipo de ruta
                $this->db->prepare(
                    "INSERT INTO rate_limit (ip_hash, route_type, requests, window_start, blocked_until)
                     VALUES (?, ?, 1, ?, 0)"
                )->execute([$ipHash, $type, $now]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            try { $this->db->rollBack(); } catch (Exception $e2) {}
            error_log('RateLimiter error: ' . $e->getMessage());
            // Fail-open: ante error de DB se permite la solicitud
            return true;
        }
    }

    // ── Limpieza de registros expirados ───────────────────────────────────────

    private function cleanup(): void {
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
