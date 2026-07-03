# Puantaj Sistemi

Personellerin görev yaptığı seferleri ve bu seferlere bağlı aylık puantajlarını yöneten
web uygulaması. Sistem, personelin pozisyonuna göre sefer başına mesai ücretini
otomatik bulur ve toplam mesai tutarını hesaplar.

> Code Nova Yazılım A.Ş. teknik değerlendirme çalışması için geliştirilmiştir.

## Kullanılan Teknolojiler

| Katman | Teknoloji |
|---|---|
| Backend | Laravel 13 (PHP 8.4), REST API |
| Kimlik doğrulama | Laravel Sanctum (token) |
| Frontend | React 19 (Vite) — SPA |
| UI | Bootstrap 5 (açık/koyu tema), react-bootstrap, Bootstrap Icons |
| Grafik | Chart.js (react-chartjs-2) |
| Excel / PDF | maatwebsite/excel, barryvdh/laravel-dompdf |
| Loglama | spatie/laravel-activitylog |
| Veritabanı | MySQL 8 / MariaDB (utf8mb4) |

## Proje Yapısı

```
puantaj-sistemi/
├── backend/    → Laravel REST API
└── frontend/   → React (Vite) SPA
```

## Kurulum

### Gereksinimler

- PHP 8.3+ (pdo_mysql, mbstring, openssl eklentileri aktif)
- Composer 2
- Node.js 18+
- MySQL / MariaDB

### 1. Backend

```bash
cd backend
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
```

`.env` dosyasında veritabanı bağlantısını düzenleyin:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=puantaj_sistemi
DB_USERNAME=root
DB_PASSWORD=
```

Veritabanını oluşturun (MySQL/MariaDB):

```sql
CREATE DATABASE puantaj_sistemi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Migration ve seeder'ları çalıştırıp sunucuyu başlatın:

```bash
php artisan migrate --seed
php artisan serve             # http://127.0.0.1:8000
```

> Backend ve frontend aynı anda çalışmalıdır (iki ayrı terminal).

### 2. Frontend

```bash
cd frontend
npm install
npm run dev                   # http://localhost:5173
```

Vite dev sunucusu `/api` isteklerini `http://127.0.0.1:8000` adresine proxy'ler
(`frontend/vite.config.js`); ayrıca bir CORS ayarı gerekmez.

## Test Kullanıcı Bilgileri

Uygulama, Laravel Sanctum (token) ile korunur; rol bazlı sayfa yetkilendirmesi vardır.
Admin, **Yönetim** sayfasından yeni rol tanımlayabilir (rol adı + görebileceği sayfalar)
ve bu role bağlı kullanıcılar (e-posta + şifre) oluşturabilir.

| E-posta | Şifre | Rol | Erişim |
|---|---|---|---|
| admin@admin.com | 123 | Admin | Tüm sayfalar + Yönetim (rol/kullanıcı) |
| puantaj@puantaj.test | password | Puantaj Uzmanı | Yalnızca Dashboard ve Puantajlar |

## Modüller

- **Dashboard** — toplam personel/sefer, bu ay oluşturulan puantaj sayısı, toplam
  mesai tutarı, en çok görev yapılan sefer, en çok sefere çıkan personel + aylık
  tutar (bar) ve sefer dağılımı (doughnut) grafikleri.
- **Personel Yönetimi** — arama, sayfalama, ekleme/güncelleme/silme (soft delete).
- **Sefer Yönetimi** — CRUD + pozisyona göre mesai ücreti tanımlama (bir sefere
  istenildiği kadar pozisyon-ücret satırı).
- **Puantaj Yönetimi** — aylık puantaj oluşturma; sefer satırlarında birim ücret
  personelin pozisyonuna göre **sistem tarafından** bulunur, kullanıcı giremez;
  satır ve genel toplam otomatik hesaplanır. Listelemede personel / departman /
  pozisyon / ay / yıl filtreleri, arama ve sayfalama vardır.

## Veritabanı Tasarımı

```
departments ─┐
positions ──┼─< personnel ──< timesheets ──< timesheet_entries >── trips
positions ──< trip_position_rates >── trips
```

- `trip_position_rates`: sefer + pozisyon ikilisi **DB seviyesinde unique** —
  aynı sefer ve pozisyona ikinci ücret tanımlanamaz.
- `timesheets`: personelin puantaj anındaki pozisyonu (`position_id`) **snapshot**
  olarak saklanır; personelin pozisyonu sonradan değişse bile geçmiş puantajlar bozulmaz.
- `timesheet_entries`: birim ücret (`unit_rate`) satıra **snapshot** olarak yazılır;
  sefer ücreti sonradan güncellense bile onaylanmış puantaj tutarları değişmez.
- Tüm ana tablolarda **soft delete** kullanılır.

## Mimari Notlar

- **Service katmanı:** puantaj hesaplama mantığı `app/Services/TimesheetService.php`
  içinde; controller'lar ince tutuldu.
- **FormRequest:** tüm doğrulama kuralları `app/Http/Requests/` altında, Türkçe
  alan adları ve özel mesajlarla.
- **API Resource:** JSON çıktıları `app/Http/Resources/` ile standardize edildi.
- **Domain exception:** eksik ücret tanımı `MissingRateException` ile 422 +
  anlamlı mesaj olarak döner.
