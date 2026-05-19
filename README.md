# Neon Simple Visitor Logs

**Neon Simple Visitor Log** adalah plugin WordPress ringan dan fokus-performa untuk mencatat aktivitas pengunjung situs secara teknis dan terkontrol. Plugin ini dirancang untuk **monitoring, analisis, dan keamanan**, tanpa over-engineering dan tanpa membebani server.

---

## Fitur Utama

- Logging pengunjung:
  - IP address (IPv4 & IPv6)
  - IP long (untuk sorting & analisis)
  - Negara
  - ASN & ASN Number
  - ISP & Organisasi (Provider)
  - Pendeteksi Tipe (Hosting, ISP, Selular, Proxy, Mobile)
  - Path yang diakses
  - Referrer
  - User-Agent
  - Timestamp (bisa diatur Local WP Time atau UTC)

- **Exclude berbasis User-Agent**
  - Built-in list bots populer (Googlebot, Bingbot, dll)
  - Custom list tambahan yang bisa ditambah/dihapus via menu Settings
  - Tidak berbasis IP / ASN (menghindari false positive)

- **ASN Manager**
  - Analisis trafik berdasarkan Organisasi (ISP/Hosting) dan AS Number
  - Dilengkapi pendeteksi tipe (Hosting / ISP / Selular)
  - Tampilan tabel yang sepenuhnya responsif di perangkat mobile

- **Submenu Settings**
  - **Exclude User-Agent**: Satu textarea untuk mengelola daftar User-Agent tambahan yang ingin diabaikan
  - **Datetime Mode**: Pengaturan zona waktu untuk `created_at` agar mengikuti waktu lokal WordPress atau UTC

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
- `isp`
- `org`
- `hosting`
- `proxy`
- `mobile`
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

## Cara Kerja Exclude

- Exclude dilakukan berdasarkan built-in bots dan list tambahan yang diatur di WordPress Options (`svl_exclude_user_agents`) via submenu Settings.
- Pencocokan menggunakan **substring match (CONTAINS)**.
- Jika User-Agent mengandung salah satu signature, kunjungan tidak akan dicatat.

---

## Instalasi

1. Upload folder plugin ke:
```
wp-content/plugins/neon-simple-visitor-logs
```
2. Aktifkan melalui **WordPress Admin → Plugins**
3. Tabel database otomatis dibuat saat aktivasi (dan diperbarui otomatis saat update ke versi baru)

---

## Uninstall

- Saat plugin **di-uninstall**, seluruh tabel akan dihapus
- Deactivate **tidak menghapus data**

---

## Kebutuhan Sistem

- WordPress 6.4+
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
