<?php
/**
 * WizAjax — All AJAX endpoint registrations and handlers.
 * Replaces inline AJAX handlers previously in functions.php.
 * All endpoints use admin-ajax.php to bypass LiteSpeed POST blocking.
 */

if (!defined('ABSPATH')) exit;

class WizAjax {

    public static function register() {
        // Data endpoints (no auth required — public market data)
        add_action('wp_ajax_wiz_fetch_historical',        [__CLASS__, 'handle_fetch_historical']);
        add_action('wp_ajax_nopriv_wiz_fetch_historical', [__CLASS__, 'handle_fetch_historical']);
        add_action('wp_ajax_wiz_fetch_live_price',        [__CLASS__, 'handle_fetch_live_price']);
        add_action('wp_ajax_nopriv_wiz_fetch_live_price', [__CLASS__, 'handle_fetch_live_price']);

        // Portfolio endpoints (auth required)
        add_action('wp_ajax_wiz_get_holdings',    [__CLASS__, 'handle_get_holdings']);
        add_action('wp_ajax_wiz_add_holding',     [__CLASS__, 'handle_add_holding']);
        add_action('wp_ajax_wiz_update_holding',  [__CLASS__, 'handle_update_holding']);
        add_action('wp_ajax_wiz_delete_holding',  [__CLASS__, 'handle_delete_holding']);
        add_action('wp_ajax_wiz_refresh_prices',  [__CLASS__, 'handle_refresh_prices']);
        add_action('wp_ajax_wiz_get_snapshots',   [__CLASS__, 'handle_get_snapshots']);

        // Contact form
        add_action('wp_ajax_wiz_contact',         [__CLASS__, 'handle_contact']);
        add_action('wp_ajax_nopriv_wiz_contact',  [__CLASS__, 'handle_contact']);

        // Auth endpoints
        add_action('wp_ajax_wiz_reset_password',         [__CLASS__, 'handle_reset_password']);
        add_action('wp_ajax_nopriv_wiz_reset_password',  [__CLASS__, 'handle_reset_password']);
        add_action('wp_ajax_wiz_forgot_password',        [__CLASS__, 'handle_forgot_password']);
        add_action('wp_ajax_nopriv_wiz_forgot_password', [__CLASS__, 'handle_forgot_password']);
    }

    // ─── SECURITY HELPERS ─────────────────────────────────────

    private static function verify_nonce($action) {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $action)) {
            wp_send_json_error(['message' => 'Security check failed. Please refresh and try again.'], 403);
        }
    }

    private static function require_login() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'You must be logged in to perform this action.'], 401);
        }
    }

    // ─── DATA ENDPOINTS ───────────────────────────────────────

    /**
     * Fetch historical daily prices for a symbol.
     * POST: symbol, days (optional, default 252)
     */
    public static function handle_fetch_historical() {
        self::verify_nonce('wiz_data_nonce');

        $symbol = strtoupper(sanitize_text_field($_POST['symbol'] ?? 'SPY'));
        $days   = intval($_POST['days'] ?? 252);
        $days   = max(30, min(756, $days)); // clamp 30–756

        $validated = WizData::validate_symbol($symbol);
        if (is_wp_error($validated)) {
            wp_send_json_error(['message' => $validated->get_error_message()]);
        }

        $prices = WizData::get_historical_prices($symbol, $days);

        if (is_wp_error($prices)) {
            wp_send_json_error(['message' => $prices->get_error_message()]);
        }

        wp_send_json_success([
            'symbol' => $symbol,
            'days'   => count($prices),
            'prices' => $prices,
            'source' => 'yahoo_finance',
        ]);
    }

    /**
     * Fetch live price for a single symbol.
     * POST: symbol
     */
    public static function handle_fetch_live_price() {
        self::verify_nonce('wiz_data_nonce');

        $symbol    = strtoupper(sanitize_text_field($_POST['symbol'] ?? ''));
        $validated = WizData::validate_symbol($symbol);

        if (is_wp_error($validated)) {
            wp_send_json_error(['message' => $validated->get_error_message()]);
        }

        $price = WizData::get_live_price($symbol);

        if (is_wp_error($price)) {
            wp_send_json_error(['message' => $price->get_error_message()]);
        }

        wp_send_json_success($price);
    }

    // ─── PORTFOLIO ENDPOINTS ──────────────────────────────────

    /**
     * Get all holdings for the current user.
     */
    public static function handle_get_holdings() {
        self::verify_nonce('wiz_portfolio_nonce');
        self::require_login();

        $user_id  = get_current_user_id();
        $holdings = WizPortfolio::get_holdings($user_id);
        $summary  = WizPortfolio::calculate_summary($holdings);

        wp_send_json_success([
            'holdings' => $holdings,
            'summary'  => $summary,
        ]);
    }

    /**
     * Add a new holding.
     * POST: name, ticker, units, buy_price, current_price, date
     */
    public static function handle_add_holding() {
        self::verify_nonce('wiz_portfolio_nonce');
        self::require_login();

        $holding = [
            'name'          => sanitize_text_field($_POST['name'] ?? ''),
            'ticker'        => sanitize_text_field($_POST['ticker'] ?? ''),
            'units'         => $_POST['units'] ?? 0,
            'buy_price'     => $_POST['buy_price'] ?? 0,
            'current_price' => $_POST['current_price'] ?? 0,
            'date'          => sanitize_text_field($_POST['date'] ?? date('Y-m-d')),
        ];

        if (empty($holding['name']) || !$holding['units'] || !$holding['buy_price'] || !$holding['current_price']) {
            wp_send_json_error(['message' => 'Please fill in all required fields.']);
        }

        $user_id  = get_current_user_id();
        $holdings = WizPortfolio::add_holding($user_id, $holding);
        $summary  = WizPortfolio::calculate_summary($holdings);

        wp_send_json_success([
            'holdings' => $holdings,
            'summary'  => $summary,
            'message'  => 'Holding added successfully.',
        ]);
    }

    /**
     * Update a holding by index.
     * POST: index, name, ticker, units, buy_price, current_price, date
     */
    public static function handle_update_holding() {
        self::verify_nonce('wiz_portfolio_nonce');
        self::require_login();

        $index   = intval($_POST['index'] ?? -1);
        $holding = [
            'name'          => sanitize_text_field($_POST['name'] ?? ''),
            'ticker'        => sanitize_text_field($_POST['ticker'] ?? ''),
            'units'         => $_POST['units'] ?? 0,
            'buy_price'     => $_POST['buy_price'] ?? 0,
            'current_price' => $_POST['current_price'] ?? 0,
            'date'          => sanitize_text_field($_POST['date'] ?? date('Y-m-d')),
        ];

        $user_id  = get_current_user_id();
        $result   = WizPortfolio::update_holding($user_id, $index, $holding);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        $summary = WizPortfolio::calculate_summary($result);
        wp_send_json_success(['holdings' => $result, 'summary' => $summary]);
    }

    /**
     * Delete a holding by index.
     * POST: index
     */
    public static function handle_delete_holding() {
        self::verify_nonce('wiz_portfolio_nonce');
        self::require_login();

        $index   = intval($_POST['index'] ?? -1);
        $user_id = get_current_user_id();
        $result  = WizPortfolio::delete_holding($user_id, $index);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        $summary = WizPortfolio::calculate_summary($result);
        WizPortfolio::take_snapshot($user_id);

        wp_send_json_success(['holdings' => $result, 'summary' => $summary]);
    }

    /**
     * Refresh live prices for all holdings that have a ticker symbol.
     */
    public static function handle_refresh_prices() {
        self::verify_nonce('wiz_portfolio_nonce');
        self::require_login();

        $user_id  = get_current_user_id();
        $holdings = WizPortfolio::get_holdings($user_id);

        if (empty($holdings)) {
            wp_send_json_error(['message' => 'No holdings to refresh.']);
        }

        $updated = 0;
        $errors  = [];

        foreach ($holdings as $i => $h) {
            if (empty($h['ticker'])) continue;

            $price_data = WizData::get_live_price($h['ticker']);

            if (is_wp_error($price_data)) {
                $errors[] = $h['ticker'] . ': ' . $price_data->get_error_message();
                continue;
            }

            WizPortfolio::update_holding_price($user_id, $i, $price_data['price']);
            $updated++;
        }

        // Re-fetch updated holdings
        $holdings = WizPortfolio::get_holdings($user_id);
        $summary  = WizPortfolio::calculate_summary($holdings);
        WizPortfolio::take_snapshot($user_id);

        wp_send_json_success([
            'holdings' => $holdings,
            'summary'  => $summary,
            'updated'  => $updated,
            'errors'   => $errors,
            'message'  => "Updated {$updated} holding(s)." . (count($errors) ? ' Some failed: ' . implode(', ', $errors) : ''),
        ]);
    }

    /**
     * Get portfolio value snapshots for chart.
     * POST: period ('1M', '3M', '6M', 'YTD', '1Y', 'ALL')
     */
    public static function handle_get_snapshots() {
        self::verify_nonce('wiz_portfolio_nonce');
        self::require_login();

        $period    = sanitize_text_field($_POST['period'] ?? 'ALL');
        $user_id   = get_current_user_id();
        $snapshots = WizPortfolio::get_snapshots($user_id, $period);

        wp_send_json_success(['snapshots' => $snapshots, 'period' => $period]);
    }

    // ─── CONTACT FORM ─────────────────────────────────────────

    public static function handle_contact() {
        if (!isset($_POST['wiz_contact_nonce']) || !wp_verify_nonce($_POST['wiz_contact_nonce'], 'wiz_contact_action')) {
            wp_send_json_error('Security check failed. Please refresh and try again.');
        }

        $name    = sanitize_text_field($_POST['name'] ?? '');
        $email   = sanitize_email($_POST['email'] ?? '');
        $subject = sanitize_text_field($_POST['subject'] ?? 'General Enquiry');
        $message = sanitize_textarea_field($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            wp_send_json_error('Please complete all fields before submitting.');
        }
        if (!is_email($email)) {
            wp_send_json_error('Please enter a valid email address.');
        }

        $post_id = wp_insert_post([
            'post_title'   => '[' . $subject . '] from ' . $name,
            'post_type'    => 'contact_submission',
            'post_content' => $message,
            'post_status'  => 'private',
        ]);

        if (is_wp_error($post_id)) {
            wp_send_json_error('Unable to save your message. Please try again.');
        }

        update_post_meta($post_id, 'contact_email', $email);
        update_post_meta($post_id, 'contact_name', $name);
        update_post_meta($post_id, 'contact_subject', $subject);

        // Email admin
        $admin_email = get_option('admin_email');
        wp_mail(
            $admin_email,
            'New Contact: [' . $subject . '] from ' . $name,
            $message . "\n\nEmail: " . $email,
            ['Reply-To: ' . $name . ' <' . $email . '>']
        );

        // HTML confirmation to sender
        $headers = ['Content-Type: text/html; charset=UTF-8', 'From: WizInvestments <' . $admin_email . '>'];
        $confirm_body = self::build_contact_confirmation_email($name, $email, $subject, $message);
        wp_mail($email, 'We received your message — WizInvestments', $confirm_body, $headers);

        wp_send_json_success('Thanks, ' . esc_html($name) . '! Your message has been received. Check your inbox for a confirmation email.');
    }

    private static function build_contact_confirmation_email($name, $email, $subject, $message) {
        return '<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
body{margin:0;padding:0;background:#0d1117;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;}
.wrapper{max-width:580px;margin:0 auto;padding:40px 20px;}
.card{background:#161b22;border:1px solid #30363d;border-radius:12px;overflow:hidden;}
.header{background:#161b22;border-bottom:1px solid #30363d;padding:32px 40px;text-align:center;}
.logo{font-size:24px;font-weight:800;color:#e6edf3;}
.logo span{color:#2962ff;}
.body{padding:40px;}
.greeting{font-size:22px;font-weight:700;color:#e6edf3;margin:0 0 12px;}
.text{font-size:15px;color:#8b949e;line-height:1.7;margin:0 0 24px;}
.summary{background:#0d1117;border:1px solid #30363d;border-radius:8px;padding:20px 24px;margin:24px 0;}
.row{display:flex;padding:8px 0;border-bottom:1px solid #21262d;}
.row:last-child{border-bottom:none;}
.lbl{font-size:12px;font-weight:600;color:#484f58;text-transform:uppercase;letter-spacing:.08em;width:80px;flex-shrink:0;padding-top:2px;}
.val{font-size:14px;color:#e6edf3;flex:1;word-break:break-word;}
.msg{background:#0d1117;border:1px solid #30363d;border-left:3px solid #2962ff;border-radius:8px;padding:16px 20px;margin:20px 0;font-size:14px;color:#8b949e;line-height:1.7;white-space:pre-wrap;}
.cta{text-align:center;margin:32px 0 8px;}
.btn{display:inline-block;background:#2962ff;color:#fff;text-decoration:none;padding:14px 32px;border-radius:8px;font-size:15px;font-weight:600;}
.footer{padding:24px 40px;text-align:center;border-top:1px solid #30363d;}
.ftext{font-size:12px;color:#484f58;line-height:1.6;margin:0;}
.ftext a{color:#2962ff;text-decoration:none;}
</style></head><body>
<div class="wrapper"><div class="card">
<div class="header"><div class="logo">Wiz<span>Investments</span></div></div>
<div class="body">
<p class="greeting">Thanks, ' . esc_html($name) . '!</p>
<p class="text">We have received your message and will get back to you within 24 hours on business days.</p>
<div class="summary">
<div class="row"><span class="lbl">Name</span><span class="val">' . esc_html($name) . '</span></div>
<div class="row"><span class="lbl">Email</span><span class="val">' . esc_html($email) . '</span></div>
<div class="row"><span class="lbl">Subject</span><span class="val">' . esc_html($subject) . '</span></div>
</div>
<div class="msg">' . esc_html($message) . '</div>
<div class="cta"><a href="' . esc_url(home_url('/analytics-dashboard/')) . '" class="btn">Explore Analytics</a></div>
</div>
<div class="footer"><p class="ftext">
You are receiving this because you submitted a message on <a href="' . esc_url(home_url()) . '">WizInvestments</a>.<br>
&copy; ' . date('Y') . ' WizInvestments &nbsp;&bull;&nbsp;
<a href="' . esc_url(home_url('/privacy-policy/')) . '">Privacy Policy</a> &nbsp;&bull;&nbsp;
<a href="' . esc_url(home_url('/terms-of-service/')) . '">Terms of Service</a>
</p></div>
</div></div></body></html>';
    }

    // ─── AUTH ENDPOINTS ───────────────────────────────────────

    public static function handle_reset_password() {
        if (!isset($_POST['wiz_nonce']) || !wp_verify_nonce($_POST['wiz_nonce'], 'wiz_reset_nonce')) {
            wp_send_json_error('Security check failed. Please refresh and try again.');
        }

        $token            = sanitize_text_field($_POST['token'] ?? '');
        $new_password     = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($token)) {
            wp_send_json_error('Invalid reset token.');
        }

        $result = wiz_reset_password_with_token($token, $new_password, $confirm_password);

        if (!$result['success']) {
            wp_send_json_error($result['message']);
        }

        // Preserve email_verified and re-authenticate
        $user_id        = $result['user_id'];
        $email_verified = get_user_meta($user_id, 'email_verified', true);
        update_user_meta($user_id, 'email_verified', $email_verified ?: true);
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, false);

        wp_send_json_success('Password reset successfully! Redirecting to dashboard...');
    }

    public static function handle_forgot_password() {
        if (!isset($_POST['wiz_nonce']) || !wp_verify_nonce($_POST['wiz_nonce'], 'wiz_forgot_nonce')) {
            wp_send_json_error('Security check failed. Please refresh and try again.');
        }

        $email  = sanitize_email($_POST['email'] ?? '');
        $result = wiz_request_password_reset($email);

        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
}
