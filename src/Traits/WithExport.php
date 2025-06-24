<?php

namespace RealZone22\PenguTables\Traits;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

trait WithExport
{
    public function exportSelected($type)
    {
        $primaryKey = $this->options->primaryKey ?? 'id';

        return $this->export($this->query()->whereIn($primaryKey, $this->selected)->get(), $type);
    }

    public function exportAll($type)
    {
        return $this->export($this->query()->get(), $type);
    }

    protected function export($data, $type)
    {
        $filename = config('pengutables.export_filename', 'export_'.now()->format('Ymd_Hi'));
        $exportColumns = collect($this->columns())->filter(fn ($column) => $column->hideInExport)->values()->toArray();

        $headers = collect($exportColumns)->map(fn ($column) => $column->label)->toArray();
        $rows = $data->map(function ($item) use ($exportColumns) {
            return collect($exportColumns)->map(function ($column) use ($item) {
                $value = $column->getValue($item);

                return strip_tags($value);
            })->toArray();
        })->toArray();

        if (str_starts_with($type, 'csv')) {
            return $this->streamCsv($filename, $headers, $rows);
        }

        return $this->streamExcel($filename, $headers, $rows);
    }

    protected function streamCsv($filename, $headers, $rows)
    {
        $writer = new CsvWriter();

        return response()->stream(function () use ($writer, $headers, $rows) {
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues($headers));

            foreach ($rows as $rowData) {
                $writer->addRow(Row::fromValues($rowData));
            }

            $writer->close();
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    protected function streamExcel($filename, $headers, $rows)
    {
        $writer = new XlsxWriter();

        return response()->stream(function () use ($writer, $headers, $rows) {
            $writer->openToFile('php://output');

            $headerStyle = (new Style())->setFontBold();
            $headerRow = Row::fromValues($headers, $headerStyle);
            $writer->addRow($headerRow);

            foreach ($rows as $rowData) {
                $writer->addRow(Row::fromValues($rowData));
            }

            $writer->close();
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.xlsx"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }
}
