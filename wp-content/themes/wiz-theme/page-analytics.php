<?php
/*
Template Name: Analytics Dashboard
*/
get_header();

if (!is_user_logged_in()) {
    echo '<section class="page-content"><h1>Analytics Dashboard</h1><p>This trading analytics dashboard is only available to registered users. Please <a href="' . esc_url(wp_login_url(get_permalink())) . '">log in</a> to continue.</p></section>';
    get_footer();
    return;
}
?>

<section class="page-content">
  <h1>Analytics Dashboard</h1>
  <p>Welcome back, <?php echo esc_html(wp_get_current_user()->display_name ?: wp_get_current_user()->user_login); ?>. Use the controls below to generate simulated trading scenarios and evaluate metrics.</p>
</section>

<section class="analytics-card">
  <div id="analytics-root">
    <div id="controls">
      <label>Initial Capital: <input id="initial-capital" type="number" value="10000"></label>
      <label>Days: <input id="sim-days" type="number" value="252" min="10" max="2000"></label>
      <label>Volatility: <input id="volatility" type="number" value="0.2" step="0.01" min="0.01" max="2"></label>
      <label>Scenario: 
        <select id="scenario-select">
          <option value="sma">SMA Crossover</option>
          <option value="random">Random Trades</option>
        </select>
      </label>
      <button id="run-sim">Run Simulation</button>
      <button id="download-csv">Download CSV</button>
    </div>
    <div class="results-grid">
      <div id="analytics-results"></div>
      <div class="analytics-card">
        <canvas id="equityChart" aria-label="Equity curve" role="img"></canvas>
      </div>
    </div>

    <div class="analytics-card">
      <h3>Trade List</h3>
      <div id="trade-list">(no trades yet)</div>
    </div>
  </div>
</section>

<?php get_footer(); ?>
