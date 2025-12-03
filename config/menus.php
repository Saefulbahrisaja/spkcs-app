<?php

return [

    'admin' => [
        'title' => 'ADMIN PANEL',
        'menus' => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['label' => 'Kelola Kriteria', 'route' => 'admin.kriteria.index'],
            ['label' => 'Pairwise AHP', 'route' => 'admin.ahp.matrix'],
            ['label' => 'Kelola Alternatif', 'route' => 'admin.wilayah.index'],
            
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
