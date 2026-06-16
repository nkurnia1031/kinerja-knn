<?php

namespace app\Filters\Strategies;


class DeviceFilterStrategy extends BaseDatabaseStrategy
{
    public function getTableName(): string
    {
        return 'device';
    }

    public function formatLabel($item): string
    {
        // Menggabungkan Nama dan No HP sesuai keinginan Anda
        return "{$item->nama} {$item->merk} | {$item->barcode}";
    }
}
