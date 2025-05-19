<?php

namespace RealZone22\PenguTables\Table;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;

abstract class Filter
{
    public string $key;

    public string $label;

    public string $type;

    public mixed $value = null;

    protected ?Closure $callback = null;

    public static function make(string $key): static
    {
        $filter = new static;
        $filter->key = $key;
        $filter->label = $key;

        return $filter;
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function filter(Closure $callback): static
    {
        $this->callback = $callback;

        return $this;
    }

    public function apply(Builder $builder, mixed $value): void
    {
        if ($this->callback) {
            call_user_func($this->callback, $builder, $value);
        }
    }
}
