<?php

namespace RealZone22\PenguTables\Table;

use Illuminate\Support\Facades\Blade;
use Livewire\Wireable;

class Action implements Wireable
{
    private string $action;

    public function __construct(string $action)
    {
        $this->action = $action;
    }

    public static function make(string $action): static
    {
        return new static($action);
    }

    public static function fromLivewire($value)
    {
        return new static($value['action']);
    }

    public function getAction(): string
    {
        return Blade::render($this->action);
    }

    public function toLivewire()
    {
        return [
            'action' => $this->action,
        ];
    }
}
