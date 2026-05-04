(function(){
  function el(id){return document.getElementById(id)}
  // Helpers
  function sma(array, window){
    const res = new Array(array.length).fill(null);
    let sum = 0;
    for(let i=0;i<array.length;i++){
      sum += array[i];
      if(i >= window) sum -= array[i-window];
      if(i >= window-1) res[i] = sum / window;
    }
    return res;
  }

  function gbmPrices(S0, days, mu, sigma){
    const dt = 1/252;
    const prices = [S0];
    for(let i=1;i<days;i++){
      const z = Math.sqrt(dt) * (Math.random()*2-1);
      const prev = prices[i-1];
      const nxt = prev * Math.exp((mu - 0.5*sigma*sigma)*dt + sigma * z);
      prices.push(nxt);
    }
    return prices;
  }

  function calcMetricsFromEquity(equity){
    const returns = [];
    for(let i=1;i<equity.length;i++) returns.push(equity[i]/equity[i-1]-1);
    const totalPnl = (equity[equity.length-1] - equity[0]);
    let peak=equity[0], maxDd=0;
    for(let v of equity){ if(v>peak) peak=v; const draw=(peak-v)/peak; if(draw>maxDd) maxDd=draw }
    const avg = returns.reduce((s,x)=>s+x,0)/Math.max(1,returns.length);
    const sd = Math.sqrt(returns.reduce((s,x)=>s+(x-avg)*(x-avg),0)/Math.max(1,returns.length-1) || 0);
    const sharpe = sd? (avg/sd * Math.sqrt(252)) : 0;
    const cagr = Math.pow(equity[equity.length-1]/equity[0], 252/Math.max(1,equity.length-1)) - 1;
    return {profit: totalPnl.toFixed(2), maxDrawdown:(maxDd*100).toFixed(2)+'%', sharpe:sharpe.toFixed(2), cagr:(cagr*100).toFixed(2)+'%'};
  }

  let equityChart = null;
  function createOrUpdateChart(equity){
    const labels = equity.map((_,i)=> 'Day ' + (i+1));
    const data = equity.map(v=> Number(v.toFixed(2)));
    const ctxEl = el('equityChart'); if(!ctxEl) return;
    if(!equityChart){
      equityChart = new Chart(ctxEl.getContext('2d'), {
        type: 'line', data: { labels: labels, datasets:[{label:'Equity', data:data, borderColor:'#0b6', backgroundColor:'rgba(11,102,34,0.08)', tension:0.2, fill:true}] },
        options:{ responsive:true, maintainAspectRatio:false, scales:{ x:{ display:false } } }
      });
    } else { equityChart.data.labels = labels; equityChart.data.datasets[0].data = data; equityChart.update(); }
  }

  // Strategy simulators — return {equity, trades}
  function simulateSMA(prices, capital){
    const short = 10, long = 50;
    const shortS = sma(prices, short);
    const longS = sma(prices, long);
    const positions = new Array(prices.length).fill(0); // 1 long, 0 flat
    for(let i=0;i<prices.length;i++){
      if(shortS[i] !== null && longS[i] !== null){
        positions[i] = shortS[i] > longS[i] ? 1 : 0;
      } else positions[i]=0;
      if(i>0 && positions[i] === 1 && positions[i-1] === 1) positions[i]=1;
    }
    // generate trades from position transitions
    const trades = [];
    let inPos = false, entryIdx=0, entryPrice=0;
    const equity = [capital];
    for(let i=0;i<prices.length;i++){
      const pos = positions[i];
      if(!inPos && pos===1){ inPos=true; entryIdx=i; entryPrice=prices[i]; }
      if(inPos && pos===0){ // exit
        const exitIdx = i; const exitPrice = prices[i];
        const pnl = exitPrice - entryPrice;
        trades.push({entryDay:entryIdx+1, exitDay:exitIdx+1, entryPrice, exitPrice, pnl});
        inPos=false;
      }
      // mark equity: if in position, exposure is (prices[i]/entryPrice) * capital; else capital
      if(inPos){ equity.push(capital + (prices[i] - entryPrice)); } else { equity.push(capital); }
    }
    // close at end if still open
    if(inPos){ const exitPrice = prices[prices.length-1]; trades.push({entryDay:entryIdx+1, exitDay:prices.length, entryPrice, exitPrice, pnl: exitPrice-entryPrice}); }
    return {equity, trades};
  }

  function simulateRandom(prices, capital){
    const equity=[capital]; const trades=[]; let i=0;
    while(i<prices.length-1){
      if(Math.random() < 0.08){ // open
        const entry = i; const entryPrice = prices[i];
        const len = Math.max(1, Math.floor(Math.random()*20));
        const exit = Math.min(prices.length-1, i+len);
        const exitPrice = prices[exit];
        trades.push({entryDay:entry+1, exitDay:exit+1, entryPrice, exitPrice, pnl: exitPrice-entryPrice});
        i = exit+1;
      } else { i++; }
      equity.push(capital);
    }
    return {equity, trades};
  }

  function renderMetrics(metrics){
    return `<table><tr><th>Profit</th><th>Max Drawdown</th><th>Sharpe</th><th>CAGR</th></tr><tr><td>${metrics.profit}</td><td>${metrics.maxDrawdown}</td><td>${metrics.sharpe}</td><td>${metrics.cagr}</td></tr></table>`;
  }

  function renderTrades(trades){
    if(!trades || trades.length===0) return '<p>(no trades)</p>';
    let h = '<table><tr><th>Entry</th><th>Exit</th><th>Entry Price</th><th>Exit Price</th><th>P&L</th></tr>';
    for(const t of trades){ h += `<tr><td>${t.entryDay}</td><td>${t.exitDay}</td><td>${t.entryPrice.toFixed(2)}</td><td>${t.exitPrice.toFixed(2)}</td><td>${t.pnl.toFixed(2)}</td></tr>` }
    h += '</table>';
    return h;
  }

  function csvDownload(filename, rows){
    const csv = rows.map(r=> r.map(c=> '"'+String(c).replace(/"/g,'""')+'"').join(',')).join('\n');
    const blob = new Blob([csv], {type:'text/csv'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = url; a.download = filename; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
  }

  function run(){
    const cap = Number(el('initial-capital').value) || 10000;
    const days = Number(el('sim-days').value) || 252;
    const sigma = Number(el('volatility').value) || 0.2;
    const scenario = el('scenario-select').value;
    const prices = gbmPrices(100, days, 0.05, sigma);
    let sim;
    if(scenario === 'sma') sim = simulateSMA(prices, cap);
    else sim = simulateRandom(prices, cap);
    const equity = sim.equity;
    const metrics = calcMetricsFromEquity(equity);
    el('analytics-results').innerHTML = `<h3>Metrics</h3>${renderMetrics(metrics)}`;
    el('trade-list').innerHTML = renderTrades(sim.trades);
    createOrUpdateChart(equity);
    // attach download data
    el('download-csv').onclick = function(){
      const rows = [['day','equity']].concat(equity.map((v,i)=>[i+1, v.toFixed(2)]));
      csvDownload('equity-series.csv', rows);
    };
  }

  document.addEventListener('DOMContentLoaded', function(){
    var runBtn = document.getElementById('run-sim');
    if(runBtn) runBtn.addEventListener('click', run);
    var downloadBtn = document.getElementById('download-csv');
    if(downloadBtn) {
      downloadBtn.addEventListener('click', function(e){ e.preventDefault(); });
    }
    if(runBtn) {
      setTimeout(run, 300);
    }
  });
})();
