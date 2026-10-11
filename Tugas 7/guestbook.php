<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/src/GuestBook.php';

// Cookie sesi tidak dapat dibaca JavaScript dan tidak dikirim dari situs lain
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// Header keamanan dasar
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");

/** Sanitasi keluaran: mengubah karakter khusus HTML menjadi teks biasa. */
function e(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Mengambil isian POST sebagai string. Nilai non-string (misal nama[]) dianggap kosong. */
function ambilInput(string $kunci): string
{
    $nilai = $_POST[$kunci] ?? '';
    return is_string($nilai) ? trim($nilai) : '';
}

// Token CSRF dibuat sekali per sesi
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pdo       = (new Database(require __DIR__ . '/config.php'))->getConnection();
$guestBook = new GuestBook($pdo);

$input     = ['nama' => '', 'email' => '', 'pesan' => ''];
$galat     = [];
$galatUmum = '';

// Pesan sukses dari pengalihan sebelumnya, ditampilkan sekali saja
$sukses = $_SESSION['flash_sukses'] ?? '';
unset($_SESSION['flash_sukses']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenKiriman = $_POST['csrf_token'] ?? '';
    $tokenValid   = is_string($tokenKiriman)
        && hash_equals($_SESSION['csrf_token'], $tokenKiriman);

    $input = [
        'nama'  => ambilInput('nama'),
        'email' => ambilInput('email'),
        'pesan' => ambilInput('pesan'),
    ];

    if (!$tokenValid) {
        http_response_code(403);
        $galatUmum = 'Permintaan tidak valid. Muat ulang halaman lalu coba lagi.';
    } else {
        $galat = $guestBook->validasi($input['nama'], $input['email'], $input['pesan']);

        if ($galat === []) {
            try {
                $guestBook->simpan($input['nama'], $input['email'], $input['pesan']);

                // Token diganti setelah dipakai, lalu alihkan (Post/Redirect/Get)
                $_SESSION['csrf_token']   = bin2hex(random_bytes(32));
                $_SESSION['flash_sukses'] = 'Pesan berhasil dikirim. Terima kasih!';
                header('Location: guestbook.php');
                exit;
            } catch (PDOException $ex) {
                error_log('GuestBook simpan gagal: ' . $ex->getMessage());
                $galatUmum = 'Pesan gagal disimpan. Silakan coba lagi nanti.';
            }
        }
    }
}

$daftarPesan = [];
try {
    $daftarPesan = $guestBook->ambilSemua();
} catch (PDOException $ex) {
    error_log('GuestBook ambil gagal: ' . $ex->getMessage());
    $galatUmum = $galatUmum ?: 'Daftar pesan tidak dapat dimuat saat ini.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buku Tamu Perpustakaan</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 760px; margin: 2rem auto; padding: 0 1rem; color: #222; }
        h1 { margin-bottom: .25rem; }
        form { background: #f5f7fa; padding: 1rem 1.25rem; border-radius: 8px; margin-bottom: 2rem; }
        label { display: block; margin-top: .75rem; font-weight: bold; }
        input, textarea { width: 100%; padding: .5rem; margin-top: .25rem; box-sizing: border-box; font: inherit; }
        button { margin-top: 1rem; padding: .6rem 1.4rem; font: inherit; cursor: pointer; }
        .galat { color: #b00020; font-size: .9rem; margin-top: .25rem; }
        .sukses { background: #e6f4ea; color: #1e6b34; padding: .75rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .galat-umum { background: #fdecea; color: #b00020; padding: .75rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: .5rem .6rem; text-align: left; vertical-align: top; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h1>Buku Tamu Perpustakaan</h1>
    <p>Tinggalkan kesan dan saran Anda.</p>

    <?php if ($sukses !== ''): ?>
        <div class="sukses"><?= e($sukses) ?></div>
    <?php endif; ?>

    <?php if ($galatUmum !== ''): ?>
        <div class="galat-umum"><?= e($galatUmum) ?></div>
    <?php endif; ?>

    <form method="post" action="guestbook.php">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

        <label for="nama">Nama</label>
        <input type="text" id="nama" name="nama" maxlength="100" value="<?= e($input['nama']) ?>">
        <?php if (isset($galat['nama'])): ?><div class="galat"><?= e($galat['nama']) ?></div><?php endif; ?>

        <label for="email">Email</label>
        <input type="text" id="email" name="email" maxlength="150" value="<?= e($input['email']) ?>">
        <?php if (isset($galat['email'])): ?><div class="galat"><?= e($galat['email']) ?></div><?php endif; ?>

        <label for="pesan">Pesan</label>
        <textarea id="pesan" name="pesan" rows="4" maxlength="1000"><?= e($input['pesan']) ?></textarea>
        <?php if (isset($galat['pesan'])): ?><div class="galat"><?= e($galat['pesan']) ?></div><?php endif; ?>

        <button type="submit">Kirim Pesan</button>
    </form>

    <h2>Daftar Pesan</h2>
    <?php if ($daftarPesan === []): ?>
        <p>Belum ada pesan. Jadilah yang pertama menulis.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Waktu</th><th>Nama</th><th>Email</th><th>Pesan</th></tr>
            </thead>
            <tbody>
            <?php foreach ($daftarPesan as $baris): ?>
                <tr>
                    <td><?= e($baris['tanggal_kirim']) ?></td>
                    <td><?= e($baris['nama']) ?></td>
                    <td><?= e($baris['email']) ?></td>
                    <td><?= nl2br(e($baris['pesan'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>