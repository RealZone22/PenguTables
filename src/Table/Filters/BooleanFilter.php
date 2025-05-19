<?php

namespace RealZone22\PenguTables\Table\Filters;

use Illuminate\Database\Eloquent\Builder;
use RealZone22\PenguTables\Table\Filter;

class BooleanFilter extends Filter
{
    public function __construct()
    {
        $this->type = 'select';
        $this->options = [
            '' => __('pengutables::tables.all'),
            'true' => __('pengutables::tables.yes'),
            'false' => __('pengutables::tables.no'),
        ];

        $this->filter(function (Builder $builder, $value) {
            if ($value === 'true') {
                $builder->whereNotNull($this->key);
            } elseif ($value === 'false') {
                $builder->whereNull($this->key);
            }
        });
    }

    public function allLabel(string $label): static
    {
        $this->options[''] = $label;

        return $this;
    }

    public function trueLabel(string $label): static
    {
        $this->options['true'] = $label;

        return $this;
    }

    public function falseLabel(string $label): static
    {
        $this->options['false'] = $label;

        return $this;
    }
}
