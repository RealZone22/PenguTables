<?php

namespace RealZone22\PenguTables\Table;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Wireable;

class Column implements Wireable
{
    public string $label;

    public ?string $key;

    public bool $sortable = false;

    public bool $searchable = false;

    public bool $hidden = false;

    public bool $showInExport = true;

    public bool $html = false;

    private ?string $formatSerialized = null;

    private ?string $labelCallbackSerialized = null;

    private ?Closure $format = null;

    private string|null|Closure $labelCallback = null;

    private function __construct(string $label, ?string $key = null)
    {
        $this->label = $label;
        $this->key = $key;
    }

    public static function make(string $label, ?string $key = null): static
    {
        return new static($label, $key);
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function format(Closure $callback): static
    {
        $this->format = $callback;
        $this->formatSerialized = serialize(new SerializableClosure($callback));

        return $this;
    }

    public function label(Closure $callback): static
    {
        $this->labelCallback = $callback;
        $this->labelCallbackSerialized = serialize(new SerializableClosure($callback));

        return $this;
    }

    public function html(): static
    {
        $this->html = true;

        return $this;
    }

    public function hidden(bool $enabled = true): static
    {
        $this->hidden = $enabled;

        return $this;
    }

    public function hideIf(Closure $callback): static
    {
        $this->hidden = $callback();

        return $this;
    }

    public function showInExport(bool $enabled = true): static
    {
        $this->showInExport = $enabled;

        return $this;
    }

    public function getValue($model): string
    {
        if ($this->labelCallbackSerialized) {
            $callback = unserialize($this->labelCallbackSerialized)->getClosure();

            return $callback($model);
        }

        if (! $this->key) {
            return '';
        }

        $value = data_get($model, $this->key);

        if ($this->formatSerialized) {
            $callback = unserialize($this->formatSerialized)->getClosure();
            $value = $callback($value, $model);
        }

        if (! $this->html) {
            $value = e($value);
        }

        return $value;
    }

    public function getLabel($model = null): string
    {
        if ($this->labelCallbackSerialized && $model) {
            $callback = unserialize($this->labelCallbackSerialized)->getClosure();

            return $callback($model);
        }

        return $this->label;
    }

    public function toLivewire(): array
    {
        return [
            'label' => $this->label,
            'key' => $this->key,
            'sortable' => $this->sortable,
            'searchable' => $this->searchable,
            'hidden' => $this->hidden,
            'showInExport' => $this->showInExport,
            'html' => $this->html,
            'formatSerialized' => $this->formatSerialized,
            'labelCallbackSerialized' => $this->labelCallbackSerialized,
        ];
    }

    public static function fromLivewire($value): static
    {
        $instance = new static($value['label'], $value['key']);
        $instance->sortable = $value['sortable'];
        $instance->searchable = $value['searchable'];
        $instance->hidden = $value['hidden'];
        $instance->showInExport = $value['showInExport'] ?? true;
        $instance->html = $value['html'];
        $instance->formatSerialized = $value['formatSerialized'];
        $instance->labelCallbackSerialized = $value['labelCallbackSerialized'];

        return $instance;
    }
}
