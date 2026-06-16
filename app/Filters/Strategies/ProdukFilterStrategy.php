<?php

namespace app\Filters\Strategies;

class ProdukFilterStrategy extends BaseDatabaseStrategy
{
    public function getTableName(): string
    {
        return 'produk';
    }
    public function formatLabel($item): string
    {
        return "{$item->nama}";
    }
}
