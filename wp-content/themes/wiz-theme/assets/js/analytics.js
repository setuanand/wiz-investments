/* ============================================================
   WizInvestments Analytics Dashboard
   Strategy Simulator + Portfolio Tracker + Portfolio Optimizer
   ============================================================ */
(function () {
  'use strict';

  // ─── CHART DEFAULTS ───────────────────────────────────────
  const DARK = {
    bg:       '#0d1117',
    surface:  '#161b22',
    border:   '#30363d',
    text:     '#e6edf3',
    muted:    '#8b949e',
    blue:     '#2962ff',
    green:    '#26a69a',
    red:      '#ef5350',
    gold:     '#f0b90b',
  };

  Chart.defaults.color = DARK.muted;
  Chart.defaults.borderColor = DARK.border;
  Chart.defaults.font.family = 'Inter, sans-serif';

  function chartDefaults(extra = {}) {
    return Object.assign({
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { labels: { color: DARK.text, boxWidth: 12 } }, tooltip: { mode: 'index', intersect: false } },
      scales: {
        x: { grid: { color: DARK.border }, ticks: { color: DARK.muted, maxTicksLimit: 8 } },
        y: { grid: { color: DARK.border }, ticks: { color: DARK.muted } }
      }
    }, extra);
  }

  // ─── HELPERS ──────────────────────────────────────────────
  function el(id) { return document.getElementById(id); }
  function fmt(n, dec = 2) { return Number(n).toLocaleString('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec }); }
  function fmtPct(n) { return (n >= 0 ? '+' : '') + fmt(n) + '%'; }
  function fmtDollar(n) { return (n >= 0 ? '+$' : '-$') + fmt(Math.abs(n)); }

  function sma(arr, w) {
    const res = new Array(arr.length).fill(null);
    let sum = 0;
    for (let i = 0; i < arr.length; i++) {
      sum += arr[i];
      if (i >= w) sum -= arr[i - w];
      if (i >= w - 1) res[i] = sum / w;
    }
    return res;
  }

  function rsi(arr, period = 14) {
    const res = new Array(arr.length).fill(null);
    let gains = 0, losses = 0;
    for (let i = 1; i <= period; i++) {
      const d = arr[i] - arr[i - 1];
      if (d > 0) gains += d; else losses -= d;
    }
    let avgGain = gains / period, avgLoss = losses / period;
    for (let i = period; i < arr.length; i++) {
      if (i > period) {
        const d = arr[i] - arr[i - 1];
        avgGain = (avgGain * (period - 1) + Math.max(0, d)) / period;
        avgLoss = (avgLoss * (period - 1) + Math.max(0, -d)) / period;
      }
      const rs = avgLoss === 0 ? 100 : avgGain / avgLoss;
      res[i] = 100 - 100 / (1 + rs);
    }
    return res;
  }

  function gbm(S0, days, mu, sigma) {
    const dt = 1 / 252;
    const prices = [S0];
    for (let i = 1; i < days; i++) {
      const z = Math.sqrt(-2 * Math.log(Math.random())) * Math.cos(2 * Math.PI * Math.random()); // Box-Muller
      prices.push(prices[i - 1] * Math.exp((mu - 0.5 * sigma * sigma) * dt + sigma * Math.sqrt(dt) * z));
    }
    return prices;
  }

  function calcMetrics(equity, trades) {
    const returns = [];
    for (let i = 1; i < equity.length; i++) returns.push(equity[i] / equity[i - 1] - 1);
    const totalReturn = (equity[equity.length - 1] / equity[0] - 1) * 100;
    let peak = equity[0], maxDd = 0;
    for (const v of equity) { if (v > peak) peak = v; const dd = (peak - v) / peak; if (dd > maxDd) maxDd = dd; }
    const avg = returns.reduce((s, x) => s + x, 0) / Math.max(1, returns.length);
    const sd = Math.sqrt(returns.reduce((s, x) => s + (x - avg) ** 2, 0) / Math.max(1, returns.length - 1)) || 0;
    const sharpe = sd ? avg / sd * Math.sqrt(252) : 0;
    const cagr = (Math.pow(equity[equity.length - 1] / equity[0], 252 / Math.max(1, equity.length - 1)) - 1) * 100;
    const wins = trades.filter(t => t.pnl > 0);
    const losses = trades.filter(t => t.pnl <= 0);
    const winRate = trades.length ? (wins.length / trades.length * 100) : 0;
    const avgWin = wins.length ? wins.reduce((s, t) => s + t.pnl, 0) / wins.length : 0;
    const avgLoss = losses.length ? losses.reduce((s, t) => s + t.pnl, 0) / losses.length : 0;
    return { totalReturn, cagr, sharpe, maxDd: maxDd * 100, winRate, avgWin, avgLoss, trades: trades.length };
  }

  // ─── STRATEGIES ────────────────────────────────────────────
  function runSMA(prices, capital) {
    const short = sma(prices, 10), long = sma(prices, 50);
    const trades = [], signals = new Array(prices.length).fill(0);
    let inPos = false, entryIdx = 0, entryPrice = 0;
    const equity = [capital];
    let currentCapital = capital;

    for (let i = 0; i < prices.length; i++) {
      if (short[i] !== null && long[i] !== null) {
        const crossUp = short[i] > long[i];
        if (!inPos && crossUp) {
          inPos = true; entryIdx = i; entryPrice = prices[i];
          signals[i] = 1; // buy signal
        } else if (inPos && !crossUp) {
          const pnl = (prices[i] - entryPrice) / entryPrice * currentCapital;
          currentCapital += pnl;
          trades.push({ entryDay: entryIdx + 1, exitDay: i + 1, entryPrice, exitPrice: prices[i], pnl });
          signals[i] = -1; // sell signal
          inPos = false;
        }
      }
      equity.push(inPos ? capital * (prices[i] / prices[entryIdx]) : currentCapital);
    }
    if (inPos) {
      const pnl = (prices[prices.length - 1] - entryPrice) / entryPrice * currentCapital;
      trades.push({ entryDay: entryIdx + 1, exitDay: prices.length, entryPrice, exitPrice: prices[prices.length - 1], pnl });
    }
    return { equity, trades, signals };
  }

  function runRSI(prices, capital) {
    const rsiVals = rsi(prices, 14);
    const trades = [], signals = new Array(prices.length).fill(0);
    let inPos = false, entryIdx = 0, entryPrice = 0;
    const equity = [capital];
    let currentCapital = capital;

    for (let i = 0; i < prices.length; i++) {
      if (rsiVals[i] !== null) {
        if (!inPos && rsiVals[i] < 30) {
          inPos = true; entryIdx = i; entryPrice = prices[i];
          signals[i] = 1;
        } else if (inPos && rsiVals[i] > 70) {
          const pnl = (prices[i] - entryPrice) / entryPrice * currentCapital;
          currentCapital += pnl;
          trades.push({ entryDay: entryIdx + 1, exitDay: i + 1, entryPrice, exitPrice: prices[i], pnl });
          signals[i] = -1;
          inPos = false;
        }
      }
      equity.push(inPos ? capital * (prices[i] / prices[entryIdx]) : currentCapital);
    }
    if (inPos) {
      const pnl = (prices[prices.length - 1] - entryPrice) / entryPrice * currentCapital;
      trades.push({ entryDay: entryIdx + 1, exitDay: prices.length, entryPrice, exitPrice: prices[prices.length - 1], pnl });
    }
    return { equity, trades, signals };
  }

  function runBuyHold(prices, capital) {
    const equity = prices.map(p => capital * (p / prices[0]));
    const trades = [{ entryDay: 1, exitDay: prices.length, entryPrice: prices[0], exitPrice: prices[prices.length - 1], pnl: (prices[prices.length - 1] / prices[0] - 1) * capital }];
    const signals = new Array(prices.length).fill(0);
    signals[0] = 1;
    return { equity, trades, signals };
  }

  // ─── CHARTS ────────────────────────────────────────────────
  let priceChart = null, equityChart = null, portfolioChart = null, allocationChart = null, frontierChart = null;

  function buildPriceChart(prices, signals) {
    const labels = prices.map((_, i) => `Day ${i + 1}`);
    const buyPoints = prices.map((p, i) => signals[i] === 1 ? p : null);
    const sellPoints = prices.map((p, i) => signals[i] === -1 ? p : null);

    const datasets = [
      { label: 'Price', data: prices.map(p => +p.toFixed(2)), borderColor: DARK.blue, backgroundColor: 'rgba(41,98,255,0.08)', borderWidth: 1.5, fill: true, tension: 0.2, pointRadius: 0 },
      { label: 'Buy', data: buyPoints, borderColor: DARK.green, backgroundColor: DARK.green, pointRadius: 6, pointStyle: 'triangle', showLine: false },
      { label: 'Sell', data: sellPoints, borderColor: DARK.red, backgroundColor: DARK.red, pointRadius: 6, pointStyle: 'triangle', rotation: 180, showLine: false },
    ];

    if (priceChart) { priceChart.data.labels = labels; priceChart.data.datasets = datasets; priceChart.update(); return; }
    const ctx = el('priceChart');
    if (!ctx) return;
    priceChart = new Chart(ctx.getContext('2d'), { type: 'line', data: { labels, datasets }, options: chartDefaults({ plugins: { legend: { labels: { color: DARK.text, filter: i => i.text !== 'Buy' && i.text !== 'Sell' } } } }) });
  }

  function buildEquityChart(equity, benchmark) {
    const labels = equity.map((_, i) => `Day ${i + 1}`);
    const datasets = [
      { label: 'Strategy', data: equity.map(v => +v.toFixed(2)), borderColor: DARK.green, backgroundColor: 'rgba(38,166,154,0.1)', borderWidth: 2, fill: true, tension: 0.2, pointRadius: 0 },
      { label: 'Buy & Hold', data: benchmark.map(v => +v.toFixed(2)), borderColor: DARK.gold, backgroundColor: 'rgba(240,185,11,0.05)', borderWidth: 1.5, borderDash: [5, 3], fill: false, tension: 0.2, pointRadius: 0 },
    ];
    if (equityChart) { equityChart.data.labels = labels; equityChart.data.datasets = datasets; equityChart.update(); return; }
    const ctx = el('equityChart');
    if (!ctx) return;
    equityChart = new Chart(ctx.getContext('2d'), { type: 'line', data: { labels, datasets }, options: chartDefaults() });
  }

  function updateMetrics(m) {
    function setMetric(id, val, good) {
      const e = el(id); if (!e) return;
      e.textContent = val;
      e.className = 'metric-value ' + (good === true ? 'gain' : good === false ? 'loss' : '');
    }
    setMetric('m-return', fmtPct(m.totalReturn), m.totalReturn >= 0);
    setMetric('m-cagr', fmtPct(m.cagr), m.cagr >= 0);
    setMetric('m-sharpe', fmt(m.sharpe), m.sharpe >= 1 ? true : m.sharpe < 0 ? false : null);
    setMetric('m-drawdown', '-' + fmt(m.maxDd) + '%', false);
    setMetric('m-winrate', fmt(m.winRate) + '%', m.winRate >= 50);
    setMetric('m-avgwin', '$' + fmt(m.avgWin), true);
    setMetric('m-avgloss', '$' + fmt(m.avgLoss), false);
    setMetric('m-trades', m.trades, null);
  }

  function renderTradeTable(trades) {
    const wrap = el('trade-list'); if (!wrap) return;
    if (!trades.length) { wrap.innerHTML = '<p style="color:var(--text-muted);text-align:center;padding:2rem 0">No trades generated</p>'; return; }
    let h = `<table class="analytics-table"><thead><tr><th>Entry Day</th><th>Exit Day</th><th>Entry $</th><th>Exit $</th><th>P&L</th><th>Return</th></tr></thead><tbody>`;
    for (const t of trades) {
      const ret = ((t.exitPrice - t.entryPrice) / t.entryPrice * 100).toFixed(2);
      const cls = t.pnl >= 0 ? 'gain' : 'loss';
      h += `<tr><td>${t.entryDay}</td><td>${t.exitDay}</td><td>$${fmt(t.entryPrice)}</td><td>$${fmt(t.exitPrice)}</td><td class="${cls}">${fmtDollar(t.pnl)}</td><td class="${cls}">${fmtPct(+ret)}</td></tr>`;
    }
    h += '</tbody></table>';
    wrap.innerHTML = h;
  }

  function csvDownload(filename, rows) {
    const csv = rows.map(r => r.map(c => '"' + String(c).replace(/"/g, '""') + '"').join(',')).join('\n');
    const a = Object.assign(document.createElement('a'), { href: URL.createObjectURL(new Blob([csv], { type: 'text/csv' })), download: filename });
    document.body.appendChild(a); a.click(); a.remove();
  }

  // ─── SIMULATION RUNNER ─────────────────────────────────────
  let lastSimData = null;
  let animFrame = null;

  function runSimulation(prices) {
    const capital = Number(el('initial-capital').value) || 10000;
    const scenario = el('scenario-select').value;

    let sim;
    if (scenario === 'sma') sim = runSMA(prices, capital);
    else if (scenario === 'rsi') sim = runRSI(prices, capital);
    else sim = runBuyHold(prices, capital);

    const benchmark = runBuyHold(prices, capital).equity;
    const metrics = calcMetrics(sim.equity, sim.trades);

    buildPriceChart(prices, sim.signals);
    buildEquityChart(sim.equity, benchmark);
    updateMetrics(metrics);
    renderTradeTable(sim.trades);

    lastSimData = { prices, sim, benchmark };

    el('download-csv').onclick = () => {
      const rows = [['Day', 'Price', 'Strategy Equity', 'BuyHold Equity', 'Signal']];
      prices.forEach((p, i) => rows.push([i + 1, p.toFixed(2), sim.equity[i + 1]?.toFixed(2) ?? '', benchmark[i + 1]?.toFixed(2) ?? '', sim.signals[i] === 1 ? 'BUY' : sim.signals[i] === -1 ? 'SELL' : '']));
      csvDownload('wiz-simulation.csv', rows);
    };
  }

  function animate(prices) {
    if (animFrame) cancelAnimationFrame(animFrame);
    const capital = Number(el('initial-capital').value) || 10000;
    const scenario = el('scenario-select').value;
    const speed = Number(el('play-speed').value) || 100;

    // Pre-compute full simulation
    let sim;
    if (scenario === 'sma') sim = runSMA(prices, capital);
    else if (scenario === 'rsi') sim = runRSI(prices, capital);
    else sim = runBuyHold(prices, capital);
    const benchmark = runBuyHold(prices, capital).equity;

    let day = 1;
    const btn = el('play-sim');
    btn.textContent = '⏹ Stop';
    btn.onclick = () => { cancelAnimationFrame(animFrame); animFrame = null; btn.textContent = '⏵ Animate'; btn.onclick = () => animate(prices); };

    function step() {
      const slicePrices = prices.slice(0, day);
      const sliceSignals = sim.signals.slice(0, day);
      const sliceEquity = sim.equity.slice(0, day + 1);
      const sliceBenchmark = benchmark.slice(0, day + 1);

      buildPriceChart(slicePrices, sliceSignals);
      buildEquityChart(sliceEquity, sliceBenchmark);

      if (day < prices.length) {
        day++;
        animFrame = setTimeout(() => requestAnimationFrame(step), speed);
      } else {
        updateMetrics(calcMetrics(sim.equity, sim.trades));
        renderTradeTable(sim.trades);
        btn.textContent = '⏵ Animate';
        btn.onclick = () => animate(prices);
      }
    }
    requestAnimationFrame(step);
  }

  async function fetchRealData(symbol = 'SPY', days = 252) {
    // Fetch via server-side proxy — no CORS issues
    const notice = el('real-data-notice');
    notice.textContent = `⏳ Fetching ${symbol} data from server...`;
    notice.style.display = 'block';
    notice.style.color = '';
    try {
      const nonces = window.wizNonces || {};
      const body = new FormData();
      body.append('action', 'wiz_fetch_historical');
      body.append('nonce', nonces.data || '');
      body.append('symbol', symbol);
      body.append('days', days);
      const resp = await fetch(nonces.ajaxUrl || '/wp-admin/admin-ajax.php', { method: 'POST', body });
      const json = await resp.json();
      if (!json.success) throw new Error(json.data?.message || 'Server error');
      const closes = json.data.prices.map(p => p.close);
      notice.textContent = `✅ Loaded ${closes.length} days of real ${symbol} data`;
      notice.style.color = 'var(--gain)';
      return closes;
    } catch (e) {
      notice.textContent = `❌ ${e.message}. Using simulation instead.`;
      notice.style.color = 'var(--loss)';
      return null;
    }
  }

  async function fetchLivePriceServer(ticker) {
    const nonces = window.wizNonces || {};
    const body = new FormData();
    body.append('action', 'wiz_fetch_live_price');
    body.append('nonce', nonces.data || '');
    body.append('symbol', ticker);
    const resp = await fetch(nonces.ajaxUrl || '/wp-admin/admin-ajax.php', { method: 'POST', body });
    const json = await resp.json();
    if (!json.success) throw new Error(json.data?.message || 'Could not fetch price');
    return json.data;
  }

  async function loadHoldingsFromServer() {
    const nonces = window.wizNonces || {};
    const body = new FormData();
    body.append('action', 'wiz_get_holdings');
    body.append('nonce', nonces.portfolio || '');
    const resp = await fetch(nonces.ajaxUrl || '/wp-admin/admin-ajax.php', { method: 'POST', body });
    const json = await resp.json();
    if (json.success) {
      const serverHoldings = json.data.holdings || [];

      // MIGRATION: if server has no holdings but localStorage does, migrate them up
      if (serverHoldings.length === 0) {
        const localRaw = localStorage.getItem('wiz_holdings');
        if (localRaw) {
          try {
            const localHoldings = JSON.parse(localRaw);
            if (localHoldings && localHoldings.length > 0) {
              console.log('Migrating ' + localHoldings.length + ' holdings from localStorage to server...');
              for (const h of localHoldings) {
                // Normalise field names (localStorage used camelCase, server uses snake_case)
                await addHoldingToServer({
                  name:          h.name || h.Name || '',
                  ticker:        h.ticker || h.Ticker || '',
                  units:         h.units || h.Units || 0,
                  buy_price:     h.buyPrice || h.buy_price || h.BuyPrice || 0,
                  current_price: h.currentPrice || h.current_price || h.CurrentPrice || 0,
                  date:          h.date || h.Date || new Date().toISOString().split('T')[0],
                });
              }
              localStorage.removeItem('wiz_holdings'); // clear after migration
              console.log('Migration complete.');
              return; // addHoldingToServer already calls renderHoldingsTable
            }
          } catch(e) {
            console.warn('localStorage migration failed:', e);
          }
        }
      }

      holdings = serverHoldings;
      renderHoldingsTable();
      if (json.data.summary) updatePortfolioSummaryFromServer(json.data.summary);
      if (serverHoldings.length > 0) loadSnapshotsFromServer('1M');
    }
  }

  async function addHoldingToServer(holding) {
    const nonces = window.wizNonces || {};
    const body = new FormData();
    body.append('action', 'wiz_add_holding');
    body.append('nonce', nonces.portfolio || '');
    Object.entries(holding).forEach(([k, v]) => body.append(k, v));
    const resp = await fetch(nonces.ajaxUrl || '/wp-admin/admin-ajax.php', { method: 'POST', body });
    const json = await resp.json();
    if (json.success) {
      holdings = json.data.holdings || [];
      renderHoldingsTable();
    } else {
      alert(json.data?.message || 'Error saving holding.');
    }
  }

  async function deleteHoldingFromServer(index) {
    const nonces = window.wizNonces || {};
    const body = new FormData();
    body.append('action', 'wiz_delete_holding');
    body.append('nonce', nonces.portfolio || '');
    body.append('index', index);
    const resp = await fetch(nonces.ajaxUrl || '/wp-admin/admin-ajax.php', { method: 'POST', body });
    const json = await resp.json();
    if (json.success) {
      holdings = json.data.holdings || [];
      renderHoldingsTable();
    }
  }

  async function refreshAllPrices() {
    const nonces = window.wizNonces || {};
    const btn = el('refresh-prices-btn');
    if (btn) { btn.disabled = true; btn.textContent = '⏳ Refreshing...'; }
    const body = new FormData();
    body.append('action', 'wiz_refresh_prices');
    body.append('nonce', nonces.portfolio || '');
    try {
      const resp = await fetch(nonces.ajaxUrl || '/wp-admin/admin-ajax.php', { method: 'POST', body });
      const json = await resp.json();
      if (json.success) {
        holdings = json.data.holdings || [];
        renderHoldingsTable();
        alert(json.data.message);
      } else {
        alert(json.data?.message || 'Error refreshing prices.');
      }
    } finally {
      if (btn) { btn.disabled = false; btn.textContent = '🔄 Refresh All Prices'; }
    }
  }

  async function loadSnapshotsFromServer(period = 'ALL') {
    const nonces = window.wizNonces || {};
    const body = new FormData();
    body.append('action', 'wiz_get_snapshots');
    body.append('nonce', nonces.portfolio || '');
    body.append('period', period);
    const resp = await fetch(nonces.ajaxUrl || '/wp-admin/admin-ajax.php', { method: 'POST', body });
    const json = await resp.json();
    if (json.success && json.data.snapshots.length > 0) {
      buildPortfolioChartFromSnapshots(json.data.snapshots);
    }
  }

  // ─── PORTFOLIO TRACKER ─────────────────────────────────────
  let holdings = JSON.parse(localStorage.getItem('wiz_holdings') || '[]');
  let portfolioChartInstance = null;
  let allocationChartInstance = null;
  let editingIndex = -1;

  function saveHoldings() { localStorage.setItem('wiz_holdings', JSON.stringify(holdings)); } // localStorage as cache

  function renderHoldingsTable() {
    const wrap = el('holdings-table-wrap'); if (!wrap) return;
    if (!holdings.length) {
      wrap.innerHTML = '<p style="color:var(--text-muted);text-align:center;padding:2rem 0">No holdings yet — add your first position above</p>';
      updatePortfolioSummary();
      return;
    }
    let totalCost = 0, totalVal = 0;
    let h = `<table class="analytics-table"><thead><tr><th>Asset</th><th>Ticker</th><th>Units</th><th>Buy $</th><th>Current $</th><th>Cost</th><th>Value</th><th>P&L</th><th>Return</th><th>Weight</th><th></th></tr></thead><tbody>`;
    const values = holdings.map(hh => hh.units * (hh.current_price || hh.currentPrice || 0));
    const costs = holdings.map(hh => hh.units * (hh.buy_price || hh.buyPrice || 0));
    const totalV = values.reduce((s, v) => s + v, 0);
    holdings.forEach((hh, i) => {
      const cost = costs[i], val = values[i], pnl = val - cost, ret = (pnl / cost * 100);
      totalCost += cost; totalVal += val;
      const cls = pnl >= 0 ? 'gain' : 'loss';
      const weight = totalV ? (val / totalV * 100).toFixed(1) : 0;
      const buyPrice = hh.buy_price || hh.buyPrice || 0;
      const currentPrice = hh.current_price || hh.currentPrice || 0;
      h += `<tr>
        <td><strong>${hh.name}</strong></td>
        <td>${hh.ticker || '—'}</td>
        <td>${hh.units}</td>
        <td>$${fmt(buyPrice)}</td>
        <td>$${fmt(currentPrice)}</td>
        <td>$${fmt(cost)}</td>
        <td>$${fmt(val)}</td>
        <td class="${cls}">${fmtDollar(pnl)}</td>
        <td class="${cls}">${fmtPct(ret)}</td>
        <td>${weight}%</td>
        <td><button class="btn btn-sm" style="padding:2px 8px;font-size:0.75rem;color:var(--loss);border:1px solid var(--loss);background:none;cursor:pointer;" onclick="window.wizDeleteHolding(${i})">✕</button></td>
      </tr>`;
    });
    h += '</tbody></table>';
    wrap.innerHTML = h;
    updatePortfolioSummary(totalCost, totalVal, values);
    updateAllocationChart();
    updatePortfolioChart();
  }

  function updatePortfolioSummary(totalCost = 0, totalVal = 0, values = []) {
    const pnl = totalVal - totalCost;
    const pct = totalCost ? pnl / totalCost * 100 : 0;

    const setEl = (id, text, cls) => { const e = el(id); if (e) { e.textContent = text; if (cls) e.className = 'ps-value ' + cls; } };
    setEl('pt-total-value', '$' + fmt(totalVal));
    setEl('pt-total-cost', '$' + fmt(totalCost));
    setEl('pt-total-pnl', fmtDollar(pnl), pnl >= 0 ? 'gain' : 'loss');
    setEl('pt-total-pct', fmtPct(pct), pct >= 0 ? 'gain' : 'loss');

    if (holdings.length) {
      const rets = holdings.map(h => (h.currentPrice - h.buyPrice) / h.buyPrice * 100);
      const bestIdx = rets.indexOf(Math.max(...rets));
      const worstIdx = rets.indexOf(Math.min(...rets));
      setEl('pt-best', holdings[bestIdx].name + ' (' + fmtPct(rets[bestIdx]) + ')', 'gain');
      setEl('pt-worst', holdings[worstIdx].name + ' (' + fmtPct(rets[worstIdx]) + ')', 'loss');
    } else {
      setEl('pt-best', '—'); setEl('pt-worst', '—');
    }
  }

  function updateAllocationChart() {
    const ctx = el('allocationChart'); if (!ctx) return;
    const labels = holdings.map(h => h.name);
    const data = holdings.map(h => +((h.units * (h.current_price || h.currentPrice || 0))).toFixed(2));
    const colors = ['#2962ff','#26a69a','#f0b90b','#ef5350','#ab47bc','#ff7043','#42a5f5','#66bb6a'];

    if (allocationChartInstance) {
      allocationChartInstance.data.labels = labels;
      allocationChartInstance.data.datasets[0].data = data;
      allocationChartInstance.data.datasets[0].backgroundColor = colors.slice(0, labels.length);
      allocationChartInstance.update();
      return;
    }
    allocationChartInstance = new Chart(ctx.getContext('2d'), {
      type: 'doughnut',
      data: { labels, datasets: [{ data, backgroundColor: colors.slice(0, labels.length), borderColor: DARK.bg, borderWidth: 3 }] },
      options: { responsive: true, plugins: { legend: { position: 'right', labels: { color: DARK.text, padding: 16 } } } }
    });
  }


  function buildPortfolioChartFromSnapshots(snapshots) {
    if (!snapshots || !snapshots.length) return;
    const labels = snapshots.map(s => s.date);
    const values = snapshots.map(s => s.value);
    const isUp = values[values.length - 1] >= values[0];
    const color = isUp ? DARK.green : DARK.red;
    const ctx = el('portfolioChart'); if (!ctx) return;
    if (portfolioChartInstance) {
      portfolioChartInstance.data.labels = labels;
      portfolioChartInstance.data.datasets[0].data = values;
      portfolioChartInstance.data.datasets[0].borderColor = color;
      portfolioChartInstance.data.datasets[0].backgroundColor = isUp ? 'rgba(38,166,154,0.1)' : 'rgba(239,83,80,0.1)';
      portfolioChartInstance.update();
      return;
    }
    portfolioChartInstance = new Chart(ctx.getContext('2d'), {
      type: 'line',
      data: { labels, datasets: [{ label: 'Portfolio Value', data: values, borderColor: color, backgroundColor: isUp ? 'rgba(38,166,154,0.1)' : 'rgba(239,83,80,0.1)', borderWidth: 2, fill: true, tension: 0.3, pointRadius: 0 }] },
      options: chartDefaults({ scales: { y: { grid: { color: DARK.border }, ticks: { color: DARK.muted, callback: v => '$' + fmt(v, 0) } }, x: { grid: { color: DARK.border }, ticks: { color: DARK.muted, maxTicksLimit: 6 } } } })
    });
  }

  function updatePortfolioSummaryFromServer(summary) {
    const setEl = (id, text, cls) => { const e = el(id); if (e) { e.textContent = text; if (cls !== undefined) e.className = 'ps-value ' + cls; } };
    setEl('pt-total-value', '$' + fmt(summary.total_value));
    setEl('pt-total-cost',  '$' + fmt(summary.total_cost));
    setEl('pt-total-pnl',   fmtDollar(summary.total_pnl), summary.total_pnl >= 0 ? 'gain' : 'loss');
    setEl('pt-total-pct',   fmtPct(summary.total_pct),    summary.total_pct >= 0 ? 'gain' : 'loss');
    if (summary.best)  setEl('pt-best',  summary.best.name  + ' (' + fmtPct(summary.best.pct)  + ')', 'gain');
    if (summary.worst) setEl('pt-worst', summary.worst.name + ' (' + fmtPct(summary.worst.pct) + ')', 'loss');
  }

  function updatePortfolioChart() {
    // Simulate portfolio value over time based on purchase dates and current prices
    const ctx = el('portfolioChart'); if (!ctx || !holdings.length) return;
    const today = new Date();
    const period = document.querySelector('.period-btn.active')?.dataset.period || '3M';
    const days = period === '1M' ? 30 : period === '3M' ? 90 : period === '6M' ? 180 : period === 'YTD' ? Math.floor((today - new Date(today.getFullYear(), 0, 1)) / 86400000) : period === '1Y' ? 365 : 730;

    const labels = [], values = [];
    for (let i = days; i >= 0; i--) {
      const d = new Date(today); d.setDate(d.getDate() - i);
      labels.push(d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
      // Simulate value: holdings that were purchased before this date contribute
      let val = 0;
      holdings.forEach(h => {
        const purchaseDate = h.date ? new Date(h.date) : new Date(today.getTime() - days * 86400000);
        if (d >= purchaseDate) {
          // Linear interpolation between buy price and current price
          const totalDays = Math.max(1, (today - purchaseDate) / 86400000);
          const elapsed = Math.max(0, (d - purchaseDate) / 86400000);
          const progress = Math.min(1, elapsed / totalDays);
          const bp = h.buy_price || h.buyPrice || 0; const cp = h.current_price || h.currentPrice || 0; const interpolatedPrice = bp + (cp - bp) * progress;
          val += h.units * interpolatedPrice;
        }
      });
      values.push(+val.toFixed(2));
    }

    const isUp = values[values.length - 1] >= values[0];
    const color = isUp ? DARK.green : DARK.red;

    if (portfolioChartInstance) {
      portfolioChartInstance.data.labels = labels;
      portfolioChartInstance.data.datasets[0].data = values;
      portfolioChartInstance.data.datasets[0].borderColor = color;
      portfolioChartInstance.data.datasets[0].backgroundColor = isUp ? 'rgba(38,166,154,0.1)' : 'rgba(239,83,80,0.1)';
      portfolioChartInstance.update();
      return;
    }
    portfolioChartInstance = new Chart(ctx.getContext('2d'), {
      type: 'line',
      data: { labels, datasets: [{ label: 'Portfolio Value', data: values, borderColor: color, backgroundColor: isUp ? 'rgba(38,166,154,0.1)' : 'rgba(239,83,80,0.1)', borderWidth: 2, fill: true, tension: 0.3, pointRadius: 0 }] },
      options: chartDefaults({ scales: { y: { grid: { color: DARK.border }, ticks: { color: DARK.muted, callback: v => '$' + fmt(v, 0) } }, x: { grid: { color: DARK.border }, ticks: { color: DARK.muted, maxTicksLimit: 6 } } } })
    });
  }

  window.wizDeleteHolding = function (i) {
    deleteHoldingFromServer(i);
  };

  async function fetchLivePrice(ticker) {
    const status = el('fetch-price-status');
    if (!ticker) { status.textContent = 'Enter a ticker symbol first (e.g. AAPL, BTC-USD)'; return; }
    status.textContent = '⏳ Fetching live price for ' + ticker + '...';
    status.style.color = '';
    try {
      const data = await fetchLivePriceServer(ticker);
      el('h-current-price').value = data.price.toFixed(2);
      status.textContent = `✅ Live price: $${data.price.toFixed(2)} (${data.change_pct >= 0 ? '+' : ''}${data.change_pct}% today)`;
      status.style.color = 'var(--gain)';
    } catch(e) {
      status.textContent = `❌ ${e.message}. Enter price manually.`;
      status.style.color = 'var(--loss)';
    }
  }

  // ─── PORTFOLIO OPTIMIZER ───────────────────────────────────
  let optimizerAssets = [
    { name: 'Asset A', ret: 10, vol: 15, weight: 50 },
    { name: 'Asset B', ret: 7,  vol: 8,  weight: 50 },
  ];

  function renderOptimizerAssets() {
    const wrap = el('optimizer-assets'); if (!wrap) return;
    let h = `<div class="optimizer-assets-grid"><div class="opt-header">Asset</div><div class="opt-header">Exp. Return (%)<span class="tooltip-wrap" data-tip="Expected annual return percentage.">ⓘ</span></div><div class="opt-header">Volatility (%)<span class="tooltip-wrap" data-tip="Annual standard deviation of returns.">ⓘ</span></div><div class="opt-header">Current Weight (%)</div><div class="opt-header"></div>`;
    optimizerAssets.forEach((a, i) => {
      h += `<input class="form-control" placeholder="Asset name" value="${a.name}" onchange="window.wizOptUpdate(${i},'name',this.value)">
      <input class="form-control" type="number" value="${a.ret}" step="0.1" onchange="window.wizOptUpdate(${i},'ret',+this.value)">
      <input class="form-control" type="number" value="${a.vol}" step="0.1" onchange="window.wizOptUpdate(${i},'vol',+this.value)">
      <input class="form-control" type="number" value="${a.weight}" step="1" onchange="window.wizOptUpdate(${i},'weight',+this.value)">
      <button class="btn btn-sm" style="color:var(--loss);border:1px solid var(--loss);background:none;cursor:pointer;" onclick="window.wizOptRemove(${i})">✕</button>`;
    });
    h += '</div>';
    wrap.innerHTML = h;
    initTooltips();
  }

  window.wizOptUpdate = (i, key, val) => { optimizerAssets[i][key] = val; };
  window.wizOptRemove = (i) => { optimizerAssets.splice(i, 1); renderOptimizerAssets(); };

  function runOptimizer() {
    const n = optimizerAssets.length;
    if (n < 2) { alert('Add at least 2 assets to run the optimizer.'); return; }

    const rets = optimizerAssets.map(a => a.ret / 100);
    const vols = optimizerAssets.map(a => a.vol / 100);

    // Generate 500 random portfolios
    const portfolios = [];
    for (let p = 0; p < 600; p++) {
      const raw = optimizerAssets.map(() => Math.random());
      const sum = raw.reduce((s, v) => s + v, 0);
      const weights = raw.map(v => v / sum);
      const pRet = weights.reduce((s, w, i) => s + w * rets[i], 0);
      // Simplified covariance (assumes 0 correlation between assets for simplicity)
      const pVol = Math.sqrt(weights.reduce((s, w, i) => s + (w * vols[i]) ** 2, 0));
      const sharpe = pVol ? (pRet - 0.02) / pVol : 0;
      portfolios.push({ weights, ret: pRet, vol: pVol, sharpe });
    }

    // Find max sharpe and min variance
    const maxSharpe = portfolios.reduce((best, p) => p.sharpe > best.sharpe ? p : best, portfolios[0]);
    const minVar = portfolios.reduce((best, p) => p.vol < best.vol ? p : best, portfolios[0]);

    // User's current portfolio
    const totalW = optimizerAssets.reduce((s, a) => s + a.weight, 0);
    const userWeights = optimizerAssets.map(a => a.weight / totalW);
    const userRet = userWeights.reduce((s, w, i) => s + w * rets[i], 0);
    const userVol = Math.sqrt(userWeights.reduce((s, w, i) => s + (w * vols[i]) ** 2, 0));

    // Build chart
    const ctx = el('frontierChart'); if (!ctx) return;
    if (frontierChart) frontierChart.destroy();

    frontierChart = new Chart(ctx.getContext('2d'), {
      type: 'scatter',
      data: {
        datasets: [
          { label: 'Portfolios', data: portfolios.map(p => ({ x: +(p.vol * 100).toFixed(2), y: +(p.ret * 100).toFixed(2) })), backgroundColor: 'rgba(41,98,255,0.3)', pointRadius: 3 },
          { label: 'Max Sharpe', data: [{ x: +(maxSharpe.vol * 100).toFixed(2), y: +(maxSharpe.ret * 100).toFixed(2) }], backgroundColor: DARK.gold, pointRadius: 10, pointStyle: 'star' },
          { label: 'Min Variance', data: [{ x: +(minVar.vol * 100).toFixed(2), y: +(minVar.ret * 100).toFixed(2) }], backgroundColor: DARK.green, pointRadius: 10, pointStyle: 'triangle' },
          { label: 'Your Portfolio', data: [{ x: +(userVol * 100).toFixed(2), y: +(userRet * 100).toFixed(2) }], backgroundColor: DARK.red, pointRadius: 10, pointStyle: 'rectRot' },
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { labels: { color: DARK.text } }, tooltip: { callbacks: { label: ctx => `Vol: ${ctx.parsed.x}% | Ret: ${ctx.parsed.y}%` } } },
        scales: { x: { title: { display: true, text: 'Volatility (%)', color: DARK.muted }, grid: { color: DARK.border }, ticks: { color: DARK.muted } }, y: { title: { display: true, text: 'Expected Return (%)', color: DARK.muted }, grid: { color: DARK.border }, ticks: { color: DARK.muted } } }
      }
    });

    // Update result metrics
    const fmtWeights = (p) => optimizerAssets.map((a, i) => `${a.name}: ${(p.weights[i] * 100).toFixed(1)}%`).join(' | ');
    const setOpt = (id, val) => { const e = el(id); if (e) e.textContent = val; };
    setOpt('opt-sharpe-weights', fmtWeights(maxSharpe));
    setOpt('opt-minvar-weights', fmtWeights(minVar));
    setOpt('opt-sharpe-return', fmtPct(maxSharpe.ret * 100));
    setOpt('opt-sharpe-vol', fmt(maxSharpe.vol * 100) + '%');
    setOpt('opt-sharpe-ratio', fmt(maxSharpe.sharpe));
    setOpt('opt-minvar-vol', fmt(minVar.vol * 100) + '%');
  }

  // ─── TOOLTIPS ──────────────────────────────────────────────
  function initTooltips() {
    document.querySelectorAll('.tooltip-wrap').forEach(t => {
      t.style.cssText = 'cursor:help;color:var(--text-muted);font-size:0.8em;margin-left:4px;position:relative;';
      t.addEventListener('mouseenter', function () {
        const tip = document.createElement('div');
        tip.className = 'wiz-tooltip';
        tip.textContent = this.dataset.tip;
        tip.style.cssText = 'position:absolute;bottom:125%;left:50%;transform:translateX(-50%);background:#21262d;color:#e6edf3;padding:8px 12px;border-radius:8px;font-size:0.8rem;width:200px;z-index:999;border:1px solid #30363d;line-height:1.5;pointer-events:none;white-space:normal;';
        this.appendChild(tip);
      });
      t.addEventListener('mouseleave', function () {
        this.querySelectorAll('.wiz-tooltip').forEach(e => e.remove());
      });
    });
  }

  // ─── TABS ──────────────────────────────────────────────────
  function initTabs() {
    document.querySelectorAll('.analytics-tab').forEach(tab => {
      tab.addEventListener('click', function () {
        document.querySelectorAll('.analytics-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.analytics-tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        const content = el('tab-' + this.dataset.tab);
        if (content) content.classList.add('active');
      });
    });

    document.querySelectorAll('.portfolio-subtab').forEach(tab => {
      tab.addEventListener('click', function () {
        document.querySelectorAll('.portfolio-subtab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.portfolio-subtab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        const content = el('subtab-' + this.dataset.subtab);
        if (content) content.classList.add('active');
      });
    });

    document.querySelectorAll('.period-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        loadSnapshotsFromServer(this.dataset.period);
      });
    });
  }

  // ─── INIT ──────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    initTabs();
    initTooltips();

    // Initial simulation
    const prices = gbm(100, 252, 0.10, 0.18);
    runSimulation(prices);

    // Run button
    const runBtn = el('run-sim');
    if (runBtn) runBtn.addEventListener('click', async () => {
      const useReal = el('use-real-data')?.checked;
      let prices;
      if (useReal) {
        prices = await fetchRealData();
        if (!prices) prices = gbm(100, Number(el('sim-days').value) || 252, Number(el('drift').value) || 0.10, Number(el('volatility').value) || 0.18);
        el('price-chart-badge').textContent = 'LIVE';
        el('price-chart-badge').style.background = 'var(--gain)';
      } else {
        prices = gbm(100, Number(el('sim-days').value) || 252, Number(el('drift').value) || 0.10, Number(el('volatility').value) || 0.18);
        el('price-chart-badge').textContent = 'SIM';
        el('price-chart-badge').style.background = '';
      }
      runSimulation(prices);
    });

    // Animate button
    const playBtn = el('play-sim');
    if (playBtn) playBtn.addEventListener('click', () => {
      const prices = gbm(100, Number(el('sim-days').value) || 252, Number(el('drift').value) || 0.10, Number(el('volatility').value) || 0.18);
      animate(prices);
    });

    // Real data toggle
    const realToggle = el('use-real-data');
    if (realToggle) realToggle.addEventListener('change', function () {
      const notice = el('real-data-notice');
      notice.style.display = this.checked ? 'block' : 'none';
    });

    // Portfolio Tracker — load from server
    loadHoldingsFromServer();

    const addBtn = el('add-holding-btn');
    if (addBtn) addBtn.addEventListener('click', () => {
      el('add-holding-form').style.display = 'block';
      el('h-date').value = new Date().toISOString().split('T')[0];
    });

    const cancelBtn = el('cancel-holding-btn');
    if (cancelBtn) cancelBtn.addEventListener('click', () => { el('add-holding-form').style.display = 'none'; });

    const fetchBtn = el('fetch-price-btn');
    if (fetchBtn) fetchBtn.addEventListener('click', () => fetchLivePrice(el('h-ticker').value.trim().toUpperCase()));

    const saveBtn = el('save-holding-btn');
    if (saveBtn) saveBtn.addEventListener('click', () => {
      const name = el('h-name').value.trim();
      const ticker = el('h-ticker').value.trim().toUpperCase();
      const units = parseFloat(el('h-units').value);
      const buy_price = parseFloat(el('h-buy-price').value);
      const current_price = parseFloat(el('h-current-price').value);
      const date = el('h-date').value;
      if (!name || !units || !buy_price || !current_price) { alert('Please fill in all required fields.'); return; }
      addHoldingToServer({ name, ticker, units, buy_price, current_price, date });
      el('add-holding-form').style.display = 'none';
      ['h-name','h-ticker','h-units','h-buy-price','h-current-price'].forEach(id => { const e = el(id); if (e) e.value = ''; });
    });

    // Refresh All Prices button
    const refreshBtn = el('refresh-prices-btn');
    if (refreshBtn) refreshBtn.addEventListener('click', refreshAllPrices);

    // Portfolio Optimizer
    renderOptimizerAssets();

    const addAssetBtn = el('add-asset-btn');
    if (addAssetBtn) addAssetBtn.addEventListener('click', () => {
      if (optimizerAssets.length >= 5) { alert('Maximum 5 assets supported.'); return; }
      optimizerAssets.push({ name: 'Asset ' + String.fromCharCode(65 + optimizerAssets.length), ret: 8, vol: 12, weight: Math.floor(100 / (optimizerAssets.length + 1)) });
      renderOptimizerAssets();
    });

    window.wizOptUpdate = (i, key, val) => { optimizerAssets[i][key] = val; };
    window.wizOptRemove = (i) => { optimizerAssets.splice(i, 1); renderOptimizerAssets(); };

    const runOptBtn = el('run-optimizer-btn');
    if (runOptBtn) runOptBtn.addEventListener('click', runOptimizer);
  });

})();
