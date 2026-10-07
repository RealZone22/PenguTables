<?php

namespace RealZone22\PenguTables\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use RealZone22\PenguTables\Livewire\PenguTable as PenguTableComponent;

class PenguTable extends Component
{
    public function __construct(public PenguTableComponent $configuration) {}

    public function render(): View
    {
        return view('pengutables::components.pengutable', $this->configuration->tableViewData());
    }
}
