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

    public array $deselected = [];

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

    public function updatedSearch(): void
    {
        $this->adjustPageToValidRange();
    }

    public function updatedActiveFilters(): void
    {
        $this->adjustPageToValidRange();
    }

    protected function adjustPageToValidRange(): void
    {
        $query = $this->query();
        $query = $this->applySearchToQuery($query);
        $query = $this->applyFilters($query);

        $totalItems = $query->count();
        $lastPage = max(1, (int) ceil($totalItems / $this->perPage));
        $currentPage = $this->getPage();

        if ($currentPage > $lastPage) {
            $this->setPage($lastPage);
        }
    }

    protected function applySearch(Builder $query): Builder
    {
        return $this->applySearchToQuery($query);
    }

    protected function applySearchToQuery(Builder $query): Builder
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
            if ($value !== null && $value !== '' && $value !== []) {
                $filter->apply($query, $value);
            }
        }

        return $query;
    }

    public function updatedSelectAll($value): void
    {
        $this->selected = [];
        $this->deselected = [];
    }

    public function toggleSelection(string $id): void
    {
        if ($this->selectAll) {
            if (in_array($id, $this->deselected, true)) {
                $this->deselected = array_values(array_diff($this->deselected, [$id]));
            } else {
                $this->deselected[] = $id;
            }

            return;
        }

        if (in_array($id, $this->selected, true)) {
            $this->selected = array_values(array_diff($this->selected, [$id]));
        } else {
            $this->selected[] = $id;
        }
    }

    protected function filteredQuery(): Builder
    {
        return $this->applyFilters($this->applySearch($this->query()));
    }

    protected function selectedQuery(): Builder
    {
        $query = $this->filteredQuery();
        $primaryKey = $this->options->primaryKey;

        if ($this->selectAll) {
            return empty($this->deselected)
                ? $query
                : $query->whereNotIn($primaryKey, $this->deselected);
        }

        return $query->whereIn($primaryKey, $this->selected);
    }

    public function selectedCount(): int
    {
        if (! $this->selectAll) {
            return count($this->selected);
        }

        return max(0, $this->filteredQuery()->count() - count($this->deselected));
    }

    public function executeBulkAction(string $actionLabel): void
    {
        $action = collect($this->bulkActions())->first(fn ($action) => $action->getLabel() === $actionLabel);
        if ($action && $this->selectedQuery()->exists()) {
            $rows = $this->selectedQuery()->get();
            $action->execute($rows);
            $this->selected = [];
            $this->deselected = [];
            $this->selectAll = false;
        }
    }

    public function updatedSelected(): void
    {
        $this->deselected = [];
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
            ->filter(fn ($column) => ! $column->hidden)
            ->values()
            ->toArray();
        $this->initializeFilters();
    }

    public function render(): View
    {
        return view(config('pengutables.table_view'), $this->tableViewData());
    }

    public function tableViewData(): array
    {
        $data = $this->data;
        $selectedCount = $this->selectAll
            ? max(0, $data->total() - count($this->deselected))
            : count($this->selected);

        return [
            'data' => $data,
            'columns' => $this->columns,
            'options' => $this->options,
            'headers' => $this->header(),
            'bulkActions' => $this->bulkActions(),
            'selected' => $this->selected,
            'deselected' => $this->deselected,
            'selectAll' => $this->selectAll,
            'selectedCount' => $selectedCount,
            'activeFilters' => $this->activeFilters,
            'search' => $this->search,
            'perPage' => $this->perPage,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'configuration' => $this,
        ];
    }
}
