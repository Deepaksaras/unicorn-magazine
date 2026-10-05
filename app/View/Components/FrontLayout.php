<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class FrontLayout extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.front');
    }
}
