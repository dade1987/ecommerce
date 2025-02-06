<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Cookie;
use Livewire\Component;
use Spatie\Newsletter\Facades\Newsletter as Nl;

class Newsletter extends Component
{
    public $email = '';

    public $isModalVisible = true;

    public $isPrivacyModalVisible = false;

    public $isPrivacyChecked = false; // Aggiunta della variabile isPrivacyChecked

    public function render()
    {
        if (! request()->cookie('newsletter_visited')) {
            return view('livewire.newsletter');
        }

        return '<div></div>';
    }

    public function setNewsletterVisited()
    {
        Cookie::queue('newsletter_visited', true, 60 * 24 * 30);
    }

    public function save()
    {
        if ($this->email && $this->isPrivacyChecked) { // Controllo aggiunto per isPrivacyChecked
            Nl::subscribe($this->email);

            $this->close();
        }
    }

    public function close()
    {
        $this->setNewsletterVisited();
        $this->isModalVisible = false;
    }

    public function openPrivacyModal()
    {
        $this->isPrivacyModalVisible = true;
    }

    public function closePrivacyModal()
    {
        $this->isPrivacyModalVisible = false;
    }
}
