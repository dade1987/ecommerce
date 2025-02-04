<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Cookie;
use Livewire\Component;
use Spatie\Newsletter\Facades\Newsletter as Nl;

class Newsletter extends Component
{
    public $email;

    public $isModalVisible = true; // Aggiunto per gestire la visibilità del modal

    public function render()
    {
        if (! request()->cookie('newsletter_visited')) {
            return view('livewire.newsletter');
        }

        return '<div></div>';
    }

    public function setNewsletterVisited()
    {
        Cookie::queue('newsletter_visited', true, 60 * 24 * 30); // Imposta il cookie per 30 giorni
    }

    public function save()
    {
        if ($this->email) {
            Nl::subscribe($this->email);
        }
        $this->close();
    }

    public function close()
    {
        $this->setNewsletterVisited();
        $this->isModalVisible = false; // Imposta isModalVisible a false
    }
}
