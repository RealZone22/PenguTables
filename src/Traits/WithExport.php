<?php

namespace RealZone22\PenguTables\Traits;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

trait WithExport
{
    public function exportSelected($type)
    {
        return $this->export($this->query()->whereIn('id', $this->selected)->get(), $type);
    }

    public function exportAll($type)
    {
        return $this->export($this->query()->get(), $type);
    }

    protected function export($data, $type)
    {
        $filename = config('pengutables.export_filename', 'export_'.now()->format('Ymd_Hi'));
        $exportColumns = collect($this->columns())->filter(fn ($column) => $column->showInExport)->values()->toArray();

        $headers = collect($exportColumns)->map(fn ($column) => $column->label)->toArray();
        $rows = $data->map(function ($item) use ($exportColumns) {
            return collect($exportColumns)->map(function ($column) use ($item) {
                $value = $column->getValue($item);

                return strip_tags($value);
            })->toArray();
        })->toArray();

        if (str_starts_with($type, 'csv')) {
            return $this->streamCsv($filename, $headers, $rows);
        } else {
            return $this->streamExcel($filename, $headers, $rows);
        }
    }

    protected function streamCsv($filename, $headers, $rows)
    {
        $callback = function () use ($headers, $rows) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers);

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    protected function streamExcel($filename, $headers, $rows)
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headers as $colIndex => $header) {
            $sheet->setCellValue($colIndex + 1, 1, $header);
        }

        foreach ($rows as $rowIndex => $rowData) {
            foreach ($rowData as $colIndex => $cellValue) {
                $sheet->setCellValue($colIndex + 1, $rowIndex + 2, $cellValue);
            }
        }

        $headerRow = $sheet->getStyle('A1:'.Coordinate::stringFromColumnIndex(count($headers)).'1');
        $headerRow->getFont()->setBold(true);

        foreach (range(1, count($headers)) as $colIndex) {
            $sheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.xlsx"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }
}
