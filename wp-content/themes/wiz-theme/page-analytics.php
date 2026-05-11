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
      <p class="about-lead">Our professional analytics dashboard is available to registered members. Create a free account or log in to access simulations, portfolio tools, and live market charts.</p>
      <div class="about-hero-actions">
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('register')); ?>" class="btn btn-primary btn-lg">Create Free Account</a>
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('login')); ?>" class="btn btn-secondary btn-lg">Log In</a>
      </div>
    </div>
  </div>
</section>
<?php get_footer(); return; endif;

$user = wp_get_current_user();
$display = $user->display_name ?: $user->user_email;
?>

<div class="analytics-page">
  <div class="container">

    <!-- Page Header -->
    <div class="analytics-page-header">
      <div>
        <span class="section-label">Member Analytics</span>
        <h1 class="analytics-page-title">Investment Dashboard</h1>
        <p class="analytics-page-sub">Welcome back, <strong style="color:var(--text-primary)"><?php echo esc_html($display); ?></strong></p>
      </div>
      <a href="<?php echo esc_url(wiz_get_page_url_by_slug('dashboard')); ?>" class="btn btn-secondary">&larr; Dashboard</a>
    </div>

    <!-- Tabs -->
    <div class="analytics-tabs">
      <button class="analytics-tab active" data-tab="simulator">
        <span class="tab-icon">📊</span> Strategy Simulator
      </button>
      <button class="analytics-tab" data-tab="portfolio">
        <span class="tab-icon">💼</span> Portfolio
      </button>
      <button class="analytics-tab" data-tab="livechart">
        <span class="tab-icon">📈</span> Live Chart
      </button>
    </div>

    <!-- ===================== TAB 1: SIMULATOR ===================== -->
    <div class="analytics-tab-content active" id="tab-simulator">

      <!-- Controls -->
      <div class="dashboard-card sim-controls-card">
        <div class="sim-controls-header">
          <h3 class="card-section-title">Simulation Parameters</h3>
          <div class="sim-data-toggle">
            <span class="toggle-label">Simulated</span>
            <label class="toggle-switch">
              <input type="checkbox" id="use-real-data">
              <span class="toggle-slider"></span>
            </label>
            <span class="toggle-label">Real Data <span class="badge-premium">PREMIUM</span></span>
          </div>
        </div>
        <div class="sim-controls-grid">
          <div class="form-group">
            <label class="form-label">Initial Capital ($)
              <span class="tooltip-wrap" data-tip="The starting amount of money in your simulated portfolio.">ⓘ</span>
            </label>
            <input class="form-control" id="initial-capital" type="number" value="10000" min="100" step="1000">
          </div>
          <div class="form-group">
            <label class="form-label">Trading Days
              <span class="tooltip-wrap" data-tip="Number of trading days to simulate. 252 = 1 full trading year.">ⓘ</span>
            </label>
            <input class="form-control" id="sim-days" type="number" value="252" min="30" max="1260">
          </div>
          <div class="form-group">
            <label class="form-label">Annual Volatility (σ)
              <span class="tooltip-wrap" data-tip="How much the price fluctuates. SPY ≈ 0.18, crypto ≈ 0.6–1.0.">ⓘ</span>
            </label>
            <input class="form-control" id="volatility" type="number" value="0.18" step="0.01" min="0.01" max="2.0">
          </div>
          <div class="form-group">
            <label class="form-label">Annual Drift (μ)
              <span class="tooltip-wrap" data-tip="Expected annual return of the asset. S&P 500 long-run avg ≈ 0.10.">ⓘ</span>
            </label>
            <input class="form-control" id="drift" type="number" value="0.10" step="0.01" min="-0.5" max="2.0">
          </div>
          <div class="form-group">
            <label class="form-label">Strategy
              <span class="tooltip-wrap" data-tip="The trading strategy to simulate against the price series.">ⓘ</span>
            </label>
            <select class="form-control" id="scenario-select">
              <option value="sma">SMA Crossover (10/50)</option>
              <option value="rsi">RSI Mean Reversion</option>
              <option value="buyhold">Buy & Hold</option>
            </select>
          </div>
          <div class="sim-controls-actions">
            <button id="run-sim" class="btn btn-primary">▶ Run</button>
            <button id="play-sim" class="btn btn-secondary">⏵ Animate</button>
            <select class="form-control" id="play-speed" style="width:auto;">
              <option value="50">Fast</option>
              <option value="100" selected>Normal</option>
              <option value="300">Slow</option>
            </select>
          </div>
        </div>
        <div id="real-data-notice" style="display:none;" class="form-notice" style="margin-top:1rem;">
          Real data mode fetches SPY historical prices. Strategy is applied to actual market data.
        </div>
      </div>

      <!-- Charts Row -->
      <div class="sim-charts-grid">
        <div class="dashboard-card">
          <h3 class="card-section-title">Price Chart <span id="price-chart-badge" class="badge-sim">SIM</span></h3>
          <div class="chart-wrap">
            <canvas id="priceChart"></canvas>
          </div>
        </div>
        <div class="dashboard-card">
          <h3 class="card-section-title">Equity Curve vs Buy & Hold</h3>
          <div class="chart-wrap">
            <canvas id="equityChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Metrics -->
      <div class="sim-metrics-grid" id="sim-metrics">
        <?php
        $metrics = [
          ['id'=>'m-return',   'label'=>'Total Return',   'tip'=>'Total profit or loss as a percentage of initial capital.'],
          ['id'=>'m-cagr',     'label'=>'CAGR',           'tip'=>'Compound Annual Growth Rate — annualised return if held for multiple years.'],
          ['id'=>'m-sharpe',   'label'=>'Sharpe Ratio',   'tip'=>'Return per unit of risk. Above 1.0 is good, above 2.0 is excellent.'],
          ['id'=>'m-drawdown', 'label'=>'Max Drawdown',   'tip'=>'The largest peak-to-trough drop. Lower is better for risk management.'],
          ['id'=>'m-winrate',  'label'=>'Win Rate',       'tip'=>'Percentage of trades that were profitable.'],
          ['id'=>'m-avgwin',   'label'=>'Avg Win',        'tip'=>'Average profit on winning trades.'],
          ['id'=>'m-avgloss',  'label'=>'Avg Loss',       'tip'=>'Average loss on losing trades. Compare to Avg Win for risk/reward.'],
          ['id'=>'m-trades',   'label'=>'Total Trades',   'tip'=>'Total number of buy/sell roundtrips executed by the strategy.'],
        ];
        foreach ($metrics as $m) :
        ?>
        <div class="metric-card">
          <div class="metric-label">
            <?php echo esc_html($m['label']); ?>
            <span class="tooltip-wrap" data-tip="<?php echo esc_attr($m['tip']); ?>">ⓘ</span>
          </div>
          <div class="metric-value" id="<?php echo $m['id']; ?>">—</div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Trade History -->
      <div class="dashboard-card" style="margin-top:var(--space-lg);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-lg);">
          <h3 class="card-section-title">Trade History</h3>
          <button id="download-csv" class="btn btn-secondary btn-sm">⬇ Download CSV</button>
        </div>
        <div id="trade-list" class="trade-table-wrap">
          <p style="color:var(--text-muted); text-align:center; padding: 2rem 0;">Run a simulation to see trade history</p>
        </div>
      </div>
    </div>

    <!-- ===================== TAB 2: PORTFOLIO ===================== -->
    <div class="analytics-tab-content" id="tab-portfolio">

      <!-- Portfolio Sub-tabs -->
      <div class="portfolio-subtabs">
        <button class="portfolio-subtab active" data-subtab="tracker">📋 Performance Tracker</button>
        <button class="portfolio-subtab" data-subtab="optimizer">🎯 Portfolio Optimizer</button>
      </div>

      <!-- PERFORMANCE TRACKER -->
      <div class="portfolio-subtab-content active" id="subtab-tracker">

        <!-- Summary Bar -->
        <div class="dashboard-card portfolio-summary-card">
          <h3 class="card-section-title">Summary</h3>
          <div class="portfolio-summary-grid">
            <div class="portfolio-summary-item">
              <div class="ps-label">Total Value <span class="tooltip-wrap" data-tip="Current market value of all holdings.">ⓘ</span></div>
              <div class="ps-value" id="pt-total-value">$0.00</div>
            </div>
            <div class="portfolio-summary-item">
              <div class="ps-label">Total Cost <span class="tooltip-wrap" data-tip="Total amount originally invested.">ⓘ</span></div>
              <div class="ps-value" id="pt-total-cost">$0.00</div>
            </div>
            <div class="portfolio-summary-item">
              <div class="ps-label">Total P&L <span class="tooltip-wrap" data-tip="Profit or loss in dollar terms.">ⓘ</span></div>
              <div class="ps-value" id="pt-total-pnl">$0.00</div>
            </div>
            <div class="portfolio-summary-item">
              <div class="ps-label">Return % <span class="tooltip-wrap" data-tip="Total return as a percentage of cost.">ⓘ</span></div>
              <div class="ps-value" id="pt-total-pct">0.00%</div>
            </div>
            <div class="portfolio-summary-item">
              <div class="ps-label">Best Performer</div>
              <div class="ps-value gain" id="pt-best">—</div>
            </div>
            <div class="portfolio-summary-item">
              <div class="ps-label">Worst Performer</div>
              <div class="ps-value loss" id="pt-worst">—</div>
            </div>
          </div>
        </div>

        <!-- Period toggles + Chart -->
        <div class="dashboard-card" style="margin-top:var(--space-lg);">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-lg);">
            <h3 class="card-section-title">Portfolio Value</h3>
            <div class="period-toggles">
              <button class="period-btn active" data-period="1M">1M</button>
              <button class="period-btn" data-period="3M">3M</button>
              <button class="period-btn" data-period="6M">6M</button>
              <button class="period-btn" data-period="YTD">YTD</button>
              <button class="period-btn" data-period="1Y">1Y</button>
              <button class="period-btn" data-period="ALL">ALL</button>
            </div>
          </div>
          <div class="chart-wrap" style="height:280px;">
            <canvas id="portfolioChart"></canvas>
          </div>
        </div>

        <!-- Holdings Table -->
        <div class="dashboard-card" style="margin-top:var(--space-lg);">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-lg);">
            <h3 class="card-section-title">Holdings</h3>
            <button id="add-holding-btn" class="btn btn-primary btn-sm">+ Add Holding</button>
          </div>

          <!-- Add Holding Form -->
          <div id="add-holding-form" class="add-holding-form" style="display:none;">
            <div class="holding-form-grid">
              <div class="form-group">
                <label class="form-label">Asset Name</label>
                <input class="form-control" id="h-name" placeholder="e.g. Apple, Bitcoin">
              </div>
              <div class="form-group">
                <label class="form-label">Ticker (optional)</label>
                <input class="form-control" id="h-ticker" placeholder="e.g. AAPL, BTC-USD">
              </div>
              <div class="form-group">
                <label class="form-label">Units / Shares</label>
                <input class="form-control" id="h-units" type="number" placeholder="e.g. 10" step="any">
              </div>
              <div class="form-group">
                <label class="form-label">Buy Price ($)</label>
                <input class="form-control" id="h-buy-price" type="number" placeholder="e.g. 150.00" step="any">
              </div>
              <div class="form-group">
                <label class="form-label">Current Price ($)</label>
                <div style="display:flex; gap:0.5rem;">
                  <input class="form-control" id="h-current-price" type="number" placeholder="e.g. 175.00" step="any">
                  <button class="btn btn-secondary btn-sm" id="fetch-price-btn" title="Fetch live price">🔄 Live</button>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Purchase Date</label>
                <input class="form-control" id="h-date" type="date">
              </div>
            </div>
            <div style="display:flex; gap:0.75rem; margin-top:var(--space-md);">
              <button class="btn btn-primary" id="save-holding-btn">Save Holding</button>
              <button class="btn btn-secondary" id="cancel-holding-btn">Cancel</button>
            </div>
            <div id="fetch-price-status" style="margin-top:0.5rem; font-size:0.85rem; color:var(--text-secondary);"></div>
          </div>

          <div id="holdings-table-wrap" class="trade-table-wrap">
            <p style="color:var(--text-muted); text-align:center; padding: 2rem 0;">No holdings yet — add your first position above</p>
          </div>
        </div>

        <!-- Allocation Pie -->
        <div class="dashboard-card" style="margin-top:var(--space-lg);">
          <h3 class="card-section-title">Allocation</h3>
          <div style="max-width:400px; margin:0 auto;">
            <canvas id="allocationChart"></canvas>
          </div>
        </div>

      </div>

      <!-- PORTFOLIO OPTIMIZER -->
      <div class="portfolio-subtab-content" id="subtab-optimizer">
        <div class="dashboard-card">
          <h3 class="card-section-title">Portfolio Optimizer
            <span class="tooltip-wrap" data-tip="Enter up to 5 assets with their expected returns, volatility, and correlation. We'll find the optimal allocation using Modern Portfolio Theory.">ⓘ</span>
          </h3>
          <p style="color:var(--text-secondary); font-size:0.9rem; margin-bottom:var(--space-lg);">Enter up to 5 assets. The optimizer will plot the Efficient Frontier and identify the Maximum Sharpe and Minimum Variance portfolios.</p>

          <div id="optimizer-assets">
            <!-- Asset rows injected by JS -->
          </div>
          <button id="add-asset-btn" class="btn btn-secondary btn-sm" style="margin-top:var(--space-md);">+ Add Asset</button>
          <button id="run-optimizer-btn" class="btn btn-primary" style="margin-top:var(--space-md); margin-left:0.5rem;">Run Optimizer</button>
        </div>

        <!-- Efficient Frontier Chart -->
        <div class="dashboard-card" style="margin-top:var(--space-lg);">
          <h3 class="card-section-title">Efficient Frontier</h3>
          <div class="chart-wrap" style="height:400px;">
            <canvas id="frontierChart"></canvas>
          </div>
        </div>

        <!-- Optimal Portfolios -->
        <div class="sim-metrics-grid" style="margin-top:var(--space-lg);" id="optimizer-results">
          <div class="metric-card">
            <div class="metric-label">Max Sharpe Portfolio <span class="tooltip-wrap" data-tip="The portfolio with the best risk-adjusted return.">ⓘ</span></div>
            <div class="metric-value" id="opt-sharpe-weights">—</div>
          </div>
          <div class="metric-card">
            <div class="metric-label">Min Variance Portfolio <span class="tooltip-wrap" data-tip="The portfolio with the lowest possible risk.">ⓘ</span></div>
            <div class="metric-value" id="opt-minvar-weights">—</div>
          </div>
          <div class="metric-card">
            <div class="metric-label">Max Sharpe Return</div>
            <div class="metric-value" id="opt-sharpe-return">—</div>
          </div>
          <div class="metric-card">
            <div class="metric-label">Max Sharpe Volatility</div>
            <div class="metric-value" id="opt-sharpe-vol">—</div>
          </div>
          <div class="metric-card">
            <div class="metric-label">Max Sharpe Ratio</div>
            <div class="metric-value" id="opt-sharpe-ratio">—</div>
          </div>
          <div class="metric-card">
            <div class="metric-label">Min Variance Risk</div>
            <div class="metric-value" id="opt-minvar-vol">—</div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================== TAB 3: LIVE CHART ===================== -->
    <div class="analytics-tab-content" id="tab-livechart">
      <div class="dashboard-card" style="padding:0; overflow:hidden;">
        <div style="height:700px;" id="tradingview_analytics_chart"></div>
        <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
        <script type="text/javascript">
        (function initAnalyticsChart() {
          if (typeof TradingView === 'undefined') { setTimeout(initAnalyticsChart, 100); return; }
          new TradingView.widget({
            "autosize": true,
            "symbol": "NASDAQ:AAPL",
            "interval": "D",
            "timezone": "Etc/UTC",
            "theme": "dark",
            "style": "1",
            "locale": "en",
            "toolbar_bg": "#161b22",
            "enable_publishing": false,
            "allow_symbol_change": true,
            "container_id": "tradingview_analytics_chart",
            "hide_side_toolbar": false,
            "withdateranges": true,
            "save_image": false,
            "backgroundColor": "rgba(13,17,23,1)",
            "gridColor": "rgba(48,54,61,0.5)",
            "studies": ["RSI@tv-basicstudies", "MASimple@tv-basicstudies"]
          });
        })();
        </script>
      </div>
    </div>

  </div><!-- .container -->
</div><!-- .analytics-page -->

<?php get_footer(); ?>
