<?php

namespace app\Filters\Contracts;

interface FilterStrategy
{
    public function getData($db, $currentIsi): array;
}
