<?php

namespace RealZone22\PenguTables\Table;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Wireable;

class Column implements Wireable
{
    public string $label;

    public ?string $key;

    public bool $sortable = false;

    public bool $searchable = false;

    public bool $hidden = false;

    public bool $hideInExport = true;

    public bool $html = false;

    private ?string $formatSerialized = null;

    private ?Closure $format = null;

    private ?string $searchSerialized = null;

    private function __construct(string $label, ?string $key = null)
    {
        $this->label = $label;
        $this->key = $key;
    }

    public static function actions(string $label, Closure $callback): static
    {
        $instance = new static($label);
        $instance->format(function ($value, $row) use ($callback) {
            $actions = [];
            foreach ($callback($row) as $action) {
                if ($action instanceof Action) {
                    $actions[] = $action->getAction();
                }
            }

            return '<div class="flex flex-row gap-1">'.implode('', $actions).'</div>';
        });

        $instance->hideInExport();
        $instance->html();

        return $instance;
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

    public function searchUsing(Closure $callback): static
    {
        $this->searchSerialized = serialize(new SerializableClosure($callback));

        return $this;
    }

    public function hasCustomSearch(): bool
    {
        return $this->searchSerialized !== null;
    }

    public function applySearch(Builder $query, string $term): void
    {
        if ($this->searchSerialized) {
            $callback = unserialize($this->searchSerialized)->getClosure();
            $callback($query, $term);

            return;
        }

        if (! $this->key) {
            return;
        }

        $value = '%'.$term.'%';
        $segments = explode('.', $this->key);
        $column = array_pop($segments);

        if ($segments !== []) {
            $query->orWhereHas(implode('.', $segments), fn (Builder $relationQuery) => $relationQuery->where($column, 'LIKE', $value));
        } else {
            $query->orWhere($this->key, 'LIKE', $value);
        }
    }

    public function format(Closure $callback): static
    {
        $this->format = $callback;
        $this->formatSerialized = serialize(new SerializableClosure($callback));

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

    public function hideInExport(bool $enabled = true): static
    {
        $this->hideInExport = ! $enabled;

        return $this;
    }

    public function getValue($model): ?string
    {
        if ($this->formatSerialized) {
            $callback = unserialize($this->formatSerialized)->getClosure();
            $value = $callback(data_get($model, $this->key), $model);

            if (! $this->html) {
                $value = e($value);
            }

            return $value;
        }

        if (! $this->key) {
            return '';
        }

        $value = data_get($model, $this->key);

        if (! $this->html) {
            $value = e($value);
        }

        return $value;
    }

    public function toLivewire(): array
    {
        return [
            'label' => $this->label,
            'key' => $this->key,
            'sortable' => $this->sortable,
            'searchable' => $this->searchable,
            'hidden' => $this->hidden,
            'hideInExport' => $this->hideInExport,
            'html' => $this->html,
            'formatSerialized' => $this->formatSerialized,
            'searchSerialized' => $this->searchSerialized,
        ];
    }

    public static function fromLivewire($value): static
    {
        $instance = new static($value['label'], $value['key']);
        $instance->sortable = $value['sortable'];
        $instance->searchable = $value['searchable'];
        $instance->hidden = $value['hidden'];
        $instance->hideInExport = $value['hideInExport'] ?? true;
        $instance->html = $value['html'];
        $instance->formatSerialized = $value['formatSerialized'];
        $instance->searchSerialized = $value['searchSerialized'] ?? null;

        return $instance;
    }
}
