<?php
session_start();
require_once 'Transaction.php'; 

if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 0.0;
}
if (!isset($_SESSION['history'])) {
    $_SESSION['history'] = [];
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Akses ditolak: Validasi CSRF gagal!');
    }

    $amountInput = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $typeInput = $_POST['type'] ?? '';

    if ($amountInput === false || $amountInput <= 0) {
        $pesan = "<div style='color: red;'>Format salah: Masukkan angka desimal positif.</div>";
    } else {
        $idTransaksi = uniqid('TRX-');
        $transaksi = new Transaction($idTransaksi, $typeInput, $amountInput);
        
        $hasil = $transaksi->process($_SESSION['balance']);
        
        if ($hasil === true) {
            $pesan = "<div style='color: green;'>Transaksi $typeInput berhasil!</div>";
            $_SESSION['history'][] = $transaksi;
        } else {
            $pesan = "<div style='color: red;'>$hasil</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Manajemen Keuangan</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 2rem auto; line-height: 1.6; }
        .card { border: 1px solid #ccc; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; }
        input, select, button { padding: 0.5rem; margin-top: 0.5rem; width: 100%; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.5rem; border: 1px solid #ccc; text-align: left; }
    </style>
</head>
<body>
    <h2>Sistem Manajemen Keuangan</h2>
    
    <div class="card">
        <h3>Saldo Saat Ini: Rp <?= htmlspecialchars(number_format($_SESSION['balance'], 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?></h3>
        <?= $pesan ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            
            <label for="type">Jenis Transaksi:</label>
            <select name="type" id="type" required>
                <option value="deposit">Deposit</option>
                <option value="penarikan">Penarikan</option>
            </select>

            <label for="amount">Jumlah (Rp):</label>
            <input type="number" step="0.01" min="0.01" name="amount" id="amount" placeholder="Contoh: 50000.50" required>

            <button type="submit">Proses Transaksi</button>
        </form>
    </div>

    <div class="card">
        <h3>Riwayat Transaksi</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Jenis</th>
                <th>Jumlah</th>
            </tr>
            <?php if (empty($_SESSION['history'])): ?>
                <tr><td colspan="3">Belum ada transaksi.</td></tr>
            <?php else: ?>
                <?php foreach (array_reverse($_SESSION['history']) as $trx): ?>
                <tr>
                    <td><?= htmlspecialchars($trx->getId(), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(ucfirst($trx->getType()), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>Rp <?= htmlspecialchars(number_format($trx->getAmount(), 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>