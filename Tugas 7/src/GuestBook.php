<?php
declare(strict_types=1);

class GuestBook
{
    private const NAMA_MAKS  = 100;
    private const EMAIL_MAKS = 150;
    private const PESAN_MIN  = 5;
    private const PESAN_MAKS = 1000;

    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Memeriksa masukan. Mengembalikan array galat per kolom.
     * Array kosong berarti semua masukan valid.
     */
    public function validasi(string $nama, string $email, string $pesan): array
    {
        $galat  = [];
        $nama   = trim($nama);
        $email  = trim($email);
        $pesan  = trim($pesan);

        if ($nama === '') {
            $galat['nama'] = 'Nama tidak boleh kosong.';
        } elseif (mb_strlen($nama) > self::NAMA_MAKS) {
            $galat['nama'] = 'Nama maksimal ' . self::NAMA_MAKS . ' karakter.';
        }

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $galat['email'] = 'Format email tidak valid.';
        } elseif (mb_strlen($email) > self::EMAIL_MAKS) {
            $galat['email'] = 'Email maksimal ' . self::EMAIL_MAKS . ' karakter.';
        }

        if (mb_strlen($pesan) < self::PESAN_MIN) {
            $galat['pesan'] = 'Pesan minimal ' . self::PESAN_MIN . ' karakter.';
        } elseif (mb_strlen($pesan) > self::PESAN_MAKS) {
            $galat['pesan'] = 'Pesan maksimal ' . self::PESAN_MAKS . ' karakter.';
        }

        return $galat;
    }

    /**
     * Menyimpan satu pesan. Memvalidasi ulang sebagai lapisan pengaman,
     * dan melempar InvalidArgumentException bila masukan tidak valid.
     */
    public function simpan(string $nama, string $email, string $pesan): bool
    {
        $nama  = trim($nama);
        $email = trim($email);
        $pesan = trim($pesan);

        if ($this->validasi($nama, $email, $pesan) !== []) {
            throw new InvalidArgumentException('Masukan tidak valid.');
        }

        $sql = 'INSERT INTO buku_tamu (nama, email, pesan)
                VALUES (:nama, :email, :pesan)';

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'nama'  => $nama,
            'email' => $email,
            'pesan' => $pesan,
        ]);
    }

    /**
     * Mengambil pesan, yang terbaru tampil lebih dulu.
     */
    public function ambilSemua(int $batas = 50): array
    {
        $sql = 'SELECT id, nama, email, pesan, tanggal_kirim
                FROM buku_tamu
                ORDER BY tanggal_kirim DESC, id DESC
                LIMIT :batas';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':batas', $batas, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}