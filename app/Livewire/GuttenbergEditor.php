<?php

namespace App\Livewire;

use App\Models\GuttenbergPage;
use Livewire\Component;

class GuttenbergEditor extends Component
{

    public function save($value){
       GuttenbergPage::create(['content'=>$value]);
        dd('done');
    }
    
    public function render()
    {
        return view('livewire.guttenberg-editor');
    }
}
