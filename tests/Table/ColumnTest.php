<?php

namespace RealZone22\PenguTables\Tests\Table;

use RealZone22\PenguTables\Table\Action;
use RealZone22\PenguTables\Table\Column;
use RealZone22\PenguTables\Tests\TestCase;

class ColumnTest extends TestCase
{
    /** @test */
    public function it_can_create_a_basic_column()
    {
        $column = Column::make('Name', 'name');

        $this->assertEquals('Name', $column->label);
        $this->assertEquals('name', $column->key);
        $this->assertFalse($column->sortable);
        $this->assertFalse($column->searchable);
        $this->assertFalse($column->hidden);
        $this->assertTrue($column->hideInExport);
        $this->assertFalse($column->html);
    }

    /** @test */
    public function it_can_make_a_column_sortable()
    {
        $column = Column::make('Name', 'name')->sortable();

        $this->assertTrue($column->sortable);
    }

    /** @test */
    public function it_can_make_a_column_searchable()
    {
        $column = Column::make('Name', 'name')->searchable();

        $this->assertTrue($column->searchable);
    }

    /** @test */
    public function it_can_format_a_column_value()
    {
        $column = Column::make('Name', 'name')->format(function ($value) {
            return strtoupper($value);
        });

        // Create a simple object to test with
        $model = new class
        {
            public $name = 'john doe';
        };

        $this->assertEquals('JOHN DOE', $column->getValue($model));
    }

    /** @test */
    public function it_escapes_html_by_default()
    {
        $column = Column::make('Name', 'name');

        // Create a simple object with HTML content
        $model = new class
        {
            public $name = '<script>alert("xss")</script>';
        };

        $this->assertEquals('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $column->getValue($model));
    }

    /** @test */
    public function it_does_not_escape_html_when_marked_as_html()
    {
        $column = Column::make('Name', 'name')->html();

        // Create a simple object with HTML content
        $model = new class
        {
            public $name = '<strong>John Doe</strong>';
        };

        $this->assertEquals('<strong>John Doe</strong>', $column->getValue($model));
    }

    /** @test */
    public function it_can_create_an_actions_column()
    {
        $column = Column::actions('Actions', function ($row) {
            return [
                Action::make('<button>Edit</button>'),
            ];
        });

        $this->assertEquals('Actions', $column->label);
        $this->assertNull($column->key);
        $this->assertTrue($column->html);
        $this->assertFalse($column->hideInExport); // Actions should be visible in export by default
    }

    /** @test */
    public function it_can_hide_a_column()
    {
        $column = Column::make('Name', 'name')->hidden();

        $this->assertTrue($column->hidden);
    }

    /** @test */
    public function it_can_conditionally_hide_a_column()
    {
        $column = Column::make('Name', 'name')->hideIf(function () {
            return true;
        });

        $this->assertTrue($column->hidden);
    }

    /** @test */
    public function it_can_control_visibility_in_export()
    {
        $column = Column::make('Name', 'name')->hideInExport(false);

        $this->assertTrue($column->hideInExport); // Note: hideInExport(false) means show in export

        $column2 = Column::make('Name', 'name')->hideInExport(true);

        $this->assertFalse($column2->hideInExport); // Note: hideInExport(true) means hide in export
    }

    /** @test */
    public function it_implements_wireable_interface()
    {
        $column = Column::make('Name', 'name')->sortable()->searchable();

        $serialized = $column->toLivewire();
        $deserialized = Column::fromLivewire($serialized);

        $this->assertEquals($column->label, $deserialized->label);
        $this->assertEquals($column->key, $deserialized->key);
        $this->assertEquals($column->sortable, $deserialized->sortable);
        $this->assertEquals($column->searchable, $deserialized->searchable);
    }
}
