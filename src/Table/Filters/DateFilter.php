<?php

namespace RealZone22\PenguTables\Table\Filters;

use RealZone22\PenguTables\Table\Filter;

class DateFilter extends Filter
{
    public function __construct()
    {
        $this->type = 'date';
    }
}
