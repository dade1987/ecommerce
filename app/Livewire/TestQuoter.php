<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class TestQuoter extends Component
{
    use WithFileUploads;

    public $file;

    public function render()
    {
        return view('livewire.test-quoter');
    }

    public function updatedFile($file)
    {
        $path = $file->store('uploads', 'public');

        dd($path);

    }
}
