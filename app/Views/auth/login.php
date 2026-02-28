<!DOCTYPE html>
<html lang="tr" class="h-full bg-gray-950">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Giriş — AI UI Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="h-full bg-gray-950 flex items-center justify-center">
  <div class="w-full max-w-md" x-data="{ showPass: false }">

    <!-- Logo -->
    <div class="text-center mb-8">
      <div class="w-16 h-16 rounded-2xl bg-indigo-600 flex items-center justify-center mx-auto mb-4">
        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/>
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-white">AI UI Panel</h1>
      <p class="text-gray-500 text-sm mt-1">Telpass AI Management</p>
    </div>

    <!-- Form -->
    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-8">
      <?php if (!empty($error)): ?>
        <div class="mb-4 px-4 py-3 bg-red-900/30 border border-red-800 rounded-lg text-red-400 text-sm">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="/login" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-400 mb-1.5">Kullanıcı Adı</label>
          <input type="text" name="username" required autocomplete="username"
            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white placeholder-gray-500
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            placeholder="admin">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-400 mb-1.5">Şifre</label>
          <div class="relative">
            <input :type="showPass ? 'text' : 'password'" name="password" required autocomplete="current-password"
              class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white placeholder-gray-500
                     focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition pr-10"
              placeholder="••••••••">
            <button type="button" @click="showPass = !showPass"
              class="absolute right-3 top-2.5 text-gray-500 hover:text-gray-300">
              <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
              </svg>
            </button>
          </div>
        </div>

        <button type="submit"
          class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg
                 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                 focus:ring-offset-gray-900">
          Giriş Yap
        </button>
      </form>
    </div>

    <p class="text-center text-xs text-gray-600 mt-6">Telpass AI UI Panel v1.0</p>
  </div>
</body>
</html>
