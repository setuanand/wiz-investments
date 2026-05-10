<?php
/*
Template Name: Services
*/
get_header();
?>

<!-- HERO -->
<section class="about-hero">
  <div class="container">
    <div class="about-hero-inner">
      <span class="section-label">What We Offer</span>
      <h1 class="about-title">Tools built for<br>every <span class="highlight">investor.</span></h1>
      <p class="about-lead">From live market analytics to comprehensive wealth planning — WizInvestments gives you the professional tools you need to make smarter, faster, and more confident investment decisions.</p>
      <div class="about-hero-actions">
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('register')); ?>" class="btn btn-primary btn-lg">Get Started Free</a>
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('contact')); ?>" class="btn btn-secondary btn-lg">Talk to Us</a>
      </div>
    </div>
  </div>
</section>

<!-- SERVICES GRID -->
<section class="section" style="background: var(--bg-surface); border-top: 1px solid var(--border-muted); border-bottom: 1px solid var(--border-muted);">
  <div class="container">
    <div class="section-header">
      <span class="section-label">Core Services</span>
      <h2 class="section-title">Everything in one platform.</h2>
      <p class="section-desc">Six core services designed to cover every stage of your investment journey.</p>
    </div>
    <div class="services-detail-grid">

      <div class="service-detail-card">
        <div class="service-detail-icon">📈</div>
        <h3>Investment Planning</h3>
        <p>We help you build customized investment plans aligned with your financial goals, risk tolerance, and time horizon. Our approach spans diversified portfolios across stocks, bonds, crypto, and alternative assets.</p>
        <ul class="service-detail-list">
          <li>Goal-based portfolio construction</li>
          <li>Risk tolerance assessment</li>
          <li>Asset class diversification</li>
          <li>Long-term strategy roadmaps</li>
        </ul>
      </div>

      <div class="service-detail-card">
        <div class="service-detail-icon">🔬</div>
        <h3>Trading Simulations & Analytics</h3>
        <p>Explore hypothetical trading scenarios with our interactive analytics engine. Run SMA crossover strategies, random simulations, and performance analysis — all without risking real capital.</p>
        <ul class="service-detail-list">
          <li>SMA crossover strategy simulation</li>
          <li>Equity curve visualization</li>
          <li>Sharpe ratio & performance metrics</li>
          <li>CSV export of trade history</li>
        </ul>
      </div>

      <div class="service-detail-card">
        <div class="service-detail-icon">🏦</div>
        <h3>Wealth Management</h3>
        <p>Comprehensive wealth management advisory covering tax planning, estate strategy, and retirement roadmaps. We help you see the full picture of your financial life — not just your portfolio.</p>
        <ul class="service-detail-list">
          <li>Retirement planning frameworks</li>
          <li>Tax-efficient investment strategies</li>
          <li>Estate planning considerations</li>
          <li>Wealth preservation tactics</li>
        </ul>
      </div>

      <div class="service-detail-card">
        <div class="service-detail-icon">⚡</div>
        <h3>Portfolio Optimization</h3>
        <p>Regular portfolio reviews and rebalancing to maximise returns while minimising risk. Our tools adapt your strategy to changing market conditions and your evolving financial goals.</p>
        <ul class="service-detail-list">
          <li>Portfolio rebalancing analysis</li>
          <li>Sector exposure breakdown</li>
          <li>Correlation & diversification scoring</li>
          <li>Performance benchmarking</li>
        </ul>
      </div>

      <div class="service-detail-card">
        <div class="service-detail-icon">🌐</div>
        <h3>Global Market Access</h3>
        <p>Monitor and analyse markets across the US, Europe, Asia, and emerging economies. Stocks, crypto, forex, commodities, and indices — all accessible from a single unified dashboard.</p>
        <ul class="service-detail-list">
          <li>S&amp;P 500, Nasdaq, Dow Jones live data</li>
          <li>Crypto — Bitcoin, Ethereum, and more</li>
          <li>Forex — major and minor pairs</li>
          <li>Global indices and heatmaps</li>
        </ul>
      </div>

      <div class="service-detail-card">
        <div class="service-detail-icon">🔒</div>
        <h3>Secure Member Platform</h3>
        <p>A bank-grade secure platform with encrypted authentication, hashed passwords, and session management. Your data and analytics are completely private — we never sell or share your information.</p>
        <ul class="service-detail-list">
          <li>Email-verified account creation</li>
          <li>Secure password reset flows</li>
          <li>Private member dashboard</li>
          <li>GDPR-conscious data handling</li>
        </ul>
      </div>

    </div>
  </div>
</section>

<!-- MEMBERSHIP TIERS -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <span class="section-label">Membership</span>
      <h2 class="section-title">Start free. Grow with us.</h2>
      <p class="section-desc">Access core analytics at no cost. Premium tiers with advanced features coming soon.</p>
    </div>
    <div class="pricing-grid">

      <div class="pricing-card">
        <div class="pricing-badge">Current</div>
        <h3 class="pricing-tier">Subscriber</h3>
        <div class="pricing-price">Free</div>
        <p class="pricing-desc">Everything you need to get started with market analytics.</p>
        <ul class="pricing-features">
          <li>✓ Live market data & charts</li>
          <li>✓ Analytics dashboard access</li>
          <li>✓ Trading simulations</li>
          <li>✓ Stock, crypto & forex heatmaps</li>
          <li>✓ Member dashboard</li>
        </ul>
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('register')); ?>" class="btn btn-primary" style="width:100%; text-align:center;">Get Started Free</a>
      </div>

      <div class="pricing-card pricing-card--featured">
        <div class="pricing-badge pricing-badge--gold">Coming Soon</div>
        <h3 class="pricing-tier">Professional</h3>
        <div class="pricing-price">TBA</div>
        <p class="pricing-desc">Advanced tools for serious investors and active traders.</p>
        <ul class="pricing-features">
          <li>✓ Everything in Subscriber</li>
          <li>✓ Advanced simulation scenarios</li>
          <li>✓ Portfolio optimisation tools</li>
          <li>✓ Custom watchlists</li>
          <li>✓ Priority support</li>
        </ul>
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('contact')); ?>" class="btn btn-secondary" style="width:100%; text-align:center;">Join Waitlist</a>
      </div>

      <div class="pricing-card">
        <div class="pricing-badge pricing-badge--muted">Coming Soon</div>
        <h3 class="pricing-tier">Institutional</h3>
        <div class="pricing-price">Custom</div>
        <p class="pricing-desc">Enterprise-grade analytics for firms and professional advisors.</p>
        <ul class="pricing-features">
          <li>✓ Everything in Professional</li>
          <li>✓ Multi-user access</li>
          <li>✓ Custom data integrations</li>
          <li>✓ Dedicated account manager</li>
          <li>✓ SLA-backed support</li>
        </ul>
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('contact')); ?>" class="btn btn-secondary" style="width:100%; text-align:center;">Contact Us</a>
      </div>

    </div>
  </div>
</section>

<!-- CTA -->
<div class="container" style="padding-bottom: var(--space-2xl);">
  <div class="cta-banner">
    <h2>Ready to get started?</h2>
    <p>Join WizInvestments today and access live market data, trading simulations, and professional analytics — free forever on the Subscriber plan.</p>
    <div class="btn-group" style="justify-content: center;">
      <?php if (!is_user_logged_in()) : ?>
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('register')); ?>" class="btn btn-gold btn-lg">Create Free Account</a>
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('analytics-dashboard')); ?>" class="btn btn-secondary btn-lg">View Analytics</a>
      <?php else : ?>
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('analytics-dashboard')); ?>" class="btn btn-gold btn-lg">Go to Analytics</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php get_footer(); ?>
