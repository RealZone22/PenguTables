<?php

namespace RealZone22\PenguTables\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

trait WithExport
{
    public function exportSelected($type)
    {
        return $this->queueExport($type, true);
    }

    public function exportAll($type)
    {
        return $this->queueExport($type, false);
    }

    public function exportFromState(array $state, string $type)
    {
        $selectedExport = $state['selectedExport'] ?? false;
        unset($state['selectedExport']);

        foreach ($state as $property => $value) {
            $this->{$property} = $value;
        }

        $query = $selectedExport
            ? $this->selectedQuery()
            : $this->applyFilters($this->applySearch($this->query()));

        return $this->exportChunked($query, $type);
    }

    protected function queueExport(string $type, bool $selected)
    {
        abort_unless(in_array($type, ['csv_all', 'xlsx_all', 'csv_selected', 'xlsx_selected'], true), 404);

        $token = Str::random(64);
        Cache::put('pengutables.export.'.$token, [
            'component' => $this->getName() ?: static::class,
            'componentIsNamed' => $this->getName() !== null,
            'state' => [
                'search' => $this->search,
                'activeFilters' => $this->activeFilters,
                'selected' => $selected ? $this->selected : [],
                'deselected' => $selected ? $this->deselected : [],
                'selectAll' => $selected && $this->selectAll,
                'selectedExport' => $selected,
            ],
            'type' => $type,
        ], now()->addSeconds(config('pengutables.export_token_ttl', 60)));

        return redirect()->route(config('pengutables.export_route', 'pengutables.export'), $token);
    }

    protected function export($data, $type)
    {
        $filename = config('pengutables.export_filename', 'export_'.now()->format('Ymd_Hi'));
        $exportColumns = collect($this->columns())->filter(fn ($column) => $column->hideInExport)->values()->toArray();

        $headers = collect($exportColumns)->map(fn ($column) => $column->label)->toArray();
        $rows = $data->map(function ($item) use ($exportColumns) {
            return collect($exportColumns)->map(function ($column) use ($item) {
                $value = $column->getValue($item);

                return html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            })->toArray();
        })->toArray();

        if (str_starts_with($type, 'csv')) {
            return $this->streamCsv($filename, $headers, $rows);
        }

        return $this->streamExcel($filename, $headers, $rows);
    }

    protected function exportChunked($query, $type)
    {
        $filename = config('pengutables.export_filename', 'export_'.now()->format('Ymd_Hi'));
        $exportColumns = collect($this->columns())->filter(fn ($column) => $column->hideInExport)->values()->toArray();
        $headers = collect($exportColumns)->map(fn ($column) => $column->label)->toArray();

        if (str_starts_with($type, 'csv')) {
            return $this->streamChunkedCsv($filename, $headers, $query, $exportColumns);
        }

        return $this->streamChunkedExcel($filename, $headers, $query, $exportColumns);
    }

    protected function streamCsv($filename, $headers, $rows)
    {
        $writer = new CsvWriter;

        return response()->streamDownload(function () use ($writer, $headers, $rows) {
            $this->clearExportOutputBuffers();
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues($headers));

            foreach ($rows as $rowData) {
                $writer->addRow(Row::fromValues($rowData));
            }

            $writer->close();
        }, $filename.'.csv', [
            'Content-Type' => 'text/csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    protected function streamChunkedCsv($filename, $headers, $query, $exportColumns)
    {
        $writer = new CsvWriter;

        return response()->streamDownload(function () use ($writer, $headers, $query, $exportColumns) {
            $this->clearExportOutputBuffers();
            $writer->openToFile('php://output');
            $writer->addRow(Row::fromValues($headers));

            $query->chunkById(1000, function ($items) use ($writer, $exportColumns) {
                foreach ($items as $item) {
                    $rowData = [];
                    foreach ($exportColumns as $column) {
                        $rowData[] = strip_tags($column->getValue($item));
                    }

                    $writer->addRow(Row::fromValues($rowData));
                }
            }, $this->options->primaryKey);

            $writer->close();
        }, $filename.'.csv', [
            'Content-Type' => 'text/csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    protected function streamExcel($filename, $headers, $rows)
    {
        $writer = new XlsxWriter;

        return response()->streamDownload(function () use ($writer, $headers, $rows) {
            $this->clearExportOutputBuffers();
            $writer->openToFile('php://output');

            $headerRow = Row::fromValues($headers);
            $writer->addRow($headerRow);

            foreach ($rows as $rowData) {
                $writer->addRow(Row::fromValues($rowData));
            }

            $writer->close();
        }, $filename.'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    protected function streamChunkedExcel($filename, $headers, $query, $exportColumns)
    {
        $writer = new XlsxWriter;

        return response()->streamDownload(function () use ($writer, $headers, $query, $exportColumns) {
            $this->clearExportOutputBuffers();
            $writer->openToFile('php://output');

            $headerRow = Row::fromValues($headers);
            $writer->addRow($headerRow);

            $query->chunkById(1000, function ($items) use ($writer, $exportColumns) {
                foreach ($items as $item) {
                    $rowData = [];
                    foreach ($exportColumns as $column) {
                        $rowData[] = strip_tags($column->getValue($item));
                    }

                    $writer->addRow(Row::fromValues($rowData));
                }
            }, $this->options->primaryKey);

            $writer->close();
        }, $filename.'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    protected function clearExportOutputBuffers(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
}
