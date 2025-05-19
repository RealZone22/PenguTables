<?php

namespace RealZone22\PenguTables\Table;

class Options
{
    public int $perPage = 10;

    public bool $withExport = true;

    public array $exportTypes = [];

    public bool $bulkActions = true;

    public bool $searchable = true;

    public bool $showItemsPerPage = true;

    public bool $loading = true;

    public array $perPageOptions = [10, 25, 50, 100];

    public int $paginationPages = 2;

    public static function make(): static
    {
        return new static;
    }

    public function setPerPage(int $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function setPaginationPages(int $pages): static
    {
        $this->paginationPages = $pages;

        return $this;
    }

    public function setPerPageOptions(array $options): static
    {
        $this->perPageOptions = $options;

        return $this;
    }

    public function withExport(bool $enabled = true, ExportTypes ...$types): static
    {
        $this->withExport = $enabled;
        $this->exportTypes = $enabled ? $types : [];

        return $this;
    }

    public function withBulkActions(bool $enabled = true): static
    {
        $this->bulkActions = $enabled;

        return $this;
    }

    public function withSearch(bool $enabled = true): static
    {
        $this->searchable = $enabled;

        return $this;
    }

    public function withShowItemsPerPage(bool $enabled = true): static
    {
        $this->showItemsPerPage = $enabled;

        return $this;
    }

    public function withLoading(bool $enabled = true): static
    {
        $this->loading = $enabled;

        return $this;
    }
}
