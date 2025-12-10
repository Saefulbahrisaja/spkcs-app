<?php

return [

    'admin' => [
        'title' => 'ADMIN PANEL',
        'menus' => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['label' => 'Kelola Alternatif', 'route' => 'admin.wilayah.index'],
            ['label' => 'Kelola Kriteria', 'route' => 'admin.ahp.experts'],
            ['label' => 'Ringkasan luas', 'route' => 'admin.ringkasan.chart'],
            ['label' => 'Setting Batas Kelas', 'route' => 'admin.batas.index'],
            ['label' => 'Laporan', 'route' => 'admin.laporan.index'],
            //['label' => 'Pengguna', 'route' => 'admin.users.index'],
        ]
    ],

    'dinas' => [
        'title' => 'DINAS',
        'menus' => [
            ['label' => 'Dashboard', 'route' => 'dinas.dashboard'],
            ['label' => 'Validasi Evaluasi', 'route' => 'dinas.evaluasi.index'],
            ['label' => 'Input Rekomendasi', 'route' => 'dinas.rekomendasi.index'],
            ['label' => 'Hasil', 'route' => 'dinas.rekomendasi.index'],
        ]
    ],

    'penyuluh' => [
        'title' => 'PENYULUH',
        'menus' => [
            ['label' => 'Dashboard', 'route' => 'penyuluh.dashboard'],
        ]
    ],

];
