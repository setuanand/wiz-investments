<?php get_header(); ?>

<!-- =============================================
     TICKER TAPE — live scrolling prices
     ============================================= -->
<div class="ticker-section">
  <div class="tradingview-widget-container">
    <div class="tradingview-widget-container__widget"><div class="tv-loading-skeleton"></div></div>
    <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-ticker-tape.js" async>
    {
      "symbols": [
        { "proName": "FOREXCOM:SPXUSD", "title": "S&P 500" },
        { "proName": "FOREXCOM:NSXUSD", "title": "Nasdaq 100" },
        { "proName": "FX_IDC:EURUSD",   "title": "EUR/USD" },
        { "proName": "BITSTAMP:BTCUSD", "title": "Bitcoin" },
        { "proName": "BITSTAMP:ETHUSD", "title": "Ethereum" },
        { "description": "Gold",        "proName": "TVC:GOLD" },
        { "description": "Apple",       "proName": "NASDAQ:AAPL" },
        { "description": "NVIDIA",      "proName": "NASDAQ:NVDA" },
        { "description": "Tesla",       "proName": "NASDAQ:TSLA" },
        { "description": "Amazon",      "proName": "NASDAQ:AMZN" },
        { "description": "Microsoft",   "proName": "NASDAQ:MSFT" },
        { "description": "Crude Oil",   "proName": "TVC:USOIL" }
      ],
      "showSymbolLogo": true,
      "isTransparent": false,
      "displayMode": "adaptive",
      "colorTheme": "dark",
      "locale": "en"
    }
    </script>
  </div>
</div>

<!-- =============================================
     HERO
     ============================================= -->
<section class="hero">
  <div class="container">
    <div class="hero-badge fade-up">
      <span class="dot"></span>
      Live Market Data
    </div>

    <h1 class="fade-up fade-up-1">
      Where the world<br>
      does <span class="highlight">wealth.</span>
    </h1>

    <p class="hero-sub fade-up fade-up-2">
      Real markets. Real data. Real insights. Explore live trading analytics, build your investment strategy, and make smarter financial decisions.
    </p>

    <div class="hero-actions fade-up fade-up-3">
      <a href="<?php echo esc_url( wiz_get_page_url_by_slug('analytics-dashboard') ); ?>" class="btn btn-primary btn-lg">
        Explore Analytics
      </a>
      <?php if ( ! is_user_logged_in() ) : ?>
        <a href="<?php echo esc_url( wiz_get_page_url_by_slug('register') ); ?>" class="btn btn-secondary btn-lg">
          Get Started Free
        </a>
      <?php endif; ?>
    </div>

    <div class="hero-stats fade-up">
      <div class="hero-stat-item">
        <div class="hero-stat-value">150M+</div>
        <div class="hero-stat-label">Traders Worldwide</div>
      </div>
      <div class="hero-stat-item">
        <div class="hero-stat-value">50+</div>
        <div class="hero-stat-label">Exchanges Covered</div>
      </div>
      <div class="hero-stat-item">
        <div class="hero-stat-value">10K+</div>
        <div class="hero-stat-label">Instruments Tracked</div>
      </div>
      <div class="hero-stat-item">
        <div class="hero-stat-value">99.9%</div>
        <div class="hero-stat-label">Uptime</div>
      </div>
    </div>
  </div>
</section>

<!-- =============================================
     MINI CHART STRIP — S&P 500, Bitcoin, Gold
     ============================================= -->
<section class="section" style="padding-top: 0; padding-bottom: 0;">
  <div class="container">
    <div class="symbol-info-grid">

      <div class="tradingview-widget-container" style="height:220px;">
        <div class="tradingview-widget-container__widget" style="height:100%;"></div>
        <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-mini-symbol-overview.js" async>
        {
          "symbol": "FOREXCOM:SPXUSD",
          "width": "100%",
          "height": "100%",
          "locale": "en",
          "dateRange": "1M",
          "colorTheme": "dark",
          "isTransparent": true,
          "autosize": false,
          "largeChartUrl": ""
        }
        </script>
      </div>

      <div class="tradingview-widget-container" style="height:220px;">
        <div class="tradingview-widget-container__widget" style="height:100%;"></div>
        <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-mini-symbol-overview.js" async>
        {
          "symbol": "BITSTAMP:BTCUSD",
          "width": "100%",
          "height": "100%",
          "locale": "en",
          "dateRange": "1M",
          "colorTheme": "dark",
          "isTransparent": true,
          "autosize": false,
          "largeChartUrl": ""
        }
        </script>
      </div>

      <div class="tradingview-widget-container" style="height:220px;">
        <div class="tradingview-widget-container__widget" style="height:100%;"></div>
        <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-mini-symbol-overview.js" async>
        {
          "symbol": "TVC:GOLD",
          "width": "100%",
          "height": "100%",
          "locale": "en",
          "dateRange": "1M",
          "colorTheme": "dark",
          "isTransparent": true,
          "autosize": false,
          "largeChartUrl": ""
        }
        </script>
      </div>

    </div>
  </div>
</section>

<!-- =============================================
     ADVANCED CHART — loads reliably on first visit
     ============================================= -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <span class="section-label">Chart</span>
      <h2 class="section-title">Live Chart</h2>
      <p class="section-desc">Professional-grade charting with technical indicators, drawing tools, and multi-timeframe analysis.</p>
    </div>

    <div class="tv-widget-wrap" style="height: 600px;">
      <div class="tradingview-widget-container" style="height:100%;">
        <div id="tradingview_advanced_chart" style="height:100%;"></div>
        <!-- Load tv.js synchronously so TradingView is defined before init runs -->
        <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
        <script type="text/javascript">
        (function initChart() {
          if (typeof TradingView === 'undefined') {
            setTimeout(initChart, 100);
            return;
          }
          new TradingView.widget({
            "autosize":           true,
            "symbol":             "NASDAQ:AAPL",
            "interval":           "D",
            "timezone":           "Etc/UTC",
            "theme":              "dark",
            "style":              "1",
            "locale":             "en",
            "toolbar_bg":         "#161b22",
            "enable_publishing":  false,
            "allow_symbol_change": true,
            "container_id":       "tradingview_advanced_chart",
            "hide_side_toolbar":  false,
            "withdateranges":     true,
            "save_image":         false,
            "backgroundColor":    "#0d1117",
            "gridColor":          "rgba(48,54,61,0.5)"
          });
        })();
        </script>
      </div>
    </div>
  </div>
</section>

<!-- =============================================
     FOREX HEATMAP — currency strength
     ============================================= -->
<section class="section" style="padding-top: 0;">
  <div class="container">
    <div class="section-header">
      <span class="section-label">Forex</span>
      <h2 class="section-title">Currency Heatmap</h2>
      <p class="section-desc">See which currencies are strongest and weakest against each other — updated in real time.</p>
    </div>

    <div class="tv-widget-wrap" style="height: 500px;">
      <div class="tradingview-widget-container" style="height:100%;">
        <div class="tradingview-widget-container__widget" style="height:100%;"></div>
        <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-forex-heat-map.js" async>
        {
          "width": "100%",
          "height": "100%",
          "currencies": [
            "EUR", "USD", "JPY", "GBP", "CHF", "AUD", "CAD", "NZD"
          ],
          "isTransparent": true,
          "colorTheme": "dark",
          "locale": "en",
          "backgroundColor": "#0d1117"
        }
        </script>
      </div>
    </div>
  </div>
</section>

<!-- =============================================
     STOCK HEATMAP
     ============================================= -->
<section class="section" style="padding-top: 0;">
  <div class="container">
    <div class="section-header">
      <span class="section-label">Heatmap</span>
      <h2 class="section-title">Market Heatmap</h2>
      <p class="section-desc">A macro view of market performance — see which sectors and stocks are moving right now.</p>
    </div>

    <div class="tv-widget-wrap" style="height: 500px;">
      <div class="tradingview-widget-container" style="height:100%;">
        <div class="tradingview-widget-container__widget" style="height:100%;"><div class="tv-loading-skeleton"></div></div>
        <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-stock-heatmap.js" async>
        {
          "exchanges": [],
          "dataSource": "SPX500",
          "grouping": "sector",
          "blockSize": "market_cap_basic",
          "blockColor": "change",
          "locale": "en",
          "symbolUrl": "",
          "colorTheme": "dark",
          "hasTopBar": false,
          "isDataSetEnabled": false,
          "isZoomEnabled": true,
          "hasSymbolTooltip": true,
          "isMonoSize": false,
          "isTransparent": true,
          "width": "100%",
          "height": "100%"
        }
        </script>
      </div>
    </div>
  </div>
</section>

<!-- =============================================
     HOTLISTS — top gainers, losers, most active
     ============================================= -->
<section class="section" style="padding-top: 0;">
  <div class="container">
    <div class="section-header">
      <span class="section-label">Movers</span>
      <h2 class="section-title">Top Movers</h2>
      <p class="section-desc">Today&#39;s biggest gainers, losers, and most active stocks — updated in real time.</p>
    </div>

    <div class="tv-widget-wrap" style="height: 500px;">
      <div class="tradingview-widget-container" style="height:100%;">
        <div class="tradingview-widget-container__widget" style="height:100%;"></div>
        <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-hotlists.js" async>
        {
          "colorTheme": "dark",
          "dateRange": "1D",
          "exchange": "US",
          "showChart": true,
          "locale": "en",
          "isTransparent": true,
          "showSymbolLogo": true,
          "showFloatingTooltip": false,
          "width": "100%",
          "height": "100%",
          "plotLineColorGrowing": "rgba(41,98,255,1)",
          "plotLineColorFalling": "rgba(239,83,80,1)",
          "gridLineColor": "rgba(48,54,61,1)",
          "scaleFontColor": "rgba(139,148,158,1)",
          "belowLineFillColorGrowing": "rgba(41,98,255,0.12)",
          "belowLineFillColorFalling": "rgba(239,83,80,0.12)",
          "belowLineFillColorGrowingBottom": "rgba(41,98,255,0)",
          "belowLineFillColorFallingBottom": "rgba(239,83,80,0)",
          "symbolActiveColor": "rgba(41,98,255,0.12)"
        }
        </script>
      </div>
    </div>
  </div>
</section>

<!-- =============================================
     SERVICES
     ============================================= -->
<section class="section" style="background: var(--bg-surface); border-top: 1px solid var(--border-muted); border-bottom: 1px solid var(--border-muted);">
  <div class="container">
    <div class="section-header">
      <span class="section-label">What We Offer</span>
      <h2 class="section-title">Our Services</h2>
      <p class="section-desc">Everything you need to plan, analyze, and execute your investment strategy.</p>
    </div>

    <div class="services-grid">
      <div class="service-card">
        <div class="service-icon">📈</div>
        <h3>Investment Planning</h3>
        <p>Customized portfolios tailored to your risk tolerance, time horizon, and financial goals. Diversified across asset classes for optimal balance.</p>
      </div>
      <div class="service-card">
        <div class="service-icon">🔬</div>
        <h3>Trading Simulations</h3>
        <p>Analyze hypothetical scenarios with our interactive analytics engine. Backtest strategies, measure Sharpe ratios, and stress-test your approach.</p>
      </div>
      <div class="service-card">
        <div class="service-icon">🏦</div>
        <h3>Wealth Management</h3>
        <p>Comprehensive advisory including tax planning, estate strategy, and retirement roadmaps to secure your long-term financial well-being.</p>
      </div>
      <div class="service-card">
        <div class="service-icon">⚡</div>
        <h3>Real-Time Analytics</h3>
        <p>Live market data, advanced charting tools, and institutional-grade technical analysis to keep you ahead of every market move.</p>
      </div>
      <div class="service-card">
        <div class="service-icon">🌐</div>
        <h3>Global Markets</h3>
        <p>Access to stocks, crypto, forex, commodities, and indices across 50+ global exchanges — all from a single unified dashboard.</p>
      </div>
      <div class="service-card">
        <div class="service-icon">🔒</div>
        <h3>Secure Platform</h3>
        <p>Bank-grade security, encrypted data, and verified authentication flows to keep your account and analytics completely private.</p>
      </div>
    </div>
  </div>
</section>

<!-- =============================================
     CTA BANNER
     ============================================= -->
<div class="container">
  <div class="cta-banner">
    <h2>Start investing smarter today.</h2>
    <p>Join thousands of investors using WizInvestments to track markets, analyze strategies, and grow their wealth.</p>
    <div class="btn-group" style="justify-content: center;">
      <?php if ( ! is_user_logged_in() ) : ?>
        <a href="<?php echo esc_url( wiz_get_page_url_by_slug('register') ); ?>" class="btn btn-gold btn-lg">
          Create Free Account
        </a>
        <a href="<?php echo esc_url( wiz_get_page_url_by_slug('analytics-dashboard') ); ?>" class="btn btn-secondary btn-lg">
          View Analytics
        </a>
      <?php else : ?>
        <a href="<?php echo esc_url( wiz_get_page_url_by_slug('analytics-dashboard') ); ?>" class="btn btn-gold btn-lg">
          Go to Analytics
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php get_footer(); ?>
