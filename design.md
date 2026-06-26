# Design System & Guidelines

Dokumen ini berisi panduan desain (Design System) yang diadaptasi dari referensi *Finexy Dashboard* dengan mengkombinasikan prinsip proporsi *radius* ala Apple (continuous curves/squircles).

## 1. Tema & Palet Warna (Theme & Colors)

Desain ini mengusung tema **Light Mode** yang cerah, kontras tinggi, dan bersih.

| Kategori | Warna | Kode HEX | Penggunaan |
| :--- | :--- | :--- | :--- |
| **Primary** | Oranye/Coral | `#FF5B37` | Tombol utama, indikator aktif, *highlight* grafik. |
| **Background** | Light Gray | `#F7F8FA` | Latar belakang utama aplikasi (*body*). |
| **Surface/Card**| White | `#FFFFFF` | Latar belakang untuk *Cards*, *Dropdown*, *Modal*. |
| **Surface Dark**| Dark Charcoal| `#1A1D1F` | Kartu gelap (seperti kartu virtual), tombol sekunder gelap. |
| **Text Primary**| Near Black | `#1A1D1F` | Judul, angka nominal saldo, teks utama. |
| **Text Secondary**| Gray | `#6F767E` | Sub-judul, label, teks sekunder. |
| **Border** | Sangat Tipis | `#EFEFEF` | Garis pemisah (*divider*), *border* ringan. |
| **Success** | Green | `#10B981` | Angka persentase naik, status "Completed" atau "Active". |
| **Danger/Error**| Red | `#EF4444` | Angka persentase turun. |
| **Warning** | Yellow | `#F59E0B` | Status "Pending" atau "In Progress". |

## 2. Radius & Sudut Membulat (Apple's Radius Principle)

Apple menggunakan prinsip kelengkungan kontinu (*continuous curve*) dan relasi radius antara elemen luar dan dalam. Rumus dasarnya adalah: **Radius Dalam = Radius Luar - Padding**.

### Skala Radius (Border-Radius Scale)
- **`sm` (6px - 8px):** Untuk *badges*, *tags*, atau *checkbox*.
- **`md` (12px - 14px):** Untuk *inputs*, *dropdown menus*, dan tombol standar (*buttons*).
- **`lg` (20px - 24px):** Untuk kartu (*Cards*), *modals*, dan elemen *container* skala menengah.
- **`xl` (32px):** Untuk kontainer utama atau kartu besar.
- **`full` (9999px):** Untuk tombol *pill-shaped*, avatar profil, dan ikon bulat di *sidebar*.

### Contoh Penerapan Relasi Radius
Jika sebuah *Card* (elemen luar) memiliki padding `24px` dan radius `32px` (`xl`), maka tombol (elemen dalam) yang menempel di sudut dalam harus memiliki radius: `32px - 24px = 8px`. Namun, untuk desain *Finexy*, elemen di dalam biasanya independen. Gunakan radius **12px** untuk tombol di dalam kartu ber-radius **24px**.

## 3. Tipografi (Typography)

Disarankan menggunakan font Sans-Serif modern dan geometris yang bersih seperti **Inter**, **SF Pro Display** (standar Apple), atau **Outfit**.

| Elemen | Ukuran (Size) | Ketebalan (Weight) | Jarak Baris (Line Height) | Contoh Penggunaan |
| :--- | :--- | :--- | :--- | :--- |
| **Heading 1 (H1)**| 32px | Bold (700) | 40px | "Good morning", Total Saldo besar |
| **Heading 2 (H2)**| 24px | Semi-Bold (600) | 32px | Judul *Card* utama ("Total Income") |
| **Heading 3 (H3)**| 18px | Semi-Bold (600) | 28px | Angka statistik kecil ("$950") |
| **Body (Main)** | 14px | Regular (400) / Medium | 20px | Teks utama, isi tabel, *dropdown* |
| **Body (Small)** | 12px | Regular (400) | 16px | Label persentase, sub-teks, tanggal |

## 4. Tata Letak (Layout) & Spacing

Menggunakan sistem *Grid* dan *Flexbox* dengan jarak antar elemen yang konsisten (menggunakan kelipatan 4px atau 8px - *8pt grid system*).

### Dimensi Utama
- **Sidebar Width:** `80px` (kondisi *collapsed* menampilkan ikon bulat saja).
- **Top Nav Height:** `72px` hingga `80px`.
- **Max Width Content:** Biasanya fluid dengan `max-w-7xl` (sekitar 1280px) atau mengisi *viewport* dengan *padding* sisi `24px` atau `32px`.

### Jarak (Spacing / Gap)
- **Gap antar Cards (Grid):** `16px` atau `24px`.
- **Padding dalam Card Utama:** `24px` atau `32px`.
- **Padding dalam Card Kecil:** `16px` atau `20px`.
- **Jarak Heading dengan Konten:** `16px`.

## 5. Komponen Utama (Components)

### 1. Cards (Kartu)
- **Background:** Putih (`#FFFFFF`).
- **Border-Radius:** `24px` (meniru lengkungan halus ala iOS).
- **Shadow:** *Drop shadow* sangat tipis dan lembut (misal: `0 4px 20px rgba(0,0,0, 0.03)`) atau tanpa shadow sama sekali (mengandalkan kontras warna background `#F7F8FA`).
- **Padding:** Minimal `20px` - `24px`.

### 2. Buttons (Tombol)
- **Primary Button:** Background Oranye (`#FF5B37`), Teks Putih, Border-Radius `12px` atau bentuk *Pill* (`full`).
- **Dark Button:** Background Hitam (`#1A1D1F`), Teks Putih. (Digunakan untuk tombol "Transfer" di referensi).
- **Outline/Secondary Button:** Tanpa latar belakang (transparan), Border abu-abu tipis, Teks Hitam, radius `12px`.
- **Hover State:** Transisi `opacity` turun menjadi 80% atau warna sedikit lebih gelap.

### 3. Badges / Status Pills
- **Desain:** Bentuk sangat membulat (*pill*).
- **Pewarnaan:**
  - *Active/Completed:* Teks hijau, dot hijau, background putih/hijau sangat transparan.
  - *Pending:* Teks kuning, dot kuning.

### 4. Input & Search
- **Background:** Abu-abu sangat terang (`#F3F4F6` atau transparan dengan *border* tipis).
- **Border-Radius:** `12px` (Medium).
- **Ikon:** Menyatu di dalam *input field* (seperti ikon *magnifying glass* di kolom pencarian).

## Ringkasan Prinsip Implementasi CSS / Tailwind
Jika nantinya menggunakan Tailwind CSS:
- Sesuaikan `theme.colors` di `tailwind.config.js`.
- Gunakan `rounded-2xl` atau `rounded-3xl` untuk kartu.
- Gunakan `bg-gray-50` untuk latar belakang `body`.
- Hindari shadow tebal (`shadow-lg`), gunakan `shadow-sm` atau modifikasi `box-shadow` dengan *opacity* warna hitam 2-3% saja.
