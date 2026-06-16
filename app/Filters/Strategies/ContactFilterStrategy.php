<?php

namespace app\Filters\Strategies;

class ContactFilterStrategy extends BaseDatabaseStrategy
{
    public function getTableName(): string
    {
        return 'contact';
    }
    public function formatLabel($item): string
    {
        return "{$item->nama} | {$item->nohp}";
    }
}
