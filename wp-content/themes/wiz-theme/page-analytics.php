<?php
/*
Template Name: Analytics Dashboard
*/
get_header();

if (!is_user_logged_in()) :
?>
<section class="about-hero">
  <div class="container">
    <div class="about-hero-inner">
      <span class="section-label">Members Only</span>
      <h1 class="about-title">Analytics <span class="highlight">Dashboard</span></h1>
      <p class="about-lead">Our trading analytics dashboard is available to registered members. Create a free account or log in to access simulations, equity curves, and performance metrics.</p>
      <div class="about-hero-actions">
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('register')); ?>" class="btn btn-primary btn-lg">Create Free Account</a>
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('login')); ?>" class="btn btn-secondary btn-lg">Log In</a>
      </div>
    </div>
  </div>
</section>
<?php
  get_footer();
  return;
endif;

$user = wp_get_current_user();
$display = $user->display_name ?: $user->user_login ?: $user->user_email;
?>

<div class="analytics-page">
  <div class="container">

    <!-- Header -->
    <div class="analytics-page-header">
      <div>
        <span class="section-label">Member Analytics</span>
        <h1 class="analytics-page-title">Trading Simulator</h1>
        <p class="analytics-page-sub">Welcome back, <strong><?php echo esc_html($display); ?></strong>. Configure your simulation parameters and run an analysis.</p>
      </div>
      <a href="<?php echo esc_url(wiz_get_page_url_by_slug('dashboard')); ?>" class="btn btn-secondary">← Dashboard</a>
    </div>

    <!-- Controls Card -->
    <div class="dashboard-card" style="margin-bottom: var(--space-lg);">
      <h3 style="margin-bottom: var(--space-lg); font-size: 1rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600;">Simulation Parameters</h3>
      <div id="controls" class="analytics-controls">
        <div class="form-group">
          <label class="form-label">Initial Capital ($)</label>
          <input class="form-control" id="initial-capital" type="number" value="10000" min="100">
        </div>
        <div class="form-group">
          <label class="form-label">Trading Days</label>
          <input class="form-control" id="sim-days" type="number" value="252" min="10" max="2000">
        </div>
        <div class="form-group">
          <label class="form-label">Volatility</label>
          <input class="form-control" id="volatility" type="number" value="0.2" step="0.01" min="0.01" max="2">
        </div>
        <div class="form-group">
          <label class="form-label">Scenario</label>
          <select class="form-control" id="scenario-select">
            <option value="sma">SMA Crossover</option>
            <option value="random">Random Trades</option>
          </select>
        </div>
        <div class="analytics-controls-actions">
          <button id="run-sim" class="btn btn-primary">Run Simulation</button>
          <button id="download-csv" class="btn btn-secondary">Download CSV</button>
        </div>
      </div>
    </div>

    <!-- Results -->
    <div class="analytics-results-grid">
      <div class="dashboard-card analytics-chart-card">
        <h3 style="margin-bottom: var(--space-lg); font-size: 1rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600;">Equity Curve</h3>
        <canvas id="equityChart" aria-label="Equity curve" role="img"></canvas>
      </div>
      <div class="dashboard-card">
        <h3 style="margin-bottom: var(--space-lg); font-size: 1rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600;">Performance Metrics</h3>
        <div id="analytics-results" class="analytics-metrics">(Run a simulation to see metrics)</div>
      </div>
    </div>

    <!-- Trade List -->
    <div class="dashboard-card" style="margin-top: var(--space-lg);">
      <h3 style="margin-bottom: var(--space-lg); font-size: 1rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600;">Trade History</h3>
      <div id="trade-list" class="analytics-trade-list">(No trades yet — run a simulation above)</div>
    </div>

  </div>
</div>

<?php get_footer(); ?>
