<?php

namespace app\Filters\Strategies;

class PeriodeFilterStrategy extends BaseDatabaseStrategy
{
    public function getTableName(): string
    {
        return 'periode_penilaian';
    }
    public function formatLabel($item): string
    {
        return "{$item->nama_periode} ";
    }
}
