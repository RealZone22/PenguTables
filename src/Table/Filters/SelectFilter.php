<?php

namespace RealZone22\PenguTables\Table\Filters;

use Illuminate\Database\Eloquent\Builder;
use RealZone22\PenguTables\Table\Filter;

class SelectFilter extends Filter
{
    public array $options = [];

    public function __construct()
    {
        $this->type = 'select';
    }

    public function options(array $options): static
    {
        $this->options = array_merge(['' => __('pengutables::tables.all')], $options);

        return $this;
    }

    public function defaultFilter(Builder $query, $value): void
    {
        if (!empty($value)) {
            $query->where($this->key, $value);
        }
    }
}
