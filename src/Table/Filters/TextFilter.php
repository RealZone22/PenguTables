<?php

namespace RealZone22\PenguTables\Table\Filters;

use Illuminate\Database\Eloquent\Builder;
use RealZone22\PenguTables\Table\Filter;

class TextFilter extends Filter
{
    public function __construct()
    {
        $this->type = 'text';
    }

    public function defaultFilter(Builder $query, $value): void
    {
        if (! empty($value)) {
            $query->where($this->key, 'like', '%'.$value.'%');
        }
    }
}
