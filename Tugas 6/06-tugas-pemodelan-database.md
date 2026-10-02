
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