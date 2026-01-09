<?php

namespace RealZone22\PenguTables\Livewire;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use RealZone22\PenguTables\Table\Options;

abstract class PenguTable extends Component
{
    use SerializesAndRestoresModelIdentifiers, WithPagination;

    public array $columns = [];

    public array $selected = [];

    #[Url('table-search')]
    public string $search = '';

    #[Url('per-page')]
    public int $perPage = 10;

    #[Url('sort-field')]
    public string $sortField = '';

    #[Url('sort-direction')]
    public string $sortDirection = 'asc';

    public array $activeFilters = [];

    protected Options $options;

    public bool $selectAll = false;

    abstract public function query(): Builder;

    abstract public function columns(): array;

    public function header(): array
    {
        return [];
    }

    public function filters(): array
    {
        return [];
    }

    public function bulkActions(): array
    {
        return [];
    }

    protected function setupOptions(): Options
    {
        return Options::make();
    }

    protected function initializeFilters(): void
    {
        foreach ($this->filters() as $filter) {
            $this->activeFilters[$filter->key] = $filter->value;
        }
    }

    protected function applySearch(Builder $query): Builder
    {
        if ($this->options->searchable && $this->search) {
            $searchTerms = array_filter(explode(' ', strtolower(trim($this->search))));

            if (! empty($searchTerms)) {
                $query->where(function (Builder $subQuery) use ($searchTerms) {
                    $searchableColumns = collect($this->columns)
                        ->filter(fn ($column) => $column->searchable && $column->key)
                        ->pluck('key')
                        ->toArray();

                    foreach ($searchTerms as $term) {
                        $subQuery->where(function (Builder $termQuery) use ($term, $searchableColumns) {
                            foreach ($searchableColumns as $column) {
                                $termQuery->orWhere($column, 'LIKE', '%'.$term.'%');
                            }
                        });
                    }
                });
            }
        }

        return $query;
    }

    protected function applyFilters(Builder $query): Builder
    {
        foreach ($this->filters() as $filter) {
            $value = $this->activeFilters[$filter->key] ?? null;
            if ($value !== null && $value !== '') {
                $filter->apply($query, $value);
            }
        }

        return $query;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $query = $this->applyFilters($this->applySearch($this->query()));
            $this->selected = $query
                ->limit($this->perPage)
                ->pluck($this->options->primaryKey)
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function executeBulkAction(string $actionLabel): void
    {
        $action = collect($this->bulkActions())->first(fn ($action) => $action->getLabel() === $actionLabel);
        if ($action && ! empty($this->selected)) {
            $rows = $this->query()->whereIn($this->options->primaryKey, $this->selected)->get();
            $action->execute($rows);
            $this->selected = [];
            $this->selectAll = false;
        }
    }

    public function updatedSelected(): void
    {
        $this->selectAll = false;
    }

    public function resetFilters(): void
    {
        $this->activeFilters = [];
        foreach ($this->filters() as $filter) {
            $this->activeFilters[$filter->key] = $filter->value;
        }
    }

    public function removeFilter($key): void
    {
        if (isset($this->activeFilters[$key])) {
            unset($this->activeFilters[$key]);
        }
    }

    protected function applySort(Builder $query): Builder
    {
        if ($this->sortField) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query;
    }

    public function sort(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'desc' ? 'asc' : 'desc';
            if ($this->sortDirection === 'asc') {
                $this->sortField = '';
            }
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getDataProperty(): LengthAwarePaginator
    {
        $query = $this->query();
        $query = $this->applySearch($query);
        $query = $this->applyFilters($query);
        $query = $this->applySort($query);

        return $query->paginate($this->perPage);
    }

    public function boot(): void
    {
        $this->options = $this->setupOptions();
        if (! isset($this->perPage) || $this->perPage <= 0) {
            $this->perPage = $this->options->perPageOptions[0];
        }
    }

    public function mount(): void
    {
        $this->columns = collect($this->columns())
            ->filter(fn($column) => !$column->hidden)
            ->values()
            ->toArray();
        $this->initializeFilters();
    }

    public function render(): View
    {
        return view(config('pengutables.table_view'), [
            'data' => $this->data,
            'columns' => $this->columns,
            'options' => $this->options,
            'headers' => $this->header(),
            'bulkActions' => $this->bulkActions(),
        ]);
    }
}
