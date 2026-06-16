<?php

namespace app\Filters\Strategies;

class KaryawanFilterStrategy extends BaseDatabaseStrategy
{
    public function getTableName(): string
    {
        return 'karyawan';
    }
    public function formatLabel($item): string
    {
        return "{$item->nama} | {$item->jabatan}";
    }
}
