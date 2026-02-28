<?php
$title = 'STT Jobs';
ob_start();
?>
<div class="flex items-center justify-between mb-6">
  <div>
    <h2 class="text-lg font-bold text-white">STT Jobs</h2>
    <p class="text-sm text-gray-500">Speech-to-text dönüşüm kayıtları</p>
  </div>
  <div class="flex items-center gap-3">
    <select id="dbSelect"
      class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:ring-indigo-500">
      <?php foreach ($connections as $conn): ?>
        <option value="<?= $conn->name ?>"><?= htmlspecialchars($conn->display_name) ?></option>
      <?php endforeach; ?>
    </select>
    <select id="statusFilter"
      class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2">
      <option value="">Tüm Durumlar</option>
      <option value="pending">Pending</option>
      <option value="processing">Processing</option>
      <option value="completed">Completed</option>
      <option value="failed">Failed</option>
    </select>
    <select id="langFilter"
      class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2">
      <option value="">Tüm Diller</option>
      <option value="tr">Türkçe</option>
      <option value="ar">Arapça</option>
      <option value="fr">Fransızca</option>
      <option value="en">İngilizce</option>
    </select>
  </div>
</div>

<div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
  <table id="sttTable" class="w-full text-sm" style="width:100%">
    <thead>
      <tr>
        <th>ID</th>
        <th>Job ID</th>
        <th>Caller ID</th>
        <th>Durum</th>
        <th>Dil</th>
        <th>Kelime</th>
        <th>Güven</th>
        <th>Bayrak</th>
        <th>Tarih</th>
        <th>İşlem</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<script>
let table;

const statusBadge = {
  pending:    '<span class="badge-pending px-2 py-0.5 rounded text-xs font-medium">Pending</span>',
  processing: '<span class="badge-processing px-2 py-0.5 rounded text-xs font-medium">Processing</span>',
  completed:  '<span class="badge-completed px-2 py-0.5 rounded text-xs font-medium">Completed</span>',
  failed:     '<span class="badge-failed px-2 py-0.5 rounded text-xs font-medium">Failed</span>',
};

function buildTable() {
  const db = document.getElementById('dbSelect').value;
  if (table) { table.destroy(); }
  table = $('#sttTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '/stt/data',
      data: d => ({
        ...d,
        db: document.getElementById('dbSelect').value,
        status: document.getElementById('statusFilter').value,
        lang:   document.getElementById('langFilter').value,
      })
    },
    columns: [
      { data: 'id',          width: '50px' },
      { data: 'job_id',      render: d => `<span class="font-mono text-xs text-gray-400">${d}</span>` },
      { data: 'caller_id',   render: d => `<span class="text-indigo-400">${d || '-'}</span>` },
      { data: 'status',      render: d => statusBadge[d] || d },
      { data: 'language',    render: d => d ? `<span class="uppercase text-xs bg-gray-700 px-1.5 py-0.5 rounded">${d}</span>` : '-' },
      { data: 'word_count',  render: d => d || '-' },
      { data: 'avg_confidence' },
      { data: 'profanity',   render: (d, t, row) => (row.profanity || '') + (row.underage || '') },
      { data: 'created_at',  render: d => `<span class="text-gray-400 text-xs">${d}</span>` },
      { data: 'id',          render: (id) => `
        <div class="flex gap-2">
          <a href="/stt/${id}?db=${document.getElementById('dbSelect').value}"
             class="text-xs text-indigo-400 hover:text-indigo-300">Detay</a>
          <a href="/correction/${id}?db=${document.getElementById('dbSelect').value}"
             class="text-xs text-green-400 hover:text-green-300">Düzelt</a>
        </div>` },
    ],
    pageLength: 25,
    language: { url: '' },
    dom: '<"flex justify-between items-center mb-4"lf>rtip',
  });
}

document.getElementById('dbSelect').addEventListener('change', buildTable);
document.getElementById('statusFilter').addEventListener('change', () => table?.ajax.reload());
document.getElementById('langFilter').addEventListener('change', () => table?.ajax.reload());

buildTable();
</script>
<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layout/base.php';
