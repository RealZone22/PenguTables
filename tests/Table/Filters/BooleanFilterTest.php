<?php

namespace RealZone22\PenguTables\Tests\Table\Filters;

use RealZone22\PenguTables\Table\Filters\BooleanFilter;
use RealZone22\PenguTables\Tests\TestCase;

class BooleanFilterTest extends TestCase
{
    /** @test */
    public function it_can_create_a_boolean_filter()
    {
        $filter = BooleanFilter::make('active');

        $this->assertEquals('active', $filter->key);
        $this->assertEquals('active', $filter->label);
        $this->assertEquals('select', $filter->type);

        // Check default options
        $this->assertArrayHasKey('', $filter->options);
        $this->assertArrayHasKey('true', $filter->options);
        $this->assertArrayHasKey('false', $filter->options);
    }

    /** @test */
    public function it_can_set_custom_labels()
    {
        $filter = BooleanFilter::make('active')
            ->allLabel('All Items')
            ->trueLabel('Active')
            ->falseLabel('Inactive');

        $this->assertEquals('All Items', $filter->options['']);
        $this->assertEquals('Active', $filter->options['true']);
        $this->assertEquals('Inactive', $filter->options['false']);
    }

    /** @test */
    public function it_has_default_filter_callback()
    {
        $filter = BooleanFilter::make('verified');

        // Verify that a callback was set
        $reflection = new \ReflectionClass($filter);
        $property = $reflection->getProperty('callback');
        $property->setAccessible(true);
        $callback = $property->getValue($filter);

        $this->assertNotNull($callback);
        $this->assertInstanceOf(\Closure::class, $callback);
    }
}
