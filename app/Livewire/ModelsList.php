<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\Cart\Facades\Cart;
use Illuminate\Database\Eloquent\Collection;

class ModelsList extends Component
{
    public Collection $rows;
    public bool $second_button;
    /**
     * Create a new component instance.
     */
    public function mount(Collection $rows, ?bool $second_button = false)
    {
        $this->rows = $rows;
        $this->second_button = $second_button;
    }

    public function render()
    {
        return view('livewire.models-list');
    }
}
