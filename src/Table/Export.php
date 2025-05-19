<?php

namespace RealZone22\PenguTables\Table;

use Livewire\Wireable;

class Export implements Wireable
{
    private ExportTypes $type;

    public function __construct(ExportTypes $type)
    {
        $this->type = $type;
    }

    public static function make(ExportTypes $type): static
    {
        return new static($type);
    }

    public function getType(): ExportTypes
    {
        return $this->type;
    }

    public function toLivewire()
    {
        return [
            'type' => $this->type,
        ];
    }

    public static function fromLivewire($value)
    {
        return new static($value['type']);
    }
}
