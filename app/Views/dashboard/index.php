<?php
$title = 'Dashboard';
ob_start();
?>
<!-- Stats Grid — AJAX ile doldurulur -->
<div id="dashboard-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-8">
  <div class="col-span-full flex items-center gap-3 text-gray-500">
    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
    </svg>
    Veriler yükleniyor...
  </div>
</div>

<!-- Chart Row -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
  <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
    <h3 class="text-sm font-semibold text-gray-300 mb-4">Son 7 Gün — İş Trendi</h3>
    <canvas id="trendChart" height="120"></canvas>
  </div>
  <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
    <h3 class="text-sm font-semibold text-gray-300 mb-4">Dil Dağılımı</h3>
    <canvas id="langChart" height="120"></canvas>
  </div>
</div>

<script>
let trendChart, langChart;

async function loadStats() {
  const res  = await fetch('/dashboard/stats');
  const data = await res.json();
  const grid = document.getElementById('dashboard-grid');
  grid.innerHTML = '';

  const trendDatasets = [];
  const langMap = {};
  const colors = ['#6366f1','#22d3ee','#a78bfa','#f59e0b','#34d399','#f87171'];

  data.forEach((db, i) => {
    const color = db.color || colors[i % colors.length];

    if (db.error) {
      grid.innerHTML += `
        <div class="bg-gray-900 rounded-xl border border-red-900/50 p-5">
          <div class="flex items-center gap-2 mb-2">
            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
            <span class="font-semibold text-white text-sm">${db.display_name || db.name}</span>
          </div>
          <p class="text-red-400 text-xs">${db.error}</p>
        </div>`;
      return;
    }

    const pct = db.total > 0 ? Math.round(db.completed / db.total * 100) : 0;

    grid.innerHTML += `
      <div class="bg-gray-900 rounded-xl border border-gray-800 p-5 hover:border-gray-700 transition">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full" style="background:${color}"></span>
            <span class="font-semibold text-white text-sm">${db.display_name || db.name}</span>
          </div>
          <span class="text-xs text-gray-500 bg-gray-800 px-2 py-1 rounded-full">${db.total} kayıt</span>
        </div>
        <div class="grid grid-cols-2 gap-3 mb-4">
          <div class="bg-gray-800/60 rounded-lg p-3">
            <p class="text-xs text-gray-500">Tamamlanan</p>
            <p class="text-lg font-bold text-green-400">${db.completed}</p>
          </div>
          <div class="bg-gray-800/60 rounded-lg p-3">
            <p class="text-xs text-gray-500">Bekleyen</p>
            <p class="text-lg font-bold text-blue-400">${db.pending}</p>
          </div>
          <div class="bg-gray-800/60 rounded-lg p-3">
            <p class="text-xs text-gray-500">İşleniyor</p>
            <p class="text-lg font-bold text-purple-400">${db.processing}</p>
          </div>
          <div class="bg-gray-800/60 rounded-lg p-3">
            <p class="text-xs text-gray-500">Hatalı</p>
            <p class="text-lg font-bold text-red-400">${db.failed}</p>
          </div>
        </div>
        <div class="mb-1 flex justify-between text-xs text-gray-500">
          <span>Tamamlanma</span><span>${pct}%</span>
        </div>
        <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
          <div class="h-full rounded-full transition-all" style="width:${pct}%;background:${color}"></div>
        </div>
      </div>`;

    // Trend dataset
    const trendLabels = db.trend?.map(t => t.date) || [];
    const trendCounts = db.trend?.map(t => t.count) || [];
    trendDatasets.push({
      label: db.display_name || db.name,
      data: trendCounts,
      borderColor: color,
      backgroundColor: color + '20',
      tension: 0.4, fill: true,
    });

    // Lang map
    db.languages?.forEach(l => {
      langMap[l.language_detected] = (langMap[l.language_detected] || 0) + l.count;
    });
  });

  // Trend Chart
  if (trendChart) trendChart.destroy();
  trendChart = new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: { labels: data[0]?.trend?.map(t => t.date) || [], datasets: trendDatasets },
    options: { responsive: true, plugins: { legend: { labels: { color: '#9ca3af', font: { size: 11 } } } },
      scales: { x: { ticks: { color: '#6b7280' }, grid: { color: '#1f2937' } },
                y: { ticks: { color: '#6b7280' }, grid: { color: '#1f2937' } } } }
  });

  // Lang Chart
  if (langChart) langChart.destroy();
  const langs  = Object.keys(langMap);
  const counts = Object.values(langMap);
  langChart = new Chart(document.getElementById('langChart'), {
    type: 'doughnut',
    data: { labels: langs, datasets: [{ data: counts,
      backgroundColor: ['#6366f1','#22d3ee','#a78bfa','#f59e0b','#34d399','#f87171'],
      borderWidth: 0 }] },
    options: { responsive: true, plugins: { legend: { position: 'right', labels: { color: '#9ca3af', font: { size: 11 } } } } }
  });
}

loadStats();
setInterval(loadStats, 30000); // 30 saniyede bir otomatik güncelle
</script>
<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layout/base.php';
