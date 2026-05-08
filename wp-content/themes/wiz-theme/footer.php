</div><!-- .site-content -->

<footer class="site-footer">
  <div class="container">

    <div class="footer-grid">

      <div class="footer-brand">
        <a class="site-title" href="<?php echo esc_url( home_url('/') ); ?>">
          Wiz<span>Investments</span>
        </a>
        <p>Real markets, real data, real insights. Your platform for investment analytics and wealth intelligence.</p>
      </div>

      <div class="footer-col">
        <h4>Platform</h4>
        <ul>
          <li><a href="<?php echo esc_url( home_url('/') ); ?>">Home</a></li>
          <li><a href="<?php echo esc_url( wiz_get_page_url_by_slug('analytics-dashboard') ); ?>">Analytics</a></li>
          <li><a href="<?php echo esc_url( wiz_get_page_url_by_slug('services') ); ?>">Services</a></li>
          <li><a href="<?php echo esc_url( wiz_get_page_url_by_slug('about') ); ?>">About</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Account</h4>
        <ul>
          <?php if ( is_user_logged_in() ) : ?>
            <li><a href="<?php echo esc_url( wiz_get_page_url_by_slug('dashboard') ); ?>">Dashboard</a></li>
            <li><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">Logout</a></li>
          <?php else : ?>
            <li><a href="<?php echo esc_url( wiz_get_page_url_by_slug('login') ); ?>">Log In</a></li>
            <li><a href="<?php echo esc_url( wiz_get_page_url_by_slug('register') ); ?>">Register</a></li>
          <?php endif; ?>
          <li><a href="<?php echo esc_url( wiz_get_page_url_by_slug('contact') ); ?>">Contact</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Legal</h4>
        <ul>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms of Service</a></li>
          <li><a href="#">Disclaimer</a></li>
        </ul>
        <div style="margin-top: 1.5rem; font-size: 0.75rem; color: var(--text-muted); line-height: 1.5;">
          Market data provided by<br>
          <a href="https://www.tradingview.com" target="_blank" rel="noopener" style="color: var(--text-muted);">TradingView</a>
        </div>
      </div>

    </div>

    <div class="footer-bottom">
      <p>&copy; <?php echo date('Y'); ?> WizInvestments. All rights reserved.</p>
      <p>For informational purposes only. Not financial advice.</p>
    </div>

  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
