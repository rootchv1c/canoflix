# StreamFlix — Kurulum Kılavuzu

## Gereksinimler
- PHP 8.0+
- Apache (mod_rewrite aktif)
- Web sunucusu (Nginx veya Apache)

## Kurulum

### 1. Dosyaları Yükle
Tüm dosyaları web sunucunun kök dizinine yükleyin (örn: `/var/www/html/streamflix/` veya cPanel'de `public_html/streamflix/`).

### 2. Dosya Yapısı
```
streamflix/
├── index.php           # Ana sayfa
├── filmler.php         # Tüm filmler
├── diziler.php         # Tüm diziler  
├── izle.php            # Video oynatıcı
├── login.php           # Giriş
├── register.php        # Kayıt
├── logout.php          # Çıkış
├── profil.php          # Profil
├── favoriler.php       # Favoriler
├── .htaccess           # Apache ayarları
├── assets/
│   ├── style.css       # CSS
│   ├── app.js          # JavaScript
│   ├── placeholder.svg
│   └── favicon.svg
├── api/
│   ├── search.php      # Arama API
│   ├── content.php     # İçerik detayları (TMDB)
│   └── favorite.php    # Favori toggle
├── data/
│   ├── filmler.json    # Film verisi (M3U'dan işlenmiş)
│   ├── diziler.json    # Dizi verisi (M3U'dan işlenmiş)
│   └── users.json      # Kullanıcı veritabanı (otomatik oluşur)
└── includes/
    ├── auth.php        # Kimlik doğrulama
    ├── data.php        # Veri fonksiyonları
    ├── tmdb.php        # TMDB API
    ├── navbar.php      # Navbar
    └── footer.php      # Footer
```

### 3. İzinler
```bash
chmod 755 data/
chmod 644 data/*.json
# users.json ve tmdb_cache için yazma izni:
chmod 777 data/
mkdir -p data/tmdb_cache
chmod 777 data/tmdb_cache
```

### 4. Nginx Config (opsiyonel)
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
location ~ \.php$ {
    fastcgi_pass unix:/run/php/php8.0-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}
```

## Özellikler
- ✅ Kayıt/Giriş sistemi (JSON tabanlı)
- ✅ 6.259 film, 1.781 dizi
- ✅ TMDB entegrasyonu (poster, açıklama, oyuncular, fragman)
- ✅ HLS stream oynatıcı
- ✅ Sezonsallı bölüm listesi
- ✅ Dublaj/Altyazı seçimi
- ✅ Favoriler & İzleme geçmişi
- ✅ Gelişmiş arama
- ✅ Kategori & tür filtreleme
- ✅ Mobil uyumlu (responsive)
- ✅ Netflix tarzı hero slider
- ✅ Benzer film önerileri

## TMDB API
API anahtarı `includes/tmdb.php` içinde tanımlı.
Tüm TMDB sonuçları `data/tmdb_cache/` klasöründe 7 gün önbelleğe alınır.
