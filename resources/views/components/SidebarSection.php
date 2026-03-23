<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SidebarSection extends Component
{
    public function __construct(public string $label) {}

    public function render()
    {
        return view('components.sidebar-section');
    }
}