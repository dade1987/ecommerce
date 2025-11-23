<?php

namespace App\View\Components\Layouts;

use Illuminate\View\Component;
use Illuminate\View\View;

class App extends Component
{
    public function render(): View
    {
        return view('layouts.app');
    }
}
