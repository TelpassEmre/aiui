# Kurulum Rehberi

## Ön Koşullar
- Ubuntu 22.04 LTS
- Root erişimi
- `aiui.telpass.io` → `185.250.36.17` DNS kaydı (Cloudflare)

## Hızlı Kurulum

```bash
# 1. Repoyu klonla
git clone https://github.com/TelpassEmre/aiui.git /var/www/aiui

# 2. Kurulum scriptini çalıştır
cd /var/www/aiui
bash docs/install.sh

# 3. Composer bağımlılıkları
composer install --no-dev --optimize-autoloader

# 4. Config düzenle
nano config/app.php
```

## İlk Giriş
- URL: `http://185.250.36.17`
- Kullanıcı: `admin`
- Şifre: `Admin123!`
- **İlk girişten sonra şifreyi değiştir!**

## Yeni Chat DB Ekleme

```sql
INSERT INTO db_connections
(name, display_name, host, port, username, password, database_name, color, sort_order)
VALUES
('taksimchat', 'Taksim Chat', '5.189.186.115', 3306, 'scriptuser', 'scriptuser', 'taksimchat', '#22D3EE', 1);
```

## SSL Kurulumu (Domain hazır olduktan sonra)

```bash
certbot --nginx -d aiui.telpass.io
```

## UFW Aktif Etme (Kurulum sonrası)

```bash
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```
