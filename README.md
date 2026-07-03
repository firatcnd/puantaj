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

## Uygulanan İş Kuralları

Tüm kurallar backend'de (FormRequest + Service katmanı) doğrulanır; frontend
doğrulamaları yalnızca kullanıcı deneyimi içindir.

- Aynı personel için aynı ay içinde yalnızca bir puantaj oluşturulabilir.
- Gün alanları, fazla mesai ve eksik mesai negatif olamaz.
- Gün alanlarının toplamı, ilgili ayın gün sayısını geçemez (28/29/30/31 duyarlı).
- Bir sefer için en az bir pozisyona mesai ücreti tanımlanmalıdır.
- Aynı sefer + pozisyon için ikinci kez ücret tanımlanamaz (form + DB unique).
- Puantaj hesaplanırken personelin **mevcut** pozisyonu esas alınır.
- Seçilen sefer için pozisyona ücret tanımlı değilse anlamlı bir hata döner
  (ör. *"İstanbul - Trabzon" seferi için "Muavin" pozisyonuna tanımlı bir mesai
  ücreti bulunamadı…*). Seed verisindeki İstanbul - Trabzon seferinde Muavin
  ücreti bilinçli olarak tanımlanmamıştır; bu senaryoyu test etmek için kullanılabilir.
- Puantaj ekranında mesai ücreti manuel değiştirilemez; birim ücret ve toplam
  tutar sunucu tarafında hesaplanır (istemciden gelen tutar alanları yok sayılır).
- Puantaj yazma işlemleri transaction içinde yapılır; ücret hatasında yarım kayıt kalmaz.

## Bonus Özellikler

- **Rol bazlı yetkilendirme:** Admin, Yönetim ekranından yeni rol tanımlayabilir
  (rol adı + görebileceği sayfalar) ve bu role bağlı kullanıcı açabilir. Sayfa
  erişimi hem menüde hem backend'de (`page:` middleware) uygulanır.
- **Excel'e / PDF'e aktarma:** Personel, sefer ve puantaj listelerinde; aktif
  filtre ve arama çıktıya da uygulanır (Türkçe karakterler için DejaVu Sans gömülü).
- **Toplu Excel içe aktarma:** Personel ekranında şablon indirme + yükleme; her
  satır tek tek doğrulanır, hatalı satırlar atlanıp raporlanır, geçerliler eklenir.
- **Loglama:** Personel/sefer/puantaj üzerindeki oluşturma/güncelleme/silme işlemleri
  kullanıcı ve alan bazında değişiklikleriyle (`eski → yeni`) kaydedilir; Yönetim
  ekranındaki "İşlem Kayıtları" sekmesinden görüntülenir (yalnızca admin).
- **Koyu tema:** Bootstrap 5.3 `data-bs-theme` ile; tercih localStorage'da saklanır.
- **Gelişmiş filtreleme + sayfalama:** Tüm liste ekranlarında arama, çoklu filtre
  ve sayfalama.

## Mimari Notlar

- **Service katmanı:** puantaj hesaplama mantığı `app/Services/TimesheetService.php`
  içinde; controller'lar ince tutuldu.
- **FormRequest:** tüm doğrulama kuralları `app/Http/Requests/` altında, Türkçe
  alan adları ve özel mesajlarla.
- **API Resource:** JSON çıktıları `app/Http/Resources/` ile standardize edildi.
- **Domain exception:** eksik ücret tanımı `MissingRateException` ile 422 +
  anlamlı mesaj olarak döner.

## Varsayımlar ve Ek Notlar

- Kimlik doğrulama Sanctum token ile yapılır; tüm API uçları oturum arkasındadır
  ve sayfa erişimi rol izinlerine göre kısıtlanır.
- "Aynı ay içinde tek puantaj" kuralı, soft delete ile çakışmaması için DB unique
  yerine uygulama seviyesinde (FormRequest) doğrulanır; silinen bir puantajın
  yerine aynı ay için yenisi açılabilir.
- Görev tarihlerinin puantajın ait olduğu ay içinde olması ek iş kuralı olarak
  eklendi (gerekçe: aylık puantajın bütünlüğü).
- Departman ve pozisyonlar normalize edilmiş ayrı tablolardadır; her pozisyon bir
  departmana bağlıdır (örn. Muavin yalnızca Operasyon'da seçilebilir) ve seeder ile gelir.
- Frontend'de görünen birim ücret yalnızca önizlemedir; sunucuya gönderilmez.
