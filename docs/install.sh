#!/bin/bash
# ============================================================
#  AI UI Panel — Kurulum Scripti
#  Hedef: Ubuntu 22.04 LTS | aiui.telpass.io
# ============================================================
set -e

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
log()  { echo -e "${GREEN}[✓]${NC} $1"; }
warn() { echo -e "${YELLOW}[!]${NC} $1"; }
err()  { echo -e "${RED}[✗]${NC} $1"; exit 1; }

[[ $EUID -ne 0 ]] && err "Root olarak çalıştır: sudo bash install.sh"

# ============================================================
# 1. Sistem güncelleme
# ============================================================
log "Sistem güncelleniyor..."
apt update && apt upgrade -y
apt install -y curl wget git unzip software-properties-common \
  gnupg2 ca-certificates lsb-release

# ============================================================
# 2. PHP 8.3
# ============================================================
log "PHP 8.3 kuruluyor..."
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.3 php8.3-fpm php8.3-cli \
  php8.3-mysql php8.3-redis php8.3-mbstring \
  php8.3-xml php8.3-curl php8.3-zip \
  php8.3-bcmath php8.3-intl php8.3-opcache

# PHP-FPM performans ayarları
sed -i 's/^pm = .*/pm = dynamic/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^pm.max_children = .*/pm.max_children = 20/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^pm.start_servers = .*/pm.start_servers = 5/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^pm.min_spare_servers = .*/pm.min_spare_servers = 3/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^pm.max_spare_servers = .*/pm.max_spare_servers = 8/' /etc/php/8.3/fpm/pool.d/www.conf

log "PHP 8.3 kuruldu: $(php -r 'echo PHP_VERSION;')"

# ============================================================
# 3. Nginx
# ============================================================
log "Nginx kuruluyor..."
apt install -y nginx

cat > /etc/nginx/sites-available/aiui << 'NGINX'
server {
    listen 80;
    server_name aiui.telpass.io 185.250.36.17;
    root /var/www/aiui/public;
    index index.php index.html;

    client_max_body_size 100M;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    gzip on;
    gzip_types text/plain text/css application/json application/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ^~ /uploads/ { deny all; }
    access_log /var/log/nginx/aiui_access.log;
    error_log  /var/log/nginx/aiui_error.log;
}
NGINX

ln -sf /etc/nginx/sites-available/aiui /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
log "Nginx hazır"

# ============================================================
# 4. MariaDB
# ============================================================
log "MariaDB kuruluyor..."
apt install -y mariadb-server mariadb-client

systemctl start mariadb

# DB oluştur
mysql -u root << 'SQL'
CREATE DATABASE IF NOT EXISTS aiui_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'aiui_user'@'localhost' IDENTIFIED BY 'AiUi_2024!Secure';
GRANT ALL PRIVILEGES ON aiui_local.* TO 'aiui_user'@'localhost';
FLUSH PRIVILEGES;
SQL

# Tablolar
mysql -u aiui_user -pAiUi_2024!Secure aiui_local << 'SQL'

CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(100) UNIQUE NOT NULL,
  email         VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','editor','viewer') DEFAULT 'viewer',
  is_active     TINYINT(1) DEFAULT 1,
  last_login    TIMESTAMP NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sessions (
  id            VARCHAR(128) PRIMARY KEY,
  user_id       INT NOT NULL,
  ip_address    VARCHAR(45),
  user_agent    TEXT,
  last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS db_connections (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(50) NOT NULL,
  display_name  VARCHAR(100),
  host          VARCHAR(100) NOT NULL,
  port          INT DEFAULT 3306,
  username      VARCHAR(100) NOT NULL,
  password      VARCHAR(255) NOT NULL,
  database_name VARCHAR(100) NOT NULL,
  color         VARCHAR(7) DEFAULT '#3B82F6',
  is_active     TINYINT(1) DEFAULT 1,
  sort_order    INT DEFAULT 0,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ui_settings (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  key_name    VARCHAR(100) UNIQUE NOT NULL,
  value       TEXT,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS audit_log (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NULL,
  action      VARCHAR(100) NOT NULL,
  entity      VARCHAR(100),
  old_value   JSON,
  new_value   JSON,
  ip_address  VARCHAR(45),
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_action (action)
);

-- Admin kullanıcı (şifre: Admin123!)
INSERT IGNORE INTO users (username, email, password_hash, role) VALUES (
  'admin',
  'admin@aiui.telpass.io',
  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin'
);

-- aivoice test bağlantısı
INSERT IGNORE INTO db_connections (name, display_name, host, port, username, password, database_name, color, sort_order)
VALUES ('aivoice', 'AI Voice (Test)', '5.189.186.115', 3306, 'scriptuser', 'scriptuser', 'aivoice', '#6366F1', 0);

SQL

log "MariaDB ve tablolar hazır"

# ============================================================
# 5. Redis
# ============================================================
log "Redis kuruluyor..."
apt install -y redis-server
sed -i 's/# maxmemory <bytes>/maxmemory 256mb/' /etc/redis/redis.conf
sed -i 's/# maxmemory-policy noeviction/maxmemory-policy allkeys-lru/' /etc/redis/redis.conf
log "Redis hazır"

# ============================================================
# 6. Supervisor
# ============================================================
log "Supervisor kuruluyor..."
apt install -y supervisor

cat > /etc/supervisor/conf.d/aiui.conf << 'SUPERVISOR'
[program:aiui-queue]
command=php /var/www/aiui/queue.php
directory=/var/www/aiui
user=www-data
autostart=false
autorestart=true
stdout_logfile=/var/www/aiui/logs/queue.log
stderr_logfile=/var/www/aiui/logs/queue_err.log
SUPERVISOR

log "Supervisor hazır"

# ============================================================
# 7. Composer
# ============================================================
log "Composer kuruluyor..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
log "Composer: $(composer --version)"

# ============================================================
# 8. Proje dizinleri
# ============================================================
log "Proje dizinleri oluşturuluyor..."
mkdir -p /var/www/aiui/{public,app,config,uploads,logs,storage}
mkdir -p /var/www/aiui/app/{Controllers,Models,Middleware,Services,Views,bootstrap}
mkdir -p /var/www/aiui/app/Views/{layout,auth,dashboard,stt,correction,settings,supervisor,users}
mkdir -p /var/www/aiui/uploads/{txt,srt,temp}
touch /var/www/aiui/logs/.gitkeep
chown -R www-data:www-data /var/www/aiui
chmod -R 755 /var/www/aiui
chmod -R 775 /var/www/aiui/uploads /var/www/aiui/logs

# ============================================================
# 9. Config dosyası
# ============================================================
if [ ! -f /var/www/aiui/config/app.php ]; then
  cp /var/www/aiui/config/app.example.php /var/www/aiui/config/app.php
  warn "config/app.php oluşturuldu — şifreyi değiştir!"
fi

# ============================================================
# 10. UFW geçici disable
# ============================================================
ufw disable
warn "UFW geçici olarak devre dışı — kurulum bitince aktif et"

# ============================================================
# 11. Servisleri başlat
# ============================================================
log "Servisler başlatılıyor..."
systemctl enable nginx php8.3-fpm mariadb redis supervisor
systemctl restart nginx php8.3-fpm mariadb redis supervisor

# ============================================================
# Özet
# ============================================================
echo ""
echo "============================================"
echo -e "${GREEN}✅ Kurulum Tamamlandı!${NC}"
echo "============================================"
echo ""
echo "Servis Durumları:"
for s in nginx php8.3-fpm mariadb redis supervisor; do
  status=$(systemctl is-active $s)
  if [ "$status" = "active" ]; then
    echo -e "  ${GREEN}✓${NC} $s"
  else
    echo -e "  ${RED}✗${NC} $s ($status)"
  fi
done
echo ""
echo "Sonraki Adımlar:"
echo "  1. cd /var/www/aiui && composer install"
echo "  2. nano config/app.php  (secret key değiştir)"
echo "  3. http://185.250.36.17  (giriş: admin / Admin123!)"
echo "  4. İlk girişte şifreni değiştir!"
echo ""
