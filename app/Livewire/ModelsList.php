<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Database\Eloquent\Collection;

class ModelsList extends Component
{
    public Collection $rows;
    /**
     * Create a new component instance.
     */
    public function mount(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function render()
    {
        return view('livewire.models-list');
    }
}
