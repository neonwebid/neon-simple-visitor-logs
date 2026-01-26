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

**Wakhid Wicaksono**  
[https://wichaksono.github.io/](https://wichaksono.github.io/)
