<?php

namespace app\Filters\Strategies;

use app\Filters\Contracts\FilterStrategy;
use app\Fungsi;

class JenisFilterStrategy implements FilterStrategy
{
    public function getData($db, $currentIsi): array
    {
        // Ambil dari Class Fungsi atau array statis
        return collect(Fungsi::$jenis)->map(fn($item, $key) => [
            'key'   => $key,
            'label' => $item['label'],
        ])->values()->all();
    }
}
