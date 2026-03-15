<?php
// models/OnlineOrdering.php

class OnlineOrdering {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // ── STATS ───────────────────────────────────────────────

    public function getStats(): array {
        // Connected platforms count
        $platforms = $this->db
            ->query("SELECT COUNT(*) FROM ordering_platforms WHERE status = 'connected'")
            ->fetchColumn();

        // Today's orders
        $todayOrders = $this->db
            ->query("SELECT COUNT(*) FROM online_orders WHERE DATE(ordered_at) = CURDATE()")
            ->fetchColumn();

        // Today's revenue & commissions
        $money = $this->db->query(
            "SELECT
                COALESCE(SUM(total_amount),   0) AS today_revenue,
                COALESCE(SUM(commission_fee), 0) AS today_commissions,
                COALESCE(AVG(total_amount),   0) AS avg_order_value
             FROM online_orders
             WHERE DATE(ordered_at) = CURDATE()"
        )->fetch();

        return [
            'connected_platforms' => (int)   $platforms,
            'today_orders'        => (int)   $todayOrders,
            'today_revenue'       => (float) $money['today_revenue'],
            'today_commissions'   => (float) $money['today_commissions'],
            'avg_order_value'     => (float) $money['avg_order_value'],
        ];
    }

    // ── PLATFORMS ────────────────────────────────────────────

    public function getPlatforms(): PDOStatement {
        $stmt = $this->db->prepare(
            "SELECT p.*,
                    COUNT(o.id)            AS total_orders,
                    COALESCE(SUM(o.total_amount),0)   AS total_revenue,
                    COALESCE(SUM(o.commission_fee),0) AS total_commissions
             FROM ordering_platforms p
             LEFT JOIN online_orders o ON o.platform_id = p.id
             GROUP BY p.id
             ORDER BY p.platform_name ASC"
        );
        $stmt->execute();
        return $stmt;
    }

    public function getPlatformById(int $id): array|false {
        $stmt = $this->db->prepare(
            "SELECT * FROM ordering_platforms WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function updatePlatform(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE ordering_platforms SET
                status          = :status,
                commission_rate = :commission_rate,
                api_key         = :api_key,
                webhook_url     = :webhook_url
             WHERE id = :id"
        );
        return $stmt->execute([
            ':status'          => $data['status']          ?? 'disconnected',
            ':commission_rate' => (float) ($data['commission_rate'] ?? 0),
            ':api_key'         => $data['api_key']         ?? null,
            ':webhook_url'     => $data['webhook_url']     ?? null,
            ':id'              => $id,
        ]);
    }

    public function updateLastSynced(int $id): void {
        $this->db->prepare(
            "UPDATE ordering_platforms SET last_synced_at = NOW() WHERE id = :id"
        )->execute([':id' => $id]);
    }

    // ── ORDERS ──────────────────────────────────────────────

    public function getRecentOrders(int $limit = 10): PDOStatement {
        $stmt = $this->db->prepare(
            "SELECT o.*, p.platform_name, p.slug AS platform_slug
             FROM online_orders o
             JOIN ordering_platforms p ON p.id = o.platform_id
             ORDER BY o.ordered_at DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function getOrderById(int $id): array|false {
        $stmt = $this->db->prepare(
            "SELECT o.*, p.platform_name
             FROM online_orders o
             JOIN ordering_platforms p ON p.id = o.platform_id
             WHERE o.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function updateOrderStatus(int $id, string $status): bool {
        $allowed = ['pending','confirmed','preparing','ready',
                    'out_for_delivery','delivered','cancelled','refunded'];
        if (!in_array($status, $allowed)) return false;

        $deliveredAt = in_array($status, ['delivered','refunded'])
            ? ', delivered_at = NOW()' : '';

        $stmt = $this->db->prepare(
            "UPDATE online_orders
             SET status = :status {$deliveredAt}
             WHERE id = :id"
        );
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function createOrder(array $data): int|false {
        $stmt = $this->db->prepare(
            "INSERT INTO online_orders
                (platform_id, platform_order_id, customer_name, customer_phone,
                 customer_address, items, subtotal, delivery_fee, discount,
                 total_amount, commission_fee, net_revenue, status,
                 payment_method, payment_status, notes)
             VALUES
                (:platform_id, :platform_order_id, :customer_name, :customer_phone,
                 :customer_address, :items, :subtotal, :delivery_fee, :discount,
                 :total_amount, :commission_fee, :net_revenue, :status,
                 :payment_method, :payment_status, :notes)"
        );

        // Calculate commission from platform rate
        $platformRow = $this->getPlatformById((int) $data['platform_id']);
        $rate        = $platformRow ? (float) $platformRow['commission_rate'] / 100 : 0;
        $commission  = round((float) $data['total_amount'] * $rate, 2);
        $net         = round((float) $data['total_amount'] - $commission, 2);

        $ok = $stmt->execute([
            ':platform_id'       => (int)   $data['platform_id'],
            ':platform_order_id' => $data['platform_order_id'],
            ':customer_name'     => $data['customer_name']    ?? null,
            ':customer_phone'    => $data['customer_phone']   ?? null,
            ':customer_address'  => $data['customer_address'] ?? null,
            ':items'             => is_array($data['items']) ? json_encode($data['items']) : $data['items'],
            ':subtotal'          => (float) ($data['subtotal']    ?? 0),
            ':delivery_fee'      => (float) ($data['delivery_fee']?? 0),
            ':discount'          => (float) ($data['discount']    ?? 0),
            ':total_amount'      => (float) ($data['total_amount']?? 0),
            ':commission_fee'    => $commission,
            ':net_revenue'       => $net,
            ':status'            => $data['status']         ?? 'pending',
            ':payment_method'    => $data['payment_method'] ?? 'cash',
            ':payment_status'    => $data['payment_status'] ?? 'unpaid',
            ':notes'             => $data['notes']          ?? null,
        ]);
        return $ok ? (int) $this->db->lastInsertId() : false;
    }

    // ── COMMISSION ──────────────────────────────────────────

    public function getCommissionSummary(int $days = 30): PDOStatement {
        $stmt = $this->db->prepare(
            "SELECT p.platform_name, p.commission_rate,
                    COALESCE(SUM(cs.total_orders),     0) AS total_orders,
                    COALESCE(SUM(cs.gross_revenue),    0) AS gross_revenue,
                    COALESCE(SUM(cs.total_commission), 0) AS total_commission,
                    COALESCE(SUM(cs.net_revenue),      0) AS net_revenue
             FROM ordering_platforms p
             LEFT JOIN commission_summary cs
                    ON cs.platform_id = p.id
                   AND cs.period_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY p.id
             ORDER BY total_commission DESC"
        );
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    /** Simulate syncing new orders from a platform (stub). */
    public function syncOrders(int $platformId): int {
        // In production: call external platform API here.
        // For now just bump last_synced_at and return a fake count.
        $this->updateLastSynced($platformId);
        return random_int(0, 5);
    }
}