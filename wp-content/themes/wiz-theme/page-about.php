<?php
/*
Template Name: About
*/
get_header();
?>

<!-- HERO -->
<section class="about-hero">
  <div class="container">
    <div class="about-hero-inner">
      <span class="section-label">Our Story</span>
      <h1 class="about-title">Built for investors<br>who think <span class="highlight">differently.</span></h1>
      <p class="about-lead">WizInvestments was founded on a simple belief — that powerful financial analytics shouldn't be reserved for institutions. We built a platform that gives every investor access to the tools, data, and insights that were once only available to professionals.</p>
      <div class="about-hero-actions">
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('analytics-dashboard')); ?>" class="btn btn-primary btn-lg">Explore Analytics</a>
        <a href="<?php echo esc_url(wiz_get_page_url_by_slug('contact')); ?>" class="btn btn-secondary btn-lg">Get In Touch</a>
      </div>
    </div>
  </div>
</section>

<!-- MISSION -->
<section class="section" style="background: var(--bg-surface); border-top: 1px solid var(--border-muted); border-bottom: 1px solid var(--border-muted);">
  <div class="container">
    <div class="about-mission">
      <div class="about-mission-text">
        <span class="section-label">Our Mission</span>
        <h2 class="section-title" style="text-align:left;">Democratising financial intelligence.</h2>
        <p style="color: var(--text-secondary); line-height: 1.8; font-size: 1.05rem;">We believe every investor deserves access to professional-grade analytics. Our mission is to make institutional-quality market data, simulations, and insights accessible to individuals at every stage of their investment journey.</p>
        <p style="color: var(--text-secondary); line-height: 1.8; font-size: 1.05rem; margin-top: 1rem;">Whether you're building your first portfolio or managing complex strategies across multiple asset classes, WizInvestments gives you the clarity to make better decisions — faster.</p>
      </div>
      <div class="about-mission-stats">
        <div class="about-stat-card">
          <div class="about-stat-value">Live</div>
          <div class="about-stat-label">Market Data</div>
          <div class="about-stat-desc">Real-time prices across stocks, crypto, forex, and indices</div>
        </div>
        <div class="about-stat-card">
          <div class="about-stat-value">Free</div>
          <div class="about-stat-label">To Get Started</div>
          <div class="about-stat-desc">Create your account and access core analytics at no cost</div>
        </div>
        <div class="about-stat-card">
          <div class="about-stat-value">5+</div>
          <div class="about-stat-label">Asset Classes</div>
          <div class="about-stat-desc">Stocks, crypto, forex, commodities, and indices</div>
        </div>
        <div class="about-stat-card">
          <div class="about-stat-value">24h</div>
          <div class="about-stat-label">Support Response</div>
          <div class="about-stat-desc">Our team responds to every enquiry within one business day</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- WHAT WE OFFER -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <span class="section-label">What We Offer</span>
      <h2 class="section-title">Everything you need to invest smarter.</h2>
      <p class="section-desc">From live market data to portfolio simulations — WizInvestments brings institutional tools to individual investors.</p>
    </div>
    <div class="about-features">
      <div class="about-feature-card">
        <div class="about-feature-icon">📈</div>
        <h3>Live Market Data</h3>
        <p>Real-time prices, charts, and performance data across global stocks, crypto, forex, and commodities — powered by TradingView.</p>
      </div>
      <div class="about-feature-card">
        <div class="about-feature-icon">🔬</div>
        <h3>Trading Simulations</h3>
        <p>Test your strategies with our analytics engine. Run simulations, measure Sharpe ratios, and stress-test your approach before committing capital.</p>
      </div>
      <div class="about-feature-card">
        <div class="about-feature-icon">🌐</div>
        <h3>Global Coverage</h3>
        <p>Access markets from the US, Europe, Asia, and beyond. S&amp;P 500, Nasdaq, Dow Jones, DAX, Nikkei, and crypto markets all in one place.</p>
      </div>
      <div class="about-feature-card">
        <div class="about-feature-icon">🔒</div>
        <h3>Secure & Private</h3>
        <p>Your data stays yours. We use encrypted authentication, hashed passwords, and secure sessions. We never sell your data.</p>
      </div>
      <div class="about-feature-card">
        <div class="about-feature-icon">⚡</div>
        <h3>Built for Speed</h3>
        <p>A fast, responsive platform that works seamlessly on desktop and mobile. No clutter, no ads — just the data you need.</p>
      </div>
      <div class="about-feature-card">
        <div class="about-feature-icon">🏦</div>
        <h3>Wealth Planning</h3>
        <p>Go beyond charts. Our tools help you think long-term — retirement planning, portfolio diversification, and risk management guidance.</p>
      </div>
    </div>
  </div>
</section>

<!-- VALUES -->
<section class="section" style="background: var(--bg-surface); border-top: 1px solid var(--border-muted); border-bottom: 1px solid var(--border-muted);">
  <div class="container">
    <div class="section-header">
      <span class="section-label">Our Values</span>
      <h2 class="section-title">What we stand for.</h2>
    </div>
    <div class="about-values">
      <div class="about-value-item">
        <div class="about-value-number">01</div>
        <h3>Transparency</h3>
        <p>We believe in clear, honest communication — no hidden fees, no misleading claims, no fine print. What you see is what you get.</p>
      </div>
      <div class="about-value-item">
        <div class="about-value-number">02</div>
        <h3>Accessibility</h3>
        <p>Professional tools shouldn't require a professional budget. We're committed to keeping core analytics free and accessible to all.</p>
      </div>
      <div class="about-value-item">
        <div class="about-value-number">03</div>
        <h3>Education First</h3>
        <p>We don't tell you what to buy. We give you the tools and knowledge to make your own informed decisions — that's how real wealth is built.</p>
      </div>
      <div class="about-value-item">
        <div class="about-value-number">04</div>
        <h3>Continuous Improvement</h3>
        <p>Markets evolve, and so do we. We continuously improve our platform, add new data sources, and listen to our community.</p>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section">
  <div class="container">
    <div class="cta-banner">
      <h2>Ready to invest smarter?</h2>
      <p>Join WizInvestments today and get access to live market data, trading simulations, and professional analytics — free to start.</p>
      <div class="btn-group" style="justify-content: center;">
        <?php if (!is_user_logged_in()) : ?>
          <a href="<?php echo esc_url(wiz_get_page_url_by_slug('register')); ?>" class="btn btn-gold btn-lg">Create Free Account</a>
          <a href="<?php echo esc_url(wiz_get_page_url_by_slug('contact')); ?>" class="btn btn-secondary btn-lg">Talk to Us</a>
        <?php else : ?>
          <a href="<?php echo esc_url(wiz_get_page_url_by_slug('analytics-dashboard')); ?>" class="btn btn-gold btn-lg">Go to Analytics</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php get_footer(); ?>
