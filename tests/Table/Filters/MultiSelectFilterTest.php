<?php

namespace RealZone22\PenguTables\Tests\Table\Filters;

use PHPUnit\Framework\Attributes\Test;
use RealZone22\PenguTables\Table\Filters\MultiSelectFilter;
use RealZone22\PenguTables\Tests\TestCase;

class MultiSelectFilterTest extends TestCase
{
    #[Test]
    public function it_can_create_a_multi_select_filter()
    {
        $filter = MultiSelectFilter::make('status');

        $this->assertEquals('status', $filter->key);
        $this->assertEquals('status', $filter->label);
        $this->assertEquals('multi-select', $filter->type);
        $this->assertEquals([], $filter->value);
    }

    #[Test]
    public function it_can_set_options()
    {
        $filter = MultiSelectFilter::make('status')
            ->options([
                'active' => 'Active',
                'inactive' => 'Inactive',
                'pending' => 'Pending',
            ]);

        $this->assertCount(3, $filter->options);
        $this->assertEquals('Active', $filter->options['active']);
        $this->assertEquals('Inactive', $filter->options['inactive']);
        $this->assertEquals('Pending', $filter->options['pending']);
    }

    #[Test]
    public function it_can_set_custom_label()
    {
        $filter = MultiSelectFilter::make('status')
            ->label('Status Filter');

        $this->assertEquals('Status Filter', $filter->label);
    }

    #[Test]
    public function it_can_set_custom_filter_callback()
    {
        $filter = MultiSelectFilter::make('status')
            ->filter(function ($query, $value) {
                $query->whereIn('custom_status', $value);
            });

        $reflection = new \ReflectionClass($filter);
        $property = $reflection->getProperty('callback');
        $property->setAccessible(true);
        $callback = $property->getValue($filter);

        $this->assertNotNull($callback);
    }

    #[Test]
    public function it_does_not_include_all_option_by_default()
    {
        $filter = MultiSelectFilter::make('status')
            ->options([
                'active' => 'Active',
                'inactive' => 'Inactive',
            ]);

        // MultiSelect should NOT have an "All" option like SelectFilter
        $this->assertArrayNotHasKey('', $filter->options);
    }
}
