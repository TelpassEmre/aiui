# AI UI Panel

> Yapay zeka departmanı için geliştirilmiş yönetim paneli.
> `aiui.telpass.io` | Ubuntu 22.04 LTS | PHP 8.3 + Slim 4 + MariaDB

---

## 🎯 Özellikler

- 🔐 Session tabanlı kullanıcı girişi ve rol yönetimi
- 📊 Side-by-side çoklu DB dashboard (aivoice, taksimchat, basechat...)
- 📋 STT Jobs tablosu — filtreli, aranabilir
- ✏️ Transcript düzeltme ekranı (remote DB write)
- ⚙️ App Settings / Env Variables / ML Model / Feature Flags yönetimi
- 🖥️ Supervisor UI — process başlat/durdur/yeniden başlat
- 👥 Kullanıcı yönetimi (CRUD)
- 📝 Audit log

---

## 🏗️ Stack

| Katman | Teknoloji |
|--------|-----------|
| Web Server | Nginx 1.24 |
| Runtime | PHP 8.3-FPM |
| Router | Slim Framework 4 |
| ORM | Illuminate/Database (Eloquent) |
| Template | Plates |
| Local DB | MariaDB 10.11 |
| Cache | Redis 7 |
| Process | Supervisor 4 |
| Frontend | Tailwind CSS + Alpine.js + Chart.js (CDN) |

---

## 🚀 Kurulum

Detaylı kurulum için: [docs/SETUP.md](docs/SETUP.md)

### Hızlı Başlangıç

```bash
# 1. Repoyu klonla
git clone https://github.com/TelpassEmre/aiui.git /var/www/aiui

# 2. Bağımlılıkları yükle
cd /var/www/aiui
composer install

# 3. Config dosyasını oluştur
cp config/app.example.php config/app.php
# config/app.php içini düzenle

# 4. Kurulum scriptini çalıştır
bash docs/install.sh
```

---

## 📁 Dizin Yapısı

```
aiui/
├── public/              # Web root (index.php)
├── app/
│   ├── Controllers/     # Route controller'ları
│   ├── Models/          # Eloquent modeller
│   ├── Services/        # İş mantığı servisleri
│   ├── Middleware/      # Auth, role middleware
│   └── Views/           # Plates template'leri
├── config/              # Uygulama konfigürasyonu
├── docs/                # Kurulum rehberi ve scriptler
├── uploads/             # TXT/SRT yüklemeleri
└── logs/                # Uygulama logları
```

---

## 🗄️ DB Yapısı

- **Local:** `aiui_local` — kullanıcılar, session, UI ayarları, DB bağlantıları
- **Remote:** `aivoice`, `taksimchat`, `basechat`, `beurteletchat`, `maghrepchat`, `arabicchat`

---

## 📄 Lisans

MIT
