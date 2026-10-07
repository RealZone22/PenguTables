<?php

namespace RealZone22\PenguTables\Table\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use JetBrains\PhpStorm\Deprecated;
use RealZone22\PenguTables\Table\Filter;

#[Deprecated('Only works with the old PenguBlade multi-select')]
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
        if (! empty($value) && is_array($value)) {
            $query->whereIn($this->key, $value);
        }
    }
}
