<?php
/**
 * WizPortfolio — Server-side portfolio persistence
 * Stores holdings in WordPress user meta so they follow the user
 * across devices and browsers. localStorage is used as client cache only.
 */

if (!defined('ABSPATH')) exit;

class WizPortfolio {

    const META_KEY_HOLDINGS  = 'wiz_portfolio_holdings';
    const META_KEY_SNAPSHOTS = 'wiz_portfolio_snapshots';

    // ─── HOLDINGS CRUD ────────────────────────────────────────

    /**
     * Get all holdings for a user.
     *
     * @param int $user_id
     * @return array
     */
    public static function get_holdings($user_id) {
        $holdings = get_user_meta($user_id, self::META_KEY_HOLDINGS, true);
        return is_array($holdings) ? $holdings : [];
    }

    /**
     * Save full holdings array for a user (replaces existing).
     *
     * @param int   $user_id
     * @param array $holdings
     * @return bool
     */
    public static function save_holdings($user_id, $holdings) {
        // Sanitize each holding
        $clean = [];
        foreach ($holdings as $h) {
            $clean[] = self::sanitize_holding($h);
        }
        return update_user_meta($user_id, self::META_KEY_HOLDINGS, $clean);
    }

    /**
     * Add a single holding for a user.
     *
     * @param int   $user_id
     * @param array $holding
     * @return array Updated holdings array
     */
    public static function add_holding($user_id, $holding) {
        $holdings   = self::get_holdings($user_id);
        $holdings[] = self::sanitize_holding($holding);
        self::save_holdings($user_id, $holdings);
        // Take a portfolio snapshot after adding
        self::take_snapshot($user_id);
        return $holdings;
    }

    /**
     * Update a holding by index.
     *
     * @param int   $user_id
     * @param int   $index
     * @param array $holding
     * @return array|WP_Error
     */
    public static function update_holding($user_id, $index, $holding) {
        $holdings = self::get_holdings($user_id);
        if (!isset($holdings[$index])) {
            return new WP_Error('not_found', 'Holding not found at index ' . $index);
        }
        $holdings[$index] = self::sanitize_holding($holding);
        self::save_holdings($user_id, $holdings);
        return $holdings;
    }

    /**
     * Delete a holding by index.
     *
     * @param int $user_id
     * @param int $index
     * @return array|WP_Error Updated holdings array
     */
    public static function delete_holding($user_id, $index) {
        $holdings = self::get_holdings($user_id);
        if (!isset($holdings[$index])) {
            return new WP_Error('not_found', 'Holding not found at index ' . $index);
        }
        array_splice($holdings, $index, 1);
        self::save_holdings($user_id, $holdings);
        return $holdings;
    }

    /**
     * Update the current price of a single holding.
     *
     * @param int   $user_id
     * @param int   $index
     * @param float $new_price
     * @return array|WP_Error
     */
    public static function update_holding_price($user_id, $index, $new_price) {
        $holdings = self::get_holdings($user_id);
        if (!isset($holdings[$index])) {
            return new WP_Error('not_found', 'Holding not found at index ' . $index);
        }
        $holdings[$index]['current_price']    = round((float)$new_price, 4);
        $holdings[$index]['price_updated_at'] = time();
        self::save_holdings($user_id, $holdings);
        return $holdings[$index];
    }

    // ─── PORTFOLIO METRICS ────────────────────────────────────

    /**
     * Calculate portfolio summary metrics.
     *
     * @param array $holdings
     * @return array
     */
    public static function calculate_summary($holdings) {
        if (empty($holdings)) {
            return [
                'total_cost'  => 0,
                'total_value' => 0,
                'total_pnl'   => 0,
                'total_pct'   => 0,
                'best'        => null,
                'worst'       => null,
                'count'       => 0,
            ];
        }

        $total_cost  = 0;
        $total_value = 0;
        $returns     = [];

        foreach ($holdings as $i => $h) {
            $cost  = $h['units'] * $h['buy_price'];
            $value = $h['units'] * $h['current_price'];
            $total_cost  += $cost;
            $total_value += $value;
            $returns[$i]  = $cost > 0 ? (($value - $cost) / $cost) * 100 : 0;
        }

        $total_pnl = $total_value - $total_cost;
        $total_pct = $total_cost > 0 ? ($total_pnl / $total_cost) * 100 : 0;

        $best_idx  = array_keys($returns, max($returns))[0];
        $worst_idx = array_keys($returns, min($returns))[0];

        return [
            'total_cost'  => round($total_cost, 2),
            'total_value' => round($total_value, 2),
            'total_pnl'   => round($total_pnl, 2),
            'total_pct'   => round($total_pct, 2),
            'best'        => ['name' => $holdings[$best_idx]['name'],  'pct' => round($returns[$best_idx], 2)],
            'worst'       => ['name' => $holdings[$worst_idx]['name'], 'pct' => round($returns[$worst_idx], 2)],
            'count'       => count($holdings),
        ];
    }

    // ─── PORTFOLIO SNAPSHOTS ──────────────────────────────────

    /**
     * Take a daily snapshot of portfolio total value.
     * Stored as array of ['date' => 'YYYY-MM-DD', 'value' => float]
     * Max 730 snapshots (2 years).
     *
     * @param int $user_id
     */
    public static function take_snapshot($user_id) {
        $holdings  = self::get_holdings($user_id);
        $summary   = self::calculate_summary($holdings);
        $snapshots = self::get_snapshots($user_id);

        $today = date('Y-m-d');

        // Update today's snapshot or add new one
        $found = false;
        foreach ($snapshots as &$snap) {
            if ($snap['date'] === $today) {
                $snap['value'] = $summary['total_value'];
                $found = true;
                break;
            }
        }
        unset($snap);

        if (!$found) {
            $snapshots[] = ['date' => $today, 'value' => $summary['total_value']];
        }

        // Sort by date ascending
        usort($snapshots, fn($a, $b) => strcmp($a['date'], $b['date']));

        // Keep max 730 entries
        if (count($snapshots) > 730) {
            $snapshots = array_slice($snapshots, -730);
        }

        update_user_meta($user_id, self::META_KEY_SNAPSHOTS, $snapshots);
    }

    /**
     * Get historical snapshots for a user.
     *
     * @param int    $user_id
     * @param string $period  '1M', '3M', '6M', 'YTD', '1Y', 'ALL'
     * @return array
     */
    public static function get_snapshots($user_id, $period = 'ALL') {
        $snapshots = get_user_meta($user_id, self::META_KEY_SNAPSHOTS, true);
        $snapshots = is_array($snapshots) ? $snapshots : [];

        if ($period === 'ALL' || empty($snapshots)) {
            return $snapshots;
        }

        $today = new DateTime();
        $cutoff = clone $today;

        switch ($period) {
            case '1M':  $cutoff->modify('-1 month');  break;
            case '3M':  $cutoff->modify('-3 months'); break;
            case '6M':  $cutoff->modify('-6 months'); break;
            case 'YTD': $cutoff = new DateTime(date('Y') . '-01-01'); break;
            case '1Y':  $cutoff->modify('-1 year');   break;
            default:    return $snapshots;
        }

        $cutoff_str = $cutoff->format('Y-m-d');
        return array_values(array_filter($snapshots, fn($s) => $s['date'] >= $cutoff_str));
    }

    // ─── SANITIZATION ─────────────────────────────────────────

    /**
     * Sanitize a holding array before storing.
     */
    public static function sanitize_holding($h) {
        return [
            'name'             => sanitize_text_field($h['name'] ?? ''),
            'ticker'           => strtoupper(sanitize_text_field($h['ticker'] ?? '')),
            'units'            => round(abs((float)($h['units'] ?? 0)), 8),
            'buy_price'        => round(abs((float)($h['buy_price'] ?? 0)), 4),
            'current_price'    => round(abs((float)($h['current_price'] ?? 0)), 4),
            'date'             => sanitize_text_field($h['date'] ?? date('Y-m-d')),
            'price_updated_at' => isset($h['price_updated_at']) ? intval($h['price_updated_at']) : null,
        ];
    }
}
