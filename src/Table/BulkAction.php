<?php

namespace RealZone22\PenguTables\Table;

use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Wireable;

class BulkAction implements Wireable
{
    private string $label;

    private string $actionSerialized;

    public function __construct(string $label, callable $action)
    {
        $this->label = $label;
        $this->actionSerialized = serialize(new SerializableClosure($action));
    }

    public static function make(string $label, callable $action): static
    {
        return new static($label, $action);
    }

    public static function fromLivewire($value)
    {
        $instance = new static($value['label'], function () {
        });
        $instance->actionSerialized = $value['actionSerialized'];

        return $instance;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function execute($rows)
    {
        $action = unserialize($this->actionSerialized)->getClosure();

        return $action($rows);
    }

    public function toLivewire()
    {
        return [
            'label' => $this->label,
            'actionSerialized' => $this->actionSerialized,
        ];
    }
}
