<?php

return [

    'admin' => [
        'title' => 'ADMIN PANEL',
        'menus' => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['label' => 'Kelola Data Kriteria', 'route' => 'admin.kriteria.index'],
            ['label' => 'Kelola Data Altenativ', 'route' => 'admin.alternatif.index'],
            ['label' => 'AHP Bobot', 'route' => 'admin.ahp.matrix'],
            //['label' => 'Threshold Klasifikasi', 'route' => 'admin.threshold.index'],
            //['label' => 'Klasifikasi FAO', 'route' => 'admin.klasifikasi.hitung'],
            ['label' => 'Hasil VIKOR', 'route' => 'admin.vikor.hitung'],
            ['label' => 'Laporan', 'route' => 'admin.laporan.index'],
            //['label' => 'Pengguna', 'route' => 'admin.users.index'],
        ]
    ],

    'dinas' => [
        'title' => 'DINAS',
        'menus' => [
            ['label' => 'Dashboard', 'route' => 'dinas.dashboard'],
            ['label' => 'Evaluasi', 'route' => 'dinas.evaluasi.index'],
            ['label' => 'Rekomendasi', 'route' => 'dinas.rekomendasi.index'],
            ['label' => 'Peta', 'route' => 'dinas.peta.index'],
        ]
    ],

    'penyuluh' => [
        'title' => 'PENYULUH',
        'menus' => [
            ['label' => 'Dashboard', 'route' => 'penyuluh.dashboard'],
            ['label' => 'Evaluasi', 'route' => 'penyuluh.evaluasi.index'],
            ['label' => 'Rekomendasi', 'route' => 'penyuluh.rekomendasi.index'],
            ['label' => 'Peta', 'route' => 'penyuluh.peta.index'],
        ]
    ],

];
