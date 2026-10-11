<?php
declare(strict_types=1);

class GuestBook
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Menyimpan satu pesan ke tabel buku_tamu.
     * Memakai prepared statement: nilai dikirim terpisah dari perintah SQL.
     */
    public function simpan(string $nama, string $email, string $pesan): bool
    {
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