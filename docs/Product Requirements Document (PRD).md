
---
## Proyek: Mini ERP Snack & Bakery

**Konteks Proyek:** Tugas Mata Kuliah Pemrograman Web

**Versi Dokumen:** 2.0 (Revisi Desktop-First & Laravel Stack)

### 1. Ringkasan Proyek

Aplikasi berbasis web yang berfungsi sebagai Mini Enterprise Resource Planning (ERP) khusus untuk UMKM rumahan (Snack & Bakery). Sistem ini mengintegrasikan fungsi Inventory Management System (IMS) dan Point of Sales (POS) dasar untuk mengatasi masalah pencatatan keuangan yang campur aduk, pelacakan stok bahan dan produk jadi yang tidak teratur, serta meminimalisir risiko barang kadaluarsa.

Sistem ini bersifat _internal tool_ yang sangat eksklusif. Customer tidak memiliki akses ke dalam sistem sama sekali; seluruh antarmuka dirancang khusus untuk operasional di belakang meja kasir dan dapur.

### 2. Tujuan & Fokus Penyelesaian Masalah

- **Pemisahan Entitas:** Memisahkan pembukuan bahan mentah (gudang) dan barang dagangan (produk jadi/etalase) agar penghitungan aset lebih akurat.
    
- **Manajemen Produksi:** Memberikan visibilitas terhadap sisa bahan baku dan estimasi kapasitas produksi roti/snack.
    
- **Pelacakan Kadaluarsa (Khusus Gudang):** Menyediakan sistem pengingat _countdown_ secara khusus untuk bahan mentah guna menghindari kerugian akibat barang basi. (Catatan: Produk jadi diasumsikan habis dalam 1-2 hari sehingga tidak memerlukan pelacakan _expired_).
    
- **Pencatatan Keuangan Sederhana:** Menghitung Laba Kotor (Gross Profit) yang didapat dari Total Pendapatan POS dikurangi Total Biaya Resep/Bahan Baku.
    

### 3. Arsitektur & Teknologi Terpilih

Aplikasi ini dibangun dengan prinsip **"Strict MVC"** (Model-View-Controller) untuk memisahkan logika bisnis, pengelolaan data, dan antarmuka pengguna secara tegas.

- **Pendekatan Desain:** _Desktop-First Design_ dengan sifat responsif. Antarmuka dioptimalkan untuk layar lebar (PC/Laptop Kasir), namun tetap fleksibel dan nyaman diakses melalui HP atau Tablet.
    
- **Frontend (View):** Livewire terintegrasi dengan Tailwind CSS (Dipilih untuk efisiensi penulisan _code_, _Single Page Application feel_ tanpa JavaScript yang berat, dan menghemat _token limit_ saat proses _development_).
    
- **Backend (Controller & Model):** Laravel 13.
    
- **Database:** MySQL.
    
- **Typography:** Menggunakan _font_ "Poppins" untuk memberikan nuansa antarmuka yang modern, bersih, dan mudah dibaca.
    
- **Lingkungan Pengembangan (Code Editor):** Visual Studio Code (VS Code).
    

### 4. Sistem Hak Akses & Keamanan (Otentikasi)

Sistem ini menggunakan otentikasi tunggal (_Single Role_).

- **Aktor:** Admin (Pemilik Usaha). Memiliki akses _Full CRUD_ (Create, Read, Update, Delete) ke seluruh modul.
    
- **Form Login:** Halaman pertama yang diakses untuk memverifikasi kredensial.
    
- **Logout:** Akses keluar sistem untuk mengamankan sesi _dashboard_.
    
- **Registrasi (Ditiadakan):** Untuk menjaga sistem _internal tool_ ini tidak disusupi pihak luar, halaman Registrasi ditiadakan. Akun Admin akan di-_generate_ secara internal oleh _developer_ menggunakan fitur Laravel Database Seeder.
    

### 5. Skema Database Inti (Relasional MySQL)

Struktur data dirancang seringkas mungkin dengan mengunci variabel harga beli agar statis untuk memudahkan kalkulasi HPP (_Harga Pokok Penjualan_).

**Tabel `users`** (Akses Sistem)

- `id` (PK)
    
- `name`, `email`, `password`
    

**Tabel `bahan_bakus`** (Dunia Gudang)

- `id` (PK)
    
- `nama_bahan` (String)
    
- `satuan` (String - Wajib satuan terkecil, misal: gram)
    
- `harga_beli_persatuan` (Integer - Harga statis)
    
- `stok` (Integer/Decimal)
    
- `minimum_stok` (Integer)
    
- `tanggal_expired` (Date)
    

**Tabel `produk_jadis`** (Dunia Toko/Etalase)

- `id` (PK)
    
- `nama_produk` (String)
    
- `harga_jual` (Integer)
    
- `stok` (Integer)
    
- `minimum_stok` (Integer)
    
    _(Tidak ada kolom tanggal_expired)_
    

**Tabel `reseps`** (Dunia Dapur - Pivot Jembatan)

- `id` (PK)
    
- `produk_jadi_id` (FK)
    
- `bahan_baku_id` (FK)
    
- `kebutuhan_satuan` (Integer/Decimal - Takaran resep)
    

**Tabel `transaksis`** (Dunia Kasir)

- `id` (PK)
    
- `metode_pembayaran` (Enum: tunai, qris)
    
- `total_pendapatan` (Integer)
    
- `total_hpp` (Integer - Modal dari bahan resep)
    
- `tanggal_transaksi` (Timestamp)
    

**Tabel `detail_transaksis`** (Rincian Pembelian)

- `id` (PK)
    
- `transaksi_id` (FK)
    
- `produk_jadi_id` (FK)
    
- `qty` (Integer)
    
- `subtotal` (Integer)
    

---

### 6. Spesifikasi UI/UX & Fungsionalitas Modul (The Three Worlds)

Pendekatan _Desktop-First_ diprioritaskan di sini, dengan antarmuka yang memanfaatkan ruang layar lebar (menggunakan komponen _Grid_ dan _Flexbox_ dari Tailwind CSS) agar Admin bisa melihat banyak data sekaligus tanpa perlu banyak _scrolling_. Interaktivitas tabel dan _form_ dikendalikan sepenuhnya oleh Livewire untuk pengalaman _Single Page Application_ (SPA) tanpa _reload_.

#### A. Dunia Gudang (Inventory Management - Bahan Mentah)

Modul ini adalah pusat kendali aset mentah. Tampilannya berupa tabel data reaktif dengan panel kontrol di bagian atas.

**1. Tata Letak UI (User Interface)**

- **Header Bar:** Menampilkan Judul "Dunia Gudang", tombol "Tambah Bahan Baku", dan _Search Bar_ (pencarian _real-time_ menggunakan Livewire `wire:model.live`).
    
- **Tabel Data Utama:** Menggunakan desain tabel _full-width_ dengan _font_ Poppins. Kolom terdiri dari: ID, Nama Bahan, Harga Modal Dasar, Stok (dengan indikator warna), Tanggal Kadaluarsa (dengan _countdown_), dan Aksi (Edit/Hapus).
    

**2. Fitur _Sorting_ Dinamis (Revisi Khusus Bahan Baku)**

Sistem pengurutan data kini difokuskan pada tabel Bahan Baku untuk memudahkan pemantauan aset mentah.

- _Header_ kolom tabel bersifat interaktif (dapat diklik).
    
- **Sort by Stok:** Jika _header_ "Stok" diklik, Livewire akan mengurutkan data stok dari yang paling banyak ke sedikit (_Descending_), atau sebaliknya (_Ascending_).
    
- **Sort by Expired Date:** Jika _header_ "Tanggal Kadaluarsa" diklik, tabel akan mengurutkan bahan baku dari yang paling cepat basi agar segera digunakan.
    

**3. Logika & Indikator Sistem**

- **Konversi Satuan _Back-End_:** Saat Admin melakukan operasi penambahan data melalui _form modal_ Livewire, input (misal: "1", satuan: "Kg") akan dicegat oleh fungsi _Controller_. Sistem akan secara otomatis menyimpannya ke _database_ dalam bentuk "1000 gram". Harga pun dipecah menjadi per 1 gram.
    
- **Indikator Warning Stok (Visual Tailwind):**
    
    - **Stok Banyak (Hijau):** Jika stok >= 3x Minimum Stock. _UI: Teks hijau dengan background badge hijau muda._
        
    - **Stok Cukup (Biru):** Jika stok >= 2x Minimum Stock. _UI: Teks biru dengan background badge biru muda._
        
    - **Stok Rendah (Kuning):** Jika stok >= 1x hingga < Minimum Stock. _UI: Teks kuning/orange, memunculkan icon peringatan._
        
    - **Stok Kosong (Merah):** Jika stok = 0. _UI: Baris tabel akan di-highlight merah transparan._
        
- **Sistem Pengingat Kadaluarsa (_Countdown_):** Memanfaatkan _library_ manipulasi waktu (seperti Carbon di Laravel). Sistem menghitung selisih tanggal hari ini dengan `tanggal_expired`. Hasilnya dirender menjadi format teks dinamis di sebelah tanggal (contoh: `(3 month 23 days to exp)`). Jika sisa waktu kurang dari 7 hari, warna teks berubah menjadi merah.
    

---

#### B. Dunia Dapur (Production & Jembatan Logika)

Modul ini adalah otak dari Mini ERP ini, tempat bertemunya data mentah (Gudang) dan data produk siap jual (Toko).

**1. Tata Letak UI (User Interface)**

- **Split Layout (Kiri dan Kanan):** Mengingat layar _desktop_ lebar, layar dibagi dua.
    
    - _Panel Kiri:_ Daftar Produk Jadi yang ada di sistem (dalam bentuk _Card_ atau _List_ rapi).
        
    - _Panel Kanan (Kalkulator/Resep):_ Area kerja dinamis yang akan berubah ketika salah satu produk di panel kiri diklik.
        

**2. Manajemen Resep (Recipe Linkage) & Modal Livewire**

- Fitur ini merelasikan satu Produk Jadi dengan banyak Bahan Baku.
    
- Ketika _Card_ Produk Jadi diklik, sebuah _Modal Pop-up_ Livewire (atau panel kanan) akan muncul menampilkan daftar kebutuhan resep.
    
- Contoh _Render_ UI: "Untuk membuat 1 Roti Coklat Lumer, membutuhkan: Tepung Terigu (100 gram), Telur Ayam (1 butir/50 gram), Coklat Batang (10 gram)."
    

**3. Kalkulator Produksi (Algoritma Estimasi Limitasi)**

- Ini adalah fitur tercanggih di modul Dapur. Sistem otomatis membaca _database_ `bahan_bakus` dan membandingkannya dengan kebutuhan `reseps`.
    
- **Logika _Bottleneck_:** Sistem menghitung setiap bahan baku. Misalnya, tepung di gudang cukup untuk membuat 100 roti, tetapi stok coklat hanya cukup untuk 20 roti. Maka, sistem akan menyimpulkan dan menampilkan di layar: **"Estimasi Maksimal Produksi: 20 Roti Coklat"**.
    
- **Visibilitas Bahan:** Menampilkan detail simulasi jika 20 roti diproduksi: "Tepung sisa 8000 gr", "Coklat sisa 0 gr".
    

**4. Eksekusi Produksi (Trigger Ajaib & Transaksi Database)**

- Di bawah Kalkulator Estimasi, terdapat input _field_: "Jumlah Produksi Aktual" dan sebuah tombol besar **"Proses Produksi"**.
    
- **Proses _Back-End_ (Statik & Mutlak):** Saat tombol ini ditekan, _method_ Livewire akan memicu fitur `DB::transaction()` milik Laravel. Sistem akan mengeksekusi dua hal secara bersamaan:
    
    1. Memotong kuantitas angka pada tabel stok bahan baku secara proporsional sesuai resep.
        
    2. Menambahkan kuantitas angka secara statis ke tabel stok produk jadi (etalase) tanpa mempedulikan pelacakan _batch_ (demi kesederhanaan).
        
- **Keamanan Eksekusi:** Jika salah satu bahan ternyata kurang di detik terakhir eksekusi, seluruh proses dibatalkan (_Rollback_) dan muncul pesan _error_, menjaga integritas data agar tidak ada nilai stok yang _minus/negatif_.
    

---

### 7. Spesifikasi UI/UX & Fungsionalitas Modul (Lanjutan)

#### C. Dunia Toko (POS / Kasir & Keuangan)

Modul ini adalah titik akhir dari perjalanan produk, berfokus murni pada pencatatan pendapatan penjualan (_revenue_) secara organik dan penarikan laporan laba kotor.

**1. Tata Letak UI Kasir (Point of Sales)**

- **Layout Keranjang Belanja:** Antarmuka dibagi menjadi dua bagian utama. Bagian kiri berisi daftar/grid Produk Jadi yang bisa diklik (hanya menampilkan data dari tabel Produk Jadi, bukan bahan baku). Bagian kanan adalah "Keranjang" (_Cart_) yang menampilkan item terpilih beserta subtotalnya.
    
- **Formulir Pembayaran Manual:** Di bawah keranjang, terdapat form sederhana untuk mencatat pembayaran.
    
    - **Metode Pembayaran:** Menyediakan opsi _dropdown_ atau _radio button_ untuk Tunai dan QRIS.
        
    - **Input Nominal:** Pemilik toko memasukkan nominal uang yang diterima secara langsung dan manual. Sistem tidak diintegrasikan dengan _payment gateway_ pihak ketiga (seperti Midtrans) agar tetap organik dan sederhana.
        

**2. Otomatisasi Transaksi (Checkout)**

- Saat admin menekan tombol "Simpan Transaksi", sistem Livewire akan memicu transaksi _database_.
    
- **Pemotongan Stok:** Sistem akan otomatis mengurangi jumlah stok pada tabel `produk_jadis` sesuai dengan kuantitas yang terjual.
    
- Pencatatan dilakukan ke tabel `transaksis` (untuk data induk pendapatan) dan `detail_transaksis` (untuk histori barang apa saja yang laku).
    

**3. Laporan Keuangan Sederhana (Dashboard / Rekap)**

- **Perputaran Uang:** Menampilkan riwayat transaksi berupa tabel (ID Transaksi, Tanggal, Uang Masuk dari POS).
    
- **Kalkulasi Gross Profit (Laba Kotor):** Sistem menghitung Laba Kotor secara otomatis dengan rumusan: `Total Pendapatan (dari Kasir) - Total Biaya HPP (Harga Pokok Penjualan dari Bahan Baku resep) = Gross Profit`. _(Catatan Logika: HPP didapatkan dari perhitungan harga beli statis bahan baku dikalikan dengan takaran resep produk yang terjual)._
    

---

### 8. Aturan Bisnis (Business Logic Rules) Terpenting

Ini adalah batasan dan aturan absolut yang mengikat _backend_ Laravel untuk memastikan aplikasi Mini ERP ini tidak menghasilkan _bug_ fatal pada perhitungan.

**1. Sistem Satuan Terkecil (Absolute Unit Rule)** 
User _frontend_ dapat menginput "1 Kg" atau "1 Liter" pada antarmuka, namun _backend_ (Controller) **wajib** mengonversi dan menyimpan nilai tersebut ke dalam satuan dasar terkecil (Gram / Mililiter) di _database_. Perhitungan kalkulator produksi di Dunia Dapur tidak akan akurat jika aturan ini dilanggar.

**2. Harga Modal Statis (Static HPP Rule)**
Demi kesederhanaan logika akademik, harga bahan baku diasumsikan konstan/statis. Saat produk jadi terjual, modal (HPP) yang dihitung adalah berdasarkan harga bahan baku yang tertera di _database_ pada saat itu, tanpa perlu melacak fluktuasi harga beli dari _supplier_ di masa lalu.

**3. Integritas Data Transaksi (ACID Compliance)** 
Pemotongan stok bahan baku dan penambahan stok produk jadi saat tombol "Proses Produksi" ditekan **harus bersifat mutlak**. Hal ini diwajibkan menggunakan `DB::transaction()` pada Laravel. Jika salah satu proses _query_ gagal (misal: karena server mati tiba-tiba atau stok bahan ternyata kurang), maka keduanya akan digagalkan sama sekali (_Rollback_).

**4. Kesederhanaan POS (Strict Organic Tracking)** Kasir difokuskan murni untuk pencatatan _revenue_ secara organik. Fitur-fitur kompleks seperti retur barang, diskon per-item bersyarat, atau penghitungan kembalian otomatis dengan kalkulator dinamis **ditiadakan** untuk menjaga spesifikasi tetap fokus pada inti ERP.

**5. Absensi Manajemen Kadaluarsa Produk Jadi (Rapid Turnover Assumption)**
Produk Jadi (roti/snack) diasumsikan memiliki _turnover_ yang sangat cepat (1-2 hari). Oleh karena itu, logika pelacakan tanggal kadaluarsa (termasuk metode FEFO) dihapus dari alur logika `produk_jadis`. Sistem hanya berfokus melacak kadaluarsa pada `bahan_bakus` (Dunia Gudang).

---

### 9. Poin Tambahan (Setup Lingkungan & Strategi Pengembangan)

#### A. Konfigurasi Database & Lingkungan Dasar (.env)

Aplikasi ini telah dihubungkan ke _database_ MySQL lokal. Berikut adalah konfigurasi akses _environment_ yang digunakan:

- `DB_CONNECTION=mysql`
    
- `DB_HOST=127.0.0.1`
    
- `DB_PORT=3306`
    
- `DB_DATABASE=snackbakery`
    
- `DB_USERNAME=root`
    
- `DB_PASSWORD=` _(kosong)_
    

#### B. Fondasi _Starter Kit_ Terpasang

Sistem otentikasi dasar dan integrasi _frontend-backend_ telah diinisialisasi menggunakan Laravel Breeze dan Livewire. Perintah berikut telah dieksekusi dan siap digunakan sebagai fondasi proyek:

1. `composer require laravel/breeze --dev`
    
2. `php artisan breeze:install livewire`
    
3. `npm install`
    
4. `npm run build`
    
5. `php artisan migrate`
    
6. `php artisan serve`
    

#### C. Strategi "Vibe Coding" - Generate Kerangka Bangunan

Untuk menjaga efisiensi instruksi kepada AI, pembuatan fondasi _database_ tidak dilakukan dengan meminta _source code_ secara satu per satu (file per file), melainkan menggunakan teknik "Generate Kerangka Bangunan".

**Instruksi Standar untuk AI:**

AI difungsikan sebagai generator _Command Line Interface_ (CLI). Daripada meminta AI menulis isi dari setiap _file_ Migration dan Model, _developer_ akan menginstruksikan AI untuk menghasilkan satu blok perintah _Artisan Command_ secara massal (contoh: `php artisan make:model BahanBaku -m`). Setelah kerangka (Model & Migration) berhasil dibuat oleh terminal, AI baru akan diinstruksikan untuk mengisi logika skema _database_ dan relasi (_Relationships_) di dalam _file_ yang sudah terbentuk tersebut.
