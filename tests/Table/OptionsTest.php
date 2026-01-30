<?php

namespace RealZone22\PenguTables\Tests\Table;

use PHPUnit\Framework\Attributes\Test;
use RealZone22\PenguTables\Table\ExportTypes;
use RealZone22\PenguTables\Table\Options;
use RealZone22\PenguTables\Tests\TestCase;

class OptionsTest extends TestCase
{
    #[Test]
    public function it_can_create_default_options()
    {
        $options = Options::make();

        $this->assertEquals(10, $options->perPage);
        $this->assertTrue($options->withExport);
        $this->assertTrue($options->bulkActions);
        $this->assertTrue($options->searchable);
        $this->assertTrue($options->showItemsPerPage);
        $this->assertTrue($options->loading);
        $this->assertEquals('id', $options->primaryKey);
        $this->assertEquals([10, 25, 50, 100], $options->perPageOptions);
        $this->assertEquals(2, $options->paginationPages);
    }

    #[Test]
    public function it_can_set_per_page()
    {
        $options = Options::make()->setPerPage(25);

        $this->assertEquals(25, $options->perPage);
    }

    #[Test]
    public function it_can_set_pagination_pages()
    {
        $options = Options::make()->setPaginationPages(5);

        $this->assertEquals(5, $options->paginationPages);
    }

    #[Test]
    public function it_can_set_per_page_options()
    {
        $options = Options::make()->setPerPageOptions([15, 30, 60]);

        $this->assertEquals([15, 30, 60], $options->perPageOptions);
    }

    #[Test]
    public function it_can_set_primary_key()
    {
        $options = Options::make()->setPrimaryKey('uuid');

        $this->assertEquals('uuid', $options->primaryKey);
    }

    #[Test]
    public function it_can_disable_export()
    {
        $options = Options::make()->withExport(false);

        $this->assertFalse($options->withExport);
        $this->assertEmpty($options->exportTypes);
    }

    #[Test]
    public function it_can_set_specific_export_types()
    {
        $options = Options::make()->withExport(true, ExportTypes::CSV_ALL, ExportTypes::XLSX_ALL);

        $this->assertTrue($options->withExport);
        $this->assertCount(2, $options->exportTypes);
        $this->assertContains(ExportTypes::CSV_ALL, $options->exportTypes);
        $this->assertContains(ExportTypes::XLSX_ALL, $options->exportTypes);
    }

    #[Test]
    public function it_can_disable_bulk_actions()
    {
        $options = Options::make()->withBulkActions(false);

        $this->assertFalse($options->bulkActions);
    }

    #[Test]
    public function it_can_disable_search()
    {
        $options = Options::make()->withSearch(false);

        $this->assertFalse($options->searchable);
    }

    #[Test]
    public function it_can_disable_show_items_per_page()
    {
        $options = Options::make()->withShowItemsPerPage(false);

        $this->assertFalse($options->showItemsPerPage);
    }

    #[Test]
    public function it_can_disable_loading_indicator()
    {
        $options = Options::make()->withLoading(false);

        $this->assertFalse($options->loading);
    }
}
