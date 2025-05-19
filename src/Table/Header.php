<?php

namespace RealZone22\PenguTables\Table;

use Livewire\Wireable;

class Header implements Wireable
{
    private string $label;

    public function __construct(string $label)
    {
        $this->label = $label;
    }

    public static function make(string $label): static
    {
        return new static($label);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function toLivewire()
    {
        return [
            'label' => $this->label,
        ];
    }

    public static function fromLivewire($value)
    {
        return new static($value['label']);
    }
}
