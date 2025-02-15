<?php

namespace App\Livewire;

use App\Notifications\SendEmailNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class ContactForm extends Component
{
    public array $form_data = [
        'name' => '',
        'email' => '',
        'subject' => '',
        'message' => '',
    ];

    public bool $isPrivacyChecked = false;

    public bool $isPrivacyModalVisible = false;

    protected $rules = [
        'form_data.name' => 'required',
        'form_data.email' => 'required|email',
        'form_data.subject' => 'required',
        'form_data.message' => 'required',
    ];

    public function send()
    {
        $this->validate();

        $emails = ['d.cavallini@cavalliniservice.com', 'g.florian@cavalliniservice.com'];

        foreach ($emails as $email) {
            Notification::route('mail', $email)->notify(new SendEmailNotification($this->form_data));
        }

        session()->flash('message', 'Messaggio Inviato');
    }

    public function openPrivacyModal()
    {
        $this->isPrivacyModalVisible = true;
    }

    public function closePrivacyModal()
    {
        $this->isPrivacyModalVisible = false;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
