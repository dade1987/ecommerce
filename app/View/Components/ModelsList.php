<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

class ModelsList extends Component
{
    public Collection $rows;
    /**
     * Create a new component instance.
     */
    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.models-list', ['rows' => $this->rows]);
    }
}
