
## 1. Analisis Kebutuhan

Sistem E-Library digunakan untuk mencatat:

- data mahasiswa sebagai anggota perpustakaan,
- data buku beserta penerbitnya,
- riwayat peminjaman dan pengembalian buku.

### Asumsi Perancangan

1. Satu transaksi peminjaman dilakukan oleh satu mahasiswa dan dapat memuat lebih dari satu buku.
2. Dalam satu transaksi, satu judul buku hanya dipinjam satu eksemplar.
3. Pengembalian dicatat per buku, karena buku dalam satu transaksi dapat
   dikembalikan pada tanggal yang berbeda.
4. Buku yang belum dikembalikan memiliki `tgl_kembali` bernilai NULL.
5. Satu buku diterbitkan oleh satu penerbit, sedangkan satu penerbit dapat menerbitkan banyak buku.
6. Satu buku memiliki satu pengarang utama (disimpan sebagai satu atribut).
7. Penamaan mengikuti konvensi modul: huruf kecil, snake_case, nama tabel tunggal,
   dan nama Foreign Key identik dengan Primary Key yang dirujuk.

## 2. Identifikasi Entitas dan Atribut

### 2.1 Entitas Mahasiswa
| Atribut | Keterangan | Kunci |
|---|---|---|
| nim | Nomor induk mahasiswa | PK |
| nama_mhs | Nama lengkap | |
| prodi | Program studi | |
| angkatan | Tahun masuk | |
| no_hp | Nomor telepon | |

### 2.2 Entitas Penerbit
| Atribut | Keterangan | Kunci |
|---|---|---|
| penerbit_id | Identitas penerbit | PK |
| nama_penerbit | Nama penerbit | |
| alamat_penerbit | Alamat penerbit | |

### 2.3 Entitas Buku
| Atribut | Keterangan | Kunci |
|---|---|---|
| buku_id | Identitas buku | PK |
| isbn | Nomor ISBN | |
| judul | Judul buku | |
| pengarang | Pengarang utama | |
| tahun_terbit | Tahun terbit | |
| stok | Jumlah eksemplar tersedia | |
| penerbit_id | Penerbit buku | FK → penerbit |

### 2.4 Entitas Transaksi Peminjaman

Entitas ini diwujudkan dalam dua tabel: header transaksi dan detail buku yang dipinjam.

**a. Peminjaman (header)**

| Atribut | Keterangan | Kunci |
|---|---|---|
| peminjaman_id | Identitas transaksi | PK |
| nim | Mahasiswa peminjam | FK → mahasiswa |
| tgl_pinjam | Tanggal transaksi | |
| tgl_jatuh_tempo | Batas pengembalian | |

**b. Detail Peminjaman (buku dalam transaksi)**

| Atribut | Keterangan | Kunci |
|---|---|---|
| peminjaman_id | Transaksi induk | PK, FK → peminjaman |
| buku_id | Buku yang dipinjam | PK, FK → buku |
| tgl_kembali | Tanggal dikembalikan (NULL jika belum) | |
| denda | Denda keterlambatan buku tersebut | |

## 3. Relasi Antar Entitas

| Relasi | Kardinalitas | Penjelasan |
|---|---|---|
| penerbit - buku | 1 : N | Satu penerbit menerbitkan banyak buku |
| mahasiswa - peminjaman | 1 : N | Satu mahasiswa dapat melakukan banyak transaksi |
| peminjaman - detail_peminjaman | 1 : N | Satu transaksi memuat banyak buku |
| buku - detail_peminjaman | 1 : N | Satu buku dapat dipinjam berkali-kali |
| peminjaman - buku | M : N | Diwujudkan lewat tabel detail_peminjaman |

## 4. Simulasi Normalisasi

### 4.1 Bentuk Tidak Normal (UNF)

Data awal dicatat petugas perpustakaan dalam satu tabel besar. Setiap baris mewakili satu
transaksi, dan seluruh buku yang dipinjam pada transaksi ditulis dalam satu sel
sebagai kelompok berulang.

**Struktur kolom UNF:**

`peminjaman_id`, `nim`, `nama_mhs`, `prodi`, `angkatan`, `no_hp`, `tgl_pinjam`,
`tgl_jatuh_tempo`, dan kelompok berulang **Buku Dipinjam**
{`buku_id`, `isbn`, `judul`, `pengarang`, `tahun_terbit`, `stok`, `penerbit_id`,
`nama_penerbit`, `alamat_penerbit`, `tgl_kembali`, `denda`}

**Contoh data:**

| peminjaman_id | nim | nama_mhs | prodi | angkatan | no_hp | tgl_pinjam | tgl_jatuh_tempo | Buku Dipinjam |
|---|---|---|---|---|---|---|---|---|
| PJ001 | D121241001 | Rian | Informatika | 2024 | 081234567890 | 2025-10-01 | 2025-10-08 | {B001, 978-602-11-0001-1, Pemrograman Web dengan PHP, Andi Wijaya, 2021, 5, P01, Nusantara Press, Makassar, 2025-10-07, 0}<br>{B002, 978-602-11-0002-8, Basis Data Relasional, Sari Dewi, 2020, 3, P02, Cahaya Ilmu, Jakarta, 2025-10-10, 2000} |
| PJ002 | D121241002 | Akbar | Teknik Elektro | 2023 | 085298765432 | 2025-10-02 | 2025-10-09 | {B001, 978-602-11-0001-1, Pemrograman Web dengan PHP, Andi Wijaya, 2021, 5, P01, Nusantara Press, Makassar, 2025-10-09, 0}<br>{B003, 978-602-11-0003-5, Algoritma dan Struktur Data, Budi Santoso, 2019, 4, P01, Nusantara Press, Makassar, NULL, 0} |
| PJ003 | D121241001 | Rian | Informatika | 2024 | 081234567890 | 2025-10-15 | 2025-10-22 | {B003, 978-602-11-0003-5, Algoritma dan Struktur Data, Budi Santoso, 2019, 4, P01, Nusantara Press, Makassar, NULL, 0} |

*Keterangan: denda dihitung Rp1.000 per hari keterlambatan. `tgl_kembali` bernilai NULL
berarti buku belum dikembalikan.*

**Masalah pada bentuk UNF:**

1. Kolom **Buku Dipinjam** memuat lebih dari satu nilai dalam satu sel (PJ001 dan PJ002
   masing-masing berisi dua buku), sehingga belum memenuhi syarat 1NF.
2. Satu sel menggabungkan 11 atribut sekaligus, sehingga data tidak dapat dicari atau
   difilter per kolom, misalnya "cari semua peminjam buku B001".
3. Identitas mahasiswa dan data buku ditulis ulang di banyak tempat (Rian muncul di
   PJ001 dan PJ003, buku B001 muncul di PJ001 dan PJ002).

### 4.2 Konversi ke Bentuk Normal Pertama (1NF)

**Tabel hasil 1NF** (PK komposit: `peminjaman_id` + `buku_id`):

| peminjaman_id (PK-1) | buku_id (PK-2) | nim | nama_mhs | prodi | angkatan | no_hp | tgl_pinjam | tgl_jatuh_tempo | isbn | judul | pengarang | tahun_terbit | stok | penerbit_id | nama_penerbit | alamat_penerbit | tgl_kembali | denda |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| PJ001 | B001 | D121241001 | Rian | Informatika | 2024 | 081234567890 | 2025-10-01 | 2025-10-08 | 978-602-11-0001-1 | Pemrograman Web dengan PHP | Andi Wijaya | 2021 | 5 | P01 | Nusantara Press | Makassar | 2025-10-07 | 0 |
| PJ001 | B002 | D121241001 | Rian | Informatika | 2024 | 081234567890 | 2025-10-01 | 2025-10-08 | 978-602-11-0002-8 | Basis Data Relasional | Sari Dewi | 2020 | 3 | P02 | Cahaya Ilmu | Jakarta | 2025-10-10 | 2000 |
| PJ002 | B001 | D121241002 | Akbar | Teknik Elektro | 2023 | 085298765432 | 2025-10-02 | 2025-10-09 | 978-602-11-0001-1 | Pemrograman Web dengan PHP | Andi Wijaya | 2021 | 5 | P01 | Nusantara Press | Makassar | 2025-10-09 | 0 |
| PJ002 | B003 | D121241002 | Akbar | Teknik Elektro | 2023 | 085298765432 | 2025-10-02 | 2025-10-09 | 978-602-11-0003-5 | Algoritma dan Struktur Data | Budi Santoso | 2019 | 4 | P01 | Nusantara Press | Makassar | NULL | 0 |
| PJ003 | B003 | D121241001 | Rian | Informatika | 2024 | 081234567890 | 2025-10-15 | 2025-10-22 | 978-602-11-0003-5 | Algoritma dan Struktur Data | Budi Santoso | 2019 | 4 | P01 | Nusantara Press | Makassar | NULL | 0 |

**Hasil pemeriksaan:**

- Tidak ada lagi sel yang memuat lebih dari satu nilai, sehingga tabel memenuhi 1NF.
- Jumlah baris bertambah dari 3 menjadi 5, satu baris untuk setiap pasangan transaksi-buku.

**Masalah yang masih tersisa (redundansi):**

1. Data mahasiswa Rian (`nama_mhs`, `prodi`, `angkatan`, `no_hp`) ditulis berulang pada
   3 baris, yaitu PJ001 dua kali dan PJ003.
2. Data transaksi (`nim`, `tgl_pinjam`, `tgl_jatuh_tempo`) ditulis ulang pada setiap
   buku dalam transaksi yang sama.
3. Data buku B001 dan B003 beserta penerbitnya ditulis ulang setiap kali buku itu dipinjam.

### 4.3 Konversi ke Bentuk Normal Kedua (2NF)

**Langkah konversi:**

Berdasarkan analisis ketergantungan pada 1NF, ketergantungan parsial dipisahkan menjadi tiga tabel:

1. Atribut yang hanya bergantung pada `peminjaman_id` dipindahkan ke tabel `peminjaman`.
2. Atribut yang hanya bergantung pada `buku_id` dipindahkan ke tabel `buku`.
3. Atribut yang bergantung pada kombinasi `peminjaman_id` dan `buku_id` tetap berada di
   tabel `detail_peminjaman`.

**Tabel `peminjaman`** (PK: `peminjaman_id`)

| peminjaman_id (PK) | nim | nama_mhs | prodi | angkatan | no_hp | tgl_pinjam | tgl_jatuh_tempo |
|---|---|---|---|---|---|---|---|
| PJ001 | D121241001 | Rian | Informatika | 2024 | 081234567890 | 2025-10-01 | 2025-10-08 |
| PJ002 | D121241002 | Akbar | Teknik Elektro | 2023 | 085298765432 | 2025-10-02 | 2025-10-09 |
| PJ003 | D121241001 | Rian | Informatika | 2024 | 081234567890 | 2025-10-15 | 2025-10-22 |

**Tabel `buku`** (PK: `buku_id`)

| buku_id (PK) | isbn | judul | pengarang | tahun_terbit | stok | penerbit_id | nama_penerbit | alamat_penerbit |
|---|---|---|---|---|---|---|---|---|
| B001 | 978-602-11-0001-1 | Pemrograman Web dengan PHP | Andi Wijaya | 2021 | 5 | P01 | Nusantara Press | Makassar |
| B002 | 978-602-11-0002-8 | Basis Data Relasional | Sari Dewi | 2020 | 3 | P02 | Cahaya Ilmu | Jakarta |
| B003 | 978-602-11-0003-5 | Algoritma dan Struktur Data | Budi Santoso | 2019 | 4 | P01 | Nusantara Press | Makassar |

**Tabel `detail_peminjaman`** (PK komposit: `peminjaman_id` + `buku_id`)

| peminjaman_id (PK-1) | buku_id (PK-2) | tgl_kembali | denda |
|---|---|---|---|
| PJ001 | B001 | 2025-10-07 | 0 |
| PJ001 | B002 | 2025-10-10 | 2000 |
| PJ002 | B001 | 2025-10-09 | 0 |
| PJ002 | B003 | NULL | 0 |
| PJ003 | B003 | NULL | 0 |

**Hasil pemeriksaan:**

- Pada `peminjaman` dan `buku`, kunci utamanya tunggal, sehingga ketergantungan parsial
  tidak mungkin terjadi.
- Pada `detail_peminjaman`, `tgl_kembali` dan `denda` hanya dapat ditentukan oleh
  pasangan transaksi dan buku, jadi bergantung penuh pada kunci komposit.
- Data buku B001 dan B003 kini tercatat satu kali, tidak lagi diulang setiap dipinjam.
- Tabel memenuhi 2NF.

**Masalah yang masih tersisa (ketergantungan transitif):**

1. Pada `peminjaman`, kolom `nama_mhs`, `prodi`, `angkatan`, dan `no_hp` bergantung pada
   `nim`, padahal `nim` bukan kunci utama (`peminjaman_id → nim → nama_mhs`).
   Akibatnya data Rian masih tertulis dua kali (PJ001 dan PJ003).
2. Pada `buku`, kolom `nama_penerbit` dan `alamat_penerbit` bergantung pada `penerbit_id`,
   padahal `penerbit_id` bukan kunci utama (`buku_id → penerbit_id → nama_penerbit`).
   Akibatnya data Nusantara Press masih tertulis dua kali (B001 dan B003).
