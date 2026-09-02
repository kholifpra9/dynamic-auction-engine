<?php

return [
    'ikan-hias' => [
        'panjang_cm' => ['label' => 'Panjang (cm)', 'type' => 'number', 'rules' => 'required|numeric|min:0'],
        'usia_bulan' => ['label' => 'Usia (bulan)', 'type' => 'number', 'rules' => 'required|integer|min:0'],
        'jenis' => ['label' => 'Jenis', 'type' => 'text', 'rules' => 'required|string'],
        'warna' => ['label' => 'Warna Dominan', 'type' => 'text', 'rules' => 'nullable|string'],
    ],
    'burung' => [
        'usia_bulan' => ['label' => 'Usia (bulan)', 'type' => 'number', 'rules' => 'required|integer|min:0'],
        'jenis_kicau' => ['label' => 'Jenis Kicau', 'type' => 'text', 'rules' => 'required|string'],
        'jenis_kelamin' => ['label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['Jantan', 'Betina'], 'rules' => 'required|in:Jantan,Betina'],
        'sudah_gacor' => ['label' => 'Sudah Gacor?', 'type' => 'select', 'options' => ['Ya', 'Belum'], 'rules' => 'nullable|in:Ya,Belum'],
    ],
];