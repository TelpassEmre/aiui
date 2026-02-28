<!DOCTYPE html>
<html lang="tr" class="h-full bg-gray-950">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? 'AI UI Panel' ?> — Telpass</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet">
  <style>
    [x-cloak] { display: none !important; }
    .sidebar-active { background: rgba(99,102,241,0.15); color: #818cf8; border-left: 3px solid #6366f1; }
    .badge-running  { background:#064e3b; color:#6ee7b7; }
    .badge-stopped  { background:#450a0a; color:#fca5a5; }
    .badge-error    { background:#431407; color:#fdba74; }
    .badge-pending  { background:#1e3a5f; color:#93c5fd; }
    .badge-completed{ background:#064e3b; color:#6ee7b7; }
    .badge-failed   { background:#450a0a; color:#fca5a5; }
    .badge-processing{background:#3b1f6e; color:#d8b4fe; }
    ::-webkit-scrollbar { width:6px; height:6px; }
    ::-webkit-scrollbar-track { background:#111827; }
    ::-webkit-scrollbar-thumb { background:#374151; border-radius:3px; }
    .dataTables_wrapper { color: #d1d5db; }
    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
      background:#1f2937; border:1px solid #374151; color:#f3f4f6;
      border-radius:6px; padding:4px 8px;
    }
    table.dataTable tbody tr { background:#111827 !important; }
    table.dataTable tbody tr:hover { background:#1f2937 !important; }
    table.dataTable thead th { background:#1f2937; color:#9ca3af; border-bottom:1px solid #374151; }
    table.dataTable tbody td { border-bottom:1px solid #1f2937; color:#d1d5db; }
  </style>
  <?= $extraHead ?? '' ?>
</head>
<body class="h-full bg-gray-950 text-gray-100" x-data="{ sidebarOpen: true }">

<div class="flex h-screen overflow-hidden">

  <!-- Sidebar -->
  <aside class="w-64 bg-gray-900 border-r border-gray-800 flex flex-col transition-all duration-300"
         :class="sidebarOpen ? 'w-64' : 'w-16'" x-cloak>

    <!-- Logo -->
    <div class="flex items-center gap-3 px-4 py-5 border-b border-gray-800">
      <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/>
        </svg>
      </div>
      <span class="font-bold text-white text-sm tracking-wide" x-show="sidebarOpen">AI UI Panel</span>
    </div>

    <!-- Nav -->
    <nav class="flex-1 py-4 overflow-y-auto space-y-1 px-2">
      <?php
      $nav = [
        ['href' => '/dashboard',           'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard'],
        ['href' => '/stt',                 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label' => 'STT Jobs'],
        ['href' => '/correction',          'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'label' => 'Düzeltme'],
        ['href' => '/settings',            'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'App Settings'],
        ['href' => '/settings/env',        'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4', 'label' => 'Env Variables'],
        ['href' => '/settings/ml',         'icon' => 'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', 'label' => 'ML Models'],
        ['href' => '/settings/servers',    'icon' => 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2', 'label' => 'Sunucular'],
        ['href' => '/settings/features',   'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01', 'label' => 'Feature Flags'],
        ['href' => '/supervisor',          'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'label' => 'Supervisor'],
        ['href' => '/users',               'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'label' => 'Kullanıcılar'],
        ['href' => '/settings/connections','icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4', 'label' => 'DB Bağlantıları'],
      ];
      $currentPath = strtok($_SERVER['REQUEST_URI'], '?');
      foreach ($nav as $item):
        $active = $currentPath === $item['href'] ? 'sidebar-active' : 'text-gray-400 hover:text-white hover:bg-gray-800';
      ?>
      <a href="<?= $item['href'] ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors <?= $active ?>">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?= $item['icon'] ?>"/>
        </svg>
        <span x-show="sidebarOpen"><?= $item['label'] ?></span>
      </a>
      <?php endforeach; ?>
    </nav>

    <!-- User info -->
    <div class="border-t border-gray-800 px-3 py-4">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
          <?= strtoupper(substr($_SESSION['user_username'] ?? 'U', 0, 1)) ?>
        </div>
        <div x-show="sidebarOpen" class="flex-1 min-w-0">
          <p class="text-sm font-medium text-white truncate"><?= htmlspecialchars($_SESSION['user_username'] ?? '') ?></p>
          <p class="text-xs text-gray-500"><?= $_SESSION['user_role'] ?? '' ?></p>
        </div>
        <a href="/logout" x-show="sidebarOpen" class="text-gray-500 hover:text-red-400 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
        </a>
      </div>
    </div>
  </aside>

  <!-- Main content -->
  <div class="flex-1 flex flex-col overflow-hidden">

    <!-- Topbar -->
    <header class="bg-gray-900 border-b border-gray-800 px-6 py-3 flex items-center justify-between flex-shrink-0">
      <div class="flex items-center gap-4">
        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-white">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
        <h1 class="text-sm font-semibold text-white"><?= $title ?? 'Dashboard' ?></h1>
      </div>
      <div class="flex items-center gap-3 text-xs text-gray-500">
        <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
        aiui.telpass.io
      </div>
    </header>

    <!-- Page content -->
    <main class="flex-1 overflow-y-auto p-6 bg-gray-950">
      <?= $content ?? '' ?>
    </main>

  </div>
</div>

<?= $extraScripts ?? '' ?>
</body>
</html>
