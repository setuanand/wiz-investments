<?php
get_header();
?>

<section class="hero">
  <div class="hero-content">
    <h1>Welcome to Wiz Investments</h1>
    <p>Expert wealth management and investment strategies for your financial future. Discover hypothetical trading scenarios and analytics to inform your decisions.</p>
    <a href="<?php echo esc_url( get_permalink( get_page_by_path( 'analytics-dashboard' ) ) ); ?>" class="cta-button">Explore Analytics</a>
  </div>
</section>

<section class="services-preview">
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

<section class="analytics-preview">
  <h2>Analytics Dashboard Preview</h2>
  <p>Try the full analytics experience by logging in and using the analytics dashboard page.</p>
</section>

<?php get_footer(); ?>