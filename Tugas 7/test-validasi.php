<?php
declare(strict_types=1);
require __DIR__ . '/Database.php';
require __DIR__ . '/src/GuestBook.php';

$pdo = (new Database(require __DIR__ . '/config.php'))->getConnection();
$gb  = new GuestBook($pdo);

$kasus = [
    'Semua valid'        => ['Budi', 'budi@contoh.com', 'Perpustakaannya nyaman.'],
    'Nama kosong'        => ['', 'budi@contoh.com', 'Perpustakaannya nyaman.'],
    'Nama hanya spasi'   => ['   ', 'budi@contoh.com', 'Perpustakaannya nyaman.'],
    'Email tanpa @'      => ['Budi', 'budicontoh.com', 'Perpustakaannya nyaman.'],
    'Email tanpa domain' => ['Budi', 'budi@', 'Perpustakaannya nyaman.'],
    'Pesan 4 karakter'   => ['Budi', 'budi@contoh.com', 'abcd'],
    'Pesan 5 karakter'   => ['Budi', 'budi@contoh.com', 'abcde'],
    'Pesan spasi saja'   => ['Budi', 'budi@contoh.com', '       '],
];

foreach ($kasus as $label => [$n, $e, $p]) {
    $galat = $gb->validasi($n, $e, $p);
    $hasil = $galat === [] ? 'VALID' : 'DITOLAK: ' . implode(' ', $galat);
    echo htmlspecialchars("$label → $hasil") . '<br>';
}