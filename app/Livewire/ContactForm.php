<?php

namespace App\Livewire;

use App\Notifications\SendEmailNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class ContactForm extends Component
{
    public array $form_data;

    protected $rules = [

        'form_data.name' => 'required',
        'form_data.email' => 'required|email',
        'form_data.subject' => 'required',
        'form_data.message' => 'required',

    ];

    public function send()
    {
        $this->validate();

        $email = 'davidecavallini1987@gmail.com';

        Notification::route('mail', $email)->notify(new SendEmailNotification($this->form_data));

        session()->flash('message', 'Messaggio Inviato');
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
