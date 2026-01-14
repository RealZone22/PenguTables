<?php

namespace RealZone22\PenguTables\Tests\Table;

use RealZone22\PenguTables\Table\Action;
use RealZone22\PenguTables\Tests\TestCase;

class ActionTest extends TestCase
{
    /** @test */
    public function it_can_create_an_action()
    {
        $action = Action::make('<button>Click Me</button>');

        $this->assertInstanceOf(Action::class, $action);
    }

    /** @test */
    public function it_stores_the_action_content()
    {
        $action = Action::make('<button>Click Me</button>');

        // Check that the content is stored in the toLivewire array
        $livewireData = $action->toLivewire();
        $this->assertEquals('<button>Click Me</button>', $livewireData['action']);
    }

    /** @test */
    public function it_implements_wireable_interface()
    {
        $action = Action::make('<button>Click Me</button>');

        $serialized = $action->toLivewire();
        $deserialized = Action::fromLivewire($serialized);

        $this->assertEquals($action->toLivewire(), $deserialized->toLivewire());
    }
}
