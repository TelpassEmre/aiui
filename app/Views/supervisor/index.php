<?php
$title = 'Supervisor';
ob_start();
?>
<div class="flex items-center justify-between mb-6">
  <div>
    <h2 class="text-lg font-bold text-white">Supervisor</h2>
    <p class="text-sm text-gray-500">Process yönetimi</p>
  </div>
  <button onclick="loadProcesses()"
    class="flex items-center gap-2 text-sm bg-gray-800 hover:bg-gray-700 px-4 py-2 rounded-lg transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
    </svg>
    Yenile
  </button>
</div>

<div id="processList" class="grid gap-4 mb-6"></div>

<!-- Log Modal -->
<div id="logModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
  <div class="bg-gray-900 rounded-xl border border-gray-700 w-3/4 max-h-[70vh] flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-700">
      <h3 class="font-semibold text-white" id="logTitle">Log</h3>
      <button onclick="document.getElementById('logModal').classList.add('hidden')" class="text-gray-400 hover:text-white">✕</button>
    </div>
    <pre id="logContent" class="flex-1 overflow-y-auto p-5 text-xs text-green-400 font-mono bg-gray-950 rounded-b-xl whitespace-pre-wrap"></pre>
  </div>
</div>

<script>
const statusColors = {
  RUNNING:  { dot: 'bg-green-500',  badge: 'badge-running'  },
  STOPPED:  { dot: 'bg-red-500',    badge: 'badge-stopped'  },
  ERROR:    { dot: 'bg-orange-500', badge: 'badge-error'    },
  FATAL:    { dot: 'bg-red-600',    badge: 'badge-error'    },
  UNKNOWN:  { dot: 'bg-gray-500',   badge: 'bg-gray-700 text-gray-300' },
};

async function loadProcesses() {
  const res  = await fetch('/supervisor/status');
  const data = await res.json();
  const list = document.getElementById('processList');

  list.innerHTML = data.processes.map(p => {
    const sc = statusColors[p.status] || statusColors.UNKNOWN;
    return `
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <span class="w-3 h-3 rounded-full ${sc.dot}"></span>
        <div>
          <p class="font-semibold text-white">${p.name}</p>
          <p class="text-xs text-gray-500">PID: ${p.pid || '-'} | ${p.uptime || ''}</p>
        </div>
        <span class="${sc.badge} px-2 py-0.5 rounded text-xs font-medium">${p.status}</span>
      </div>
      <div class="flex items-center gap-2">
        <button onclick="action('${p.name}','start')"
          class="text-xs bg-green-900/40 hover:bg-green-900/70 text-green-400 px-3 py-1.5 rounded-lg transition">▶ Başlat</button>
        <button onclick="action('${p.name}','stop')"
          class="text-xs bg-red-900/40 hover:bg-red-900/70 text-red-400 px-3 py-1.5 rounded-lg transition">■ Durdur</button>
        <button onclick="action('${p.name}','restart')"
          class="text-xs bg-yellow-900/40 hover:bg-yellow-900/70 text-yellow-400 px-3 py-1.5 rounded-lg transition">↺ Yeniden</button>
        <button onclick="showLogs('${p.name}')"
          class="text-xs bg-gray-800 hover:bg-gray-700 text-gray-300 px-3 py-1.5 rounded-lg transition">📄 Log</button>
      </div>
    </div>`;
  }).join('');
}

async function action(name, act) {
  await fetch('/supervisor/action', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `process=${encodeURIComponent(name)}&action=${act}`
  });
  setTimeout(loadProcesses, 1500);
}

async function showLogs(name) {
  document.getElementById('logTitle').textContent = name + ' — Log';
  document.getElementById('logContent').textContent = 'Yükleniyor...';
  document.getElementById('logModal').classList.remove('hidden');
  const res  = await fetch(`/supervisor/logs/${name}?lines=100`);
  const data = await res.json();
  document.getElementById('logContent').textContent = data.logs;
}

loadProcesses();
setInterval(loadProcesses, 10000);
</script>
<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layout/base.php';
