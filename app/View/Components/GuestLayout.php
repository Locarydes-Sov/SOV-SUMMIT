<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * @param  string  $title  Page title shown in the browser tab, e.g. "Sign in".
     */
    public function __construct(public string $title = 'Sign in') {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
