<?php

namespace RealZone22\PenguTables\Table\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use RealZone22\PenguTables\Table\Filter;

class MultiSelectFilter extends Filter
{
    public array $options = [];

    public function __construct()
    {
        $this->type = 'multi-select';
        $this->value = [];
    }

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function apply(Builder $builder, mixed $value): void
    {
        if ($this->callback) {
            call_user_func($this->callback, $builder, $value);
        } else {
            $this->defaultFilter($builder, $value);
        }
    }

    public function defaultFilter(Builder $query, $value): void
    {
        if (!empty($value) && is_array($value)) {
            $query->whereIn($this->key, $value);
        }
    }
}
