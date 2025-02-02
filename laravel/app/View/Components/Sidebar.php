<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Sidebar extends Component
{
    public function __construct()
    {
    }

    public function render()
    {
        return view('components.sidebar');
    }
} 