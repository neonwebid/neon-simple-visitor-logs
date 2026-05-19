# Neon Simple Visitor Logs

**Neon Simple Visitor Log** adalah plugin WordPress ringan dan fokus-performa untuk mencatat aktivitas pengunjung situs secara teknis dan terkontrol. Plugin ini dirancang untuk **monitoring, analisis, dan keamanan**, tanpa over-engineering dan tanpa membebani server.

---

## Fitur Utama

- Logging pengunjung:
  - IP address (IPv4 & IPv6)
  - IP long (untuk sorting & analisis)
  - Negara
  - ASN & ASN Number
  - Path yang diakses
  - Referrer
  - User-Agent
  - Timestamp

- **Exclude berbasis User-Agent**
  - Exact match (aman & cepat)
  - Cocok untuk bot, crawler, dan UA spesifik
  - Tidak berbasis IP / ASN (menghindari false positive)

- **ASN Manager**
  - Dasar pengelompokan ASN (Block / Challenge / Monitor)
  - Siap disinkronkan dengan Cloudflare (opsional)

- Desain database **index-aware**
  - Aman untuk data besar
  - Delete & query cepat
  - Tidak melakukan full table scan bodoh

- Arsitektur fail-safe
  - Logger tidak mengganggu frontend
  - Bisa dimatikan kapan saja

---

## Filosofi Desain

Plugin ini dibuat dengan prinsip:

- ❌ Tidak mencatat aset statis
- ❌ Tidak exclude berdasarkan IP / ASN
- ❌ Tidak ambil semua data ke PHP
- ❌ Tidak pakai LIKE `%...%` sembarangan
- ✅ Exact match untuk operasi kritikal
- ✅ Database yang bekerja, bukan PHP
- ✅ Logger boleh kehilangan data, **site tidak boleh mati**

---

## Struktur Database

### `visitor_logs`

Menyimpan seluruh log kunjungan.

Kolom utama:
- `ip_address`
- `ip_long`
- `country`
- `asn`
- `asn_number`
- `path`
- `referrer`
- `user_agent`
- `created_at`

Index penting:
- `created_at`
- `ip_long`
- `asn_number`
- `user_agent`

---

### `visitor_log_excludes`

Menyimpan User-Agent yang **tidak perlu dicatat**.

Kolom:
- `user_agent` (VARCHAR 255, exact match)
- `active`

Index:
- `(active, user_agent)` (composite / unique disarankan)


## Cara Kerja Exclude

- Exclude dilakukan **hanya berdasarkan User-Agent**
- Rule exclude berisi **signature User-Agent pendek**
- Pencocokan menggunakan **substring match (CONTAINS)**
- Proses matching dilakukan langsung di database dengan `LIMIT 1`
- Tidak ada loop PHP dan tidak memuat seluruh data ke memory
- Aman untuk volume data besar

Contoh User-Agent signature yang cocok untuk exclude:
```
Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)
Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)
Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)
facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)
Mozilla/5.0 (compatible; Applebot/0.1; +http://www.apple.com/go/applebot)

```

---

## Instalasi

1. Upload folder plugin ke:
```

wp-content/plugins/neon-simple-visitor-logs

```
2. Aktifkan melalui **WordPress Admin → Plugins**
3. Tabel database otomatis dibuat saat aktivasi

---

## Uninstall

- Saat plugin **di-uninstall**, seluruh tabel akan dihapus
- Deactivate **tidak menghapus data**

---

## Kebutuhan Sistem

- WordPress 6.8+
- PHP 8.2+
- MySQL / MariaDB (InnoDB)

---

## Catatan Penting

- Plugin ini **bukan analytics visual**
- Plugin ini **bukan pengganti Google Analytics**
- Plugin ini dibuat untuk:
- monitoring teknis
- forensic ringan
- analisis traffic non-visual
- security awareness

---

## Lisensi

GPL v2 or later  
https://www.gnu.org/licenses/gpl-2.0.html

---

## Author

**NeonWebId**  
https://neon.web.id/
