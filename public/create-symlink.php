<?php

$target = realpath(__DIR__.'/../../simahub_app/storage/app/public');
$link = __DIR__.'/storage';

if (! $target) {
    exit('Error: Folder target (simahub_app/storage/app/public) tidak ditemukan.');
}

if (file_exists($link)) {
    exit("Symlink atau folder 'storage' sudah ada di public_html/simahub. Hapus terlebih dahulu jika ingin membuat ulang.");
}

if (symlink($target, $link)) {
    echo 'BERHASIL! Symlink storage telah dibuat.<br>';
    echo "Target: $target <br>";
    echo "Link: $link";
} else {
    echo 'GAGAL membuat symlink. Pastikan fungsi symlink() tidak didisable oleh hosting.';
}
