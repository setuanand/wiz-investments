<?php
get_header();
?>

<section class="hero">
  <div class="hero-content">
    <h1>Where the world does wealth.</h1>
    <p>Real markets, real data, real insights. Explore trading analytics and build your investment strategy.</p>
    <div style="display: flex; gap: var(--spacing-md); margin-top: var(--spacing-lg); flex-wrap: wrap;">
      <a href="<?php echo esc_url( get_permalink( get_page_by_path( 'analytics-dashboard' ) ) ); ?>" class="cta-button">Explore Analytics</a>
      <?php if (!is_user_logged_in()) : ?>
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('register')); ?>" class="cta-button" style="background: rgba(0, 212, 255, 0.1); color: var(--color-accent); border: 1px solid var(--color-accent);">Get Started Free</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="dashboard-section">
  <h2>Market Snapshot</h2>
  <div class="widget-grid">
    <div class="market-card">
      <div class="market-card-symbol">Stocks</div>
      <div class="market-card-price">7,200</div>
      <div class="market-card-change negative">−0.41% Today</div>
      <div class="market-card-description">S&P 500 Index</div>
    </div>
    <div class="market-card">
      <div class="market-card-symbol">Crypto</div>
      <div class="market-card-price">$80.2K</div>
      <div class="market-card-change positive">+2.02% 24h</div>
      <div class="market-card-description">Bitcoin (BTC)</div>
    </div>
    <div class="market-card">
      <div class="market-card-symbol">Commodities</div>
      <div class="market-card-price">4,525</div>
      <div class="market-card-change negative">−2.58% 1M</div>
      <div class="market-card-description">Gold (XAUUSD)</div>
    </div>
    <div class="market-card">
      <div class="market-card-symbol">Forex</div>
      <div class="market-card-price">1.1680</div>
      <div class="market-card-change negative">−0.23% Today</div>
      <div class="market-card-description">EUR/USD Pair</div>
    </div>
  </div>
</section>

<section class="dashboard-section">
  <h2>Trending Symbols</h2>
  <div class="widget-card">
    <ul class="compact-list">
      <li><a href="#">Apple (AAPL)</a> <span class="compact-list-value positive">+5.2%</span></li>
      <li><a href="#">NVIDIA (NVDA)</a> <span class="compact-list-value positive">+3.8%</span></li>
      <li><a href="#">Tesla (TSLA)</a> <span class="compact-list-value negative">−1.5%</span></li>
      <li><a href="#">Amazon (AMZN)</a> <span class="compact-list-value positive">+2.1%</span></li>
      <li><a href="#">Ethereum (ETH)</a> <span class="compact-list-value positive">+1.56%</span></li>
    </ul>
  </div>
</section>

<section class="dashboard-section">
  <h2>Our Services</h2>
  <div class="services-grid">
    <div class="service-card">
      <h3>Investment Planning</h3>
      <p>Customized portfolios tailored to your risk tolerance and goals.</p>
    </div>
    <div class="service-card">
      <h3>Trading Simulations</h3>
      <p>Analyze hypothetical scenarios with our interactive analytics tools.</p>
    </div>
    <div class="service-card">
      <h3>Wealth Management</h3>
      <p>Comprehensive advice for long-term financial growth.</p>
    </div>
  </div>
</section>

<section class="dashboard-section">
  <h2>Key Metrics</h2>
  <div class="stats-grid">
    <div class="stat-box">
      <div class="stat-label">Market Cap</div>
      <div class="stat-value">$2.62T</div>
    </div>
    <div class="stat-box">
      <div class="stat-label">Bitcoin Dominance</div>
      <div class="stat-value">61.22%</div>
    </div>
    <div class="stat-box">
      <div class="stat-label">30-Day Change</div>
      <div class="stat-value">+14.6%</div>
    </div>
    <div class="stat-box">
      <div class="stat-label">Active Users</div>
      <div class="stat-value">100M+</div>
    </div>
  </div>
</section>

<?php get_footer(); ?>
