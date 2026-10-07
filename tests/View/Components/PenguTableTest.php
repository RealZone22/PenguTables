<?php

namespace RealZone22\PenguTables\Tests\View\Components;

use Illuminate\Support\Facades\Blade;
use RealZone22\PenguTables\Tests\TestCase;
use RealZone22\PenguTables\View\Components\PenguTable;

class PenguTableTest extends TestCase
{
    public function test_pengutable_blade_component_is_registered(): void
    {
        $this->assertSame(PenguTable::class, Blade::getClassComponentAliases()['pengutable']);
    }
}
