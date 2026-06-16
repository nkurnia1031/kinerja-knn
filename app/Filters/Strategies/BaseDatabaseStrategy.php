<?php

namespace app\Filters\Strategies;

use app\Filters\Contracts\FilterStrategy;

abstract class BaseDatabaseStrategy implements FilterStrategy
{
    abstract public function getTableName(): string;
    abstract public function formatLabel($item): string;

    public function getData($db, $currentIsi): array
    {
        $ids = collect($currentIsi)->pluck('key')->filter()->values()->all();
        if (empty($ids)) return [];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $db->safeQuery(
            "SELECT * FROM {$this->getTableName()} WHERE id IN ($placeholders) ORDER BY id ASC",
            $ids
        );

        return array_map(fn($item) => [
            'key'   => $item->id,
            'label' => $this->formatLabel($item),
        ], $rows);
    }
}
