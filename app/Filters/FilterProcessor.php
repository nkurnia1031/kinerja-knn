<?php

namespace app\Filters;

class FilterProcessor
{
    private $db;
    private $strategies = [];

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function addStrategy($key, $strategy)
    {
        $this->strategies[$key] = $strategy;
        return $this;
    }

    public function process($filter)
    {
        foreach ($this->strategies as $key => $strategy) {
            if (isset($filter[$key])) {
                $filter[$key]->isi = $strategy->getData($this->db, $filter[$key]->isi);
            }
        }
        return $filter;
    }
}
