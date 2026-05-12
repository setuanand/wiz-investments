<?php
/**
 * WizData — Server-side data layer
 * Handles all external API calls with caching via WP Transients.
 * All calls use wp_remote_get() so there are no CORS issues.
 */

if (!defined('ABSPATH')) exit;

class WizData {

    // Cache durations
    const CACHE_HISTORICAL = 3600;      // 1 hour for daily historical data
    const CACHE_LIVE_PRICE = 900;       // 15 minutes for live prices
    const CACHE_MARKET_STATUS = 300;    // 5 minutes for market open/close

    // ─── HISTORICAL PRICE DATA ────────────────────────────────

    /**
     * Fetch historical daily closing prices for a symbol.
     * Uses Yahoo Finance v8 API (free, no key required).
     *
     * @param string $symbol  e.g. 'SPY', 'QQQ', 'AAPL', 'BTC-USD'
     * @param int    $days    Number of trading days (default 504 = ~2 years)
     * @return array|WP_Error Array of ['date' => 'YYYY-MM-DD', 'close' => float]
     */
    public static function get_historical_prices($symbol, $days = 504) {
        $symbol = strtoupper(sanitize_text_field($symbol));
        $cache_key = 'wiz_hist_' . $symbol . '_' . $days;

        // Check cache first
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $period2 = time();
        $period1 = $period2 - ($days * 1.5 * 86400); // extra buffer for weekends/holidays

        $url = add_query_arg([
            'interval' => '1d',
            'period1'  => intval($period1),
            'period2'  => intval($period2),
        ], "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}");

        $response = wp_remote_get($url, [
            'timeout' => 15,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; WizInvestments/1.0)',
                'Accept'     => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('fetch_failed', 'Could not connect to data provider: ' . $response->get_error_message());
        }

        $status = wp_remote_retrieve_response_code($response);
        if ($status !== 200) {
            return new WP_Error('bad_response', "Data provider returned status {$status} for symbol {$symbol}");
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['chart']['result'][0])) {
            return new WP_Error('no_data', "No data returned for symbol {$symbol}. Check the symbol is valid.");
        }

        $result    = $data['chart']['result'][0];
        $timestamps = $result['timestamp'] ?? [];
        $closes     = $result['indicators']['quote'][0]['close'] ?? [];

        if (empty($timestamps) || empty($closes)) {
            return new WP_Error('empty_data', "Empty price data for symbol {$symbol}");
        }

        // Build clean array of date => close pairs, skip null values
        $prices = [];
        foreach ($timestamps as $i => $ts) {
            if (isset($closes[$i]) && $closes[$i] !== null) {
                $prices[] = [
                    'date'  => date('Y-m-d', $ts),
                    'close' => round((float)$closes[$i], 4),
                ];
            }
        }

        // Trim to requested number of days
        if (count($prices) > $days) {
            $prices = array_slice($prices, -$days);
        }

        // Cache the result
        set_transient($cache_key, $prices, self::CACHE_HISTORICAL);

        return $prices;
    }

    // ─── LIVE PRICE ───────────────────────────────────────────

    /**
     * Fetch the current/latest price for a symbol.
     *
     * @param string $symbol  e.g. 'AAPL', 'BTC-USD', 'MSFT'
     * @return array|WP_Error ['symbol', 'price', 'change', 'change_pct', 'currency', 'market_state']
     */
    public static function get_live_price($symbol) {
        $symbol = strtoupper(sanitize_text_field($symbol));
        $cache_key = 'wiz_live_' . $symbol;

        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $url = add_query_arg([
            'interval' => '1d',
            'range'    => '1d',
        ], "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}");

        $response = wp_remote_get($url, [
            'timeout' => 10,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; WizInvestments/1.0)',
                'Accept'     => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('fetch_failed', 'Could not fetch price: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['chart']['result'][0])) {
            return new WP_Error('no_data', "No price data for symbol {$symbol}");
        }

        $meta  = $data['chart']['result'][0]['meta'];
        $price = $meta['regularMarketPrice'] ?? null;
        $prev  = $meta['previousClose'] ?? $meta['chartPreviousClose'] ?? null;

        if (!$price) {
            return new WP_Error('no_price', "Could not extract price for {$symbol}");
        }

        $change     = $prev ? round($price - $prev, 4) : 0;
        $change_pct = $prev ? round(($change / $prev) * 100, 2) : 0;

        $result = [
            'symbol'      => $symbol,
            'price'       => round($price, 4),
            'change'      => $change,
            'change_pct'  => $change_pct,
            'currency'    => $meta['currency'] ?? 'USD',
            'market_state'=> $meta['marketState'] ?? 'UNKNOWN',
            'cached_at'   => time(),
        ];

        set_transient($cache_key, $result, self::CACHE_LIVE_PRICE);

        return $result;
    }

    // ─── BATCH LIVE PRICES ────────────────────────────────────

    /**
     * Fetch live prices for multiple symbols.
     * Returns array keyed by symbol. Failed symbols return error string.
     *
     * @param array $symbols  e.g. ['AAPL', 'MSFT', 'BTC-USD']
     * @return array
     */
    public static function get_live_prices_batch($symbols) {
        $results = [];
        foreach ($symbols as $symbol) {
            $result = self::get_live_price($symbol);
            $results[$symbol] = is_wp_error($result)
                ? ['error' => $result->get_error_message()]
                : $result;
        }
        return $results;
    }

    // ─── CACHE MANAGEMENT ─────────────────────────────────────

    /**
     * Force-clear cached data for a symbol (useful for testing).
     */
    public static function clear_cache($symbol, $type = 'all') {
        $symbol = strtoupper(sanitize_text_field($symbol));
        if ($type === 'live' || $type === 'all') {
            delete_transient('wiz_live_' . $symbol);
        }
        if ($type === 'historical' || $type === 'all') {
            foreach ([252, 504, 756] as $days) {
                delete_transient('wiz_hist_' . $symbol . '_' . $days);
            }
        }
    }

    /**
     * Validate a symbol string before making API calls.
     */
    public static function validate_symbol($symbol) {
        $symbol = strtoupper(trim($symbol));
        if (empty($symbol)) return new WP_Error('empty_symbol', 'Symbol cannot be empty.');
        if (!preg_match('/^[A-Z0-9\-\.]{1,12}$/', $symbol)) {
            return new WP_Error('invalid_symbol', 'Invalid symbol format. Use letters, numbers, hyphens only (e.g. AAPL, BTC-USD).');
        }
        return $symbol;
    }
}
