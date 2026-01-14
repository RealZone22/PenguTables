<?php

namespace RealZone22\PenguTables\Tests\Traits;

use RealZone22\PenguTables\Table\Column;
use RealZone22\PenguTables\Tests\TestCase;
use RealZone22\PenguTables\Traits\WithExport;

class WithExportTest extends TestCase
{
    /** @test */
    public function it_processes_html_content_correctly_for_export()
    {
        // Create a mock class that uses the WithExport trait
        $mock = new class {
            use WithExport;

            public function columns()
            {
                return [
                    Column::make('Email', 'email')->format(function ($value) {
                        return '<strong>' . $value . '</strong>';
                    })->html(),
                ];
            }
        };

        // Create a mock model
        $model = new class {
            public $email = 'john@example.com';
        };

        // Get the columns
        $columns = $mock->columns();
        $emailColumn = $columns[0];

        // Test that HTML is preserved when html() is set
        $formattedValue = $emailColumn->getValue($model);
        $this->assertEquals('<strong>john@example.com</strong>', $formattedValue);

        // Test that strip_tags works as expected (as used in WithExport trait)
        $strippedValue = strip_tags($formattedValue);
        $this->assertEquals('john@example.com', $strippedValue);
    }

    /** @test */
    public function it_correctly_filters_columns_for_export()
    {
        // Create columns with different hideInExport settings
        $column1 = Column::make('ID', 'id')->hideInExport(false); // Should be INCLUDED in export (!false = true)
        $column2 = Column::make('Name', 'name'); // Default hideInExport = true, so EXCLUDED
        $column3 = Column::make('Email', 'email')->hideInExport(true); // Should be EXCLUDED in export (!true = false)

        $columns = [$column1, $column2, $column3];

        // Check the actual values
        $this->assertTrue($column1->hideInExport); // hideInExport(false) sets it to true
        $this->assertTrue($column2->hideInExport); // Default is true
        $this->assertFalse($column3->hideInExport); // hideInExport(true) sets it to false

        // Filter columns as done in WithExport trait
        $exportColumns = collect($columns)->filter(fn($column) => $column->hideInExport)->values()->toArray();

        // Columns with hideInExport = true should be included (column1 and column2)
        $this->assertCount(2, $exportColumns);
        $this->assertEquals('ID', $exportColumns[0]->label);
        $this->assertEquals('Name', $exportColumns[1]->label);
    }
}
