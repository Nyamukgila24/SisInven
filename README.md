# SisInven — Sistem Manajemen Inventaris Barang

Aplikasi web sederhana untuk mencatat, mencari, dan melaporkan barang
inventaris kantor. Dibuat dengan PHP + MySQL biasa (tanpa framework
berat), supaya mudah dipasang dan dirawat oleh siapa pun — termasuk
yang belum pernah pegang kode PHP sebelumnya.

---

## 1. Yang Perlu Disiapkan

Anda hanya butuh **XAMPP** (paket siap pakai yang berisi PHP + MySQL +
Apache dalam satu installer). Unduh di https://www.apachefriends.org/
sesuai sistem operasi Anda (Windows/Mac/Linux), lalu install seperti
biasa.

---

## 2. Cara Memasang (Instalasi)

1. **Salin folder ini** (`sisinven`) ke dalam folder `htdocs` milik
   XAMPP.
   - Windows biasanya: `C:\xampp\htdocs\sisinven`
   - Mac biasanya: `/Applications/XAMPP/htdocs/sisinven`
2. **Buka aplikasi XAMPP Control Panel**, klik tombol **Start** pada
   baris **Apache** dan **MySQL**. Tunggu sampai keduanya berwarna
   hijau.
3. **Buka phpMyAdmin**: buka browser, ketik `http://localhost/phpmyadmin`.
4. Klik menu **New / Baru** di kiri untuk membuat database baru,
   ATAU langsung saja klik tab **Import**, pilih file
   `database/sisinven.sql` yang ada di folder ini, lalu klik tombol
   **Go / Kirim**. File ini akan otomatis membuat database bernama
   `sisinven` beserta seluruh tabel dan beberapa contoh data.
5. Buka browser, ketik `http://localhost/sisinven`. Anda akan
   melihat halaman **Login**.
6. Masuk dengan akun contoh yang sudah disediakan:
   - **Username:** `admin`
   - **Password:** `admin123`
7. **Segera ganti password admin** ini lewat menu **Pengguna** setelah
   berhasil login, agar aplikasi lebih aman.

Selesai! Aplikasi sudah siap dipakai.

### Jika password MySQL Anda tidak kosong / berbeda

Buka file `config/database.php`, ubah bagian ini sesuai pengaturan
komputer Anda:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sisinven');
define('DB_USER', 'root');
define('DB_PASS', '');      // isi password MySQL Anda di sini jika ada
```

Ini satu-satunya file yang perlu diubah jika terjadi error
"Koneksi database gagal".

---

## 3. Struktur Folder (Peta Kode)

Supaya mudah dicari saat ingin mengubah sesuatu, berikut isi tiap
folder dan fungsinya:

```
sisinven/
├── config/
│   └── database.php        <- Pengaturan koneksi ke MySQL (SATU-SATUNYA file setting)
├── includes/
│   ├── functions.php        <- Semua "logika inti" aplikasi (login, generate kode barang, dst.)
│   ├── header.php           <- Menu navigasi atas, tampil di semua halaman
│   └── footer.php           <- Bagian bawah halaman
├── assets/
│   └── css/style.css        <- Semua warna & gaya tampilan, terpisah dari kode PHP
├── master/                  <- Kelola Kategori, Subkategori, Merek, Lokasi, Nama Peralatan
├── barang/                  <- Kelola data barang (tambah/ubah/hapus/cari)
├── user/                    <- Kelola akun pengguna & reset password
├── laporan/                 <- Laporan, filter, dan ekspor Excel/PDF
├── database/
│   └── sisinven.sql         <- Skema database + data contoh
├── login.php / logout.php   <- Halaman masuk & keluar
└── index.php                <- Dasbor (halaman pertama setelah login)
```

**Prinsip yang dipakai:** satu halaman = satu file = satu tugas. Jika
Anda ingin mengubah tampilan menu Merek misalnya, cukup buka
`master/merek.php` — semua yang berkaitan dengan Merek ada di situ,
tidak berserakan di banyak file.

---

## 4. Cara Kerja Fitur-Fitur Utama

### a. Data Master (Kategori, Subkategori, Merek, Lokasi, Nama Peralatan)
Menu **Data Master** dipakai untuk membuat "daftar pilihan baku" agar
pegawai tidak mengetik manual saat input barang (mencegah typo seperti
"HP" vs "hp" vs "Hewlett Packard"). Ada di menu atas → **Data Master**.

- **Kategori**: Anda yang menentukan kodenya secara manual (misalnya
  Umum = 1, Jaringan = 2).
- **Subkategori**: wajib dipilih induk Kategori-nya. Kodenya dibuat
  **otomatis** oleh sistem (urutan ke berapa di bawah kategori itu).
- **Nama Peralatan**: kodenya juga dibuat **otomatis** (urutan global).

### b. Kode Barang Otomatis
Ini bagian paling penting. Setiap kali barang baru disimpan, sistem
membuat Kode Barang sendiri dengan format:

```
[Kode Kategori].[Kode Subkategori].[Kode Peralatan].[Nomor Urut]
```

Contoh: Kategori "Umum" (kode 1) → Subkategori "Komputer & Laptop"
(kode 1) → Peralatan "Komputer" (kode 1) → ini komputer pertama yang
didaftarkan pada kombinasi tersebut → kode barangnya jadi `1.1.1.1`.
Komputer kedua dengan kombinasi persis sama akan otomatis menjadi
`1.1.1.2`, dan seterusnya. Pengguna **tidak bisa** mengetik kode ini
secara manual — logikanya ada di fungsi `generateKodeBarang()` dalam
file `includes/functions.php`.

Karena kode ini menyatu dengan Kategori/Subkategori/Peralatan yang
dipilih, ketiga pilihan tersebut **tidak bisa diubah lagi** setelah
barang disimpan (supaya kode lama tidak jadi salah/bentrok). Kalau
salah pilih sejak awal, cara memperbaikinya adalah: hapus data barang
itu, lalu input ulang dengan pilihan yang benar.

### c. Dropdown Bertingkat (Cascading)
Saat menambah barang, pilihan **Subkategori** akan otomatis menyaring
diri sesuai **Kategori** yang baru saja dipilih (mirip pilih
Provinsi → Kota). Ini dikerjakan oleh sedikit kode JavaScript di
`barang/form.php` yang memanggil `barang/get_subkategori.php`.

### d. Kondisi Barang & Nomor Inventaris
- **Kondisi** wajib dipilih setiap kali input/ubah barang: Baik,
  Rusak, atau Sedang Diperbaiki.
- **Nomor Inventaris Kantor** boleh dikosongkan — dipakai hanya untuk
  barang yang statusnya aset resmi kantor.

### e. Jejak Perubahan (Audit Sederhana)
Setiap barang menyimpan siapa yang terakhir mengubahnya dan kapan
(kolom `user_last_edit` dan `timestamp_last_edit`), otomatis terisi
tiap kali disimpan — tidak perlu diisi manual.

### f. Manajemen Pengguna
Semua pegawai yang terdaftar setara haknya (tidak ada admin vs staf).
Siapa pun yang login bisa menambah/mengubah/menghapus akun pengguna
lain, termasuk **mereset password** rekan kerja yang lupa (menu
**Pengguna** → ikon kunci 🔑) tanpa perlu masuk ke database.

### g. Laporan & Ekspor
Menu **Laporan** menyediakan dua jenis penyaringan sesuai permintaan:
berdasarkan **Kondisi Barang**, dan berdasarkan **ada/tidaknya Nomor
Inventaris Kantor**. Ada dua tombol ekspor:

- **Ekspor ke Excel** — mengunduh file `.csv` yang langsung bisa
  dibuka Microsoft Excel maupun Google Sheets. Sengaja dibuat format
  CSV (bukan `.xlsx` asli) supaya aplikasi tidak butuh instalasi
  library tambahan yang rumit (seperti Composer/PhpSpreadsheet) —
  jadi lebih ringan dan gampang dipasang di server kantor mana pun.
- **Ekspor ke PDF** — membuka halaman khusus cetak yang rapi. Tinggal
  klik tombol **"Cetak / Simpan sebagai PDF"**, lalu di kotak dialog
  print browser pilih tujuan **"Save as PDF"**. Cara ini dipilih
  dengan alasan yang sama: tidak butuh library PDF tambahan.

---

## 5. Fitur yang SENGAJA Belum Ada (Backlog)

Sesuai kesepakatan pada dokumen kebutuhan, **impor data dari Excel**
sengaja ditunda dulu di versi ini. Semua data barang saat ini
dimasukkan satu per satu lewat form web. Fitur impor bisa ditambahkan
belakangan setelah aplikasi ini berjalan stabil dan kantor sudah
punya format Excel yang seragam.

---

## 6. Pertanyaan Umum (FAQ)

**Q: Saya lupa password, gimana?**
A: Minta rekan kerja lain yang masih bisa login untuk membuka menu
**Pengguna**, klik ikon kunci 🔑 di baris nama Anda, lalu masukkan
password baru untuk Anda.

**Q: Bisa tidak menghapus Kategori/Merek/Lokasi yang salah ketik?**
A: Bisa, asal data master itu belum pernah dipakai oleh barang mana
pun (dan untuk Kategori, tidak sedang punya Subkategori di bawahnya).
Kalau sudah terpakai, sistem akan menolak penghapusan dengan pesan
yang jelas, supaya data barang lama tidak menjadi rusak/tidak
lengkap.

**Q: Kode barang-nya salah / kepilih kategori yang salah, gimana
membetulkannya?**
A: Hapus data barang tersebut dari menu **Data Barang**, lalu input
ulang dengan pilihan Kategori/Subkategori/Peralatan yang benar.

**Q: Boleh tidak dua pegawai login bersamaan dan menambah barang
sejenis pada saat yang sama?**
A: Boleh, aplikasi ini sudah mengunci proses pembuatan nomor urut
kode barang (`SELECT ... FOR UPDATE` di database) supaya dua barang
sejenis yang diinput hampir bersamaan tidak mendapat kode kembar.

---

## 7. Kalau Ingin Mengembangkan Lebih Lanjut

- Ingin menambah kolom baru di data barang? Ubah 3 tempat:
  `database/sisinven.sql` (tambah kolom), `barang/form.php` (tambah
  input di form), `barang/simpan.php` (proses simpan kolom baru).
- Ingin mengubah warna/tema? Cukup ubah `assets/css/style.css`, tidak
  perlu menyentuh file PHP mana pun.
- Ingin menambah menu master baru? Silakan tiru pola dari
  `master/merek.php` (contoh paling sederhana) — struktur tiap file
  master sengaja dibuat serupa.

---

Dibuat berdasarkan Dokumen Spesifikasi Kebutuhan Fungsional SISINVEN
v1.0 (27 Agustus 2026), untuk UPT Balai Latihan Kerja (BLK) Surabaya.
