<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SidebarItem extends Component
{
    public function __construct(
        public string $route,
        public string $icon,
        public string $label,
        public ?int $badge = null,
    ) {}

    public function render()
    {
        return view('components.sidebar-item', [
            'active' => request()->routeIs($this->route),
        ]);
    }
}