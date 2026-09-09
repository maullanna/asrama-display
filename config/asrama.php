<?php

return [

    /*
     | Jam batas CI (check-in). Setelah jam ini, mahasiswa aktif yang belum CI
     | dan tidak punya keterangan (sakit/izin/dinas) dianggap "abnormal".
     | Format 24 jam "HH:MM". Bisa diubah lewat .env: ASRAMA_CI_CUTOFF=21:00
     */
    'ci_cutoff' => env('ASRAMA_CI_CUTOFF', '21:00'),

];
