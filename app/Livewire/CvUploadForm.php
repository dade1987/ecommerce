<?php

namespace App\Livewire;

use App\Mail\NewCvSubmission;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithFileUploads;

class CvUploadForm extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $cv;
    public bool $privacy_consent = false;
    public string $privacy_policy_text;

    public function mount(string $privacy_policy_text)
    {
        $this->privacy_policy_text = $privacy_policy_text;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB Max
            'privacy_consent' => 'accepted',
        ];
    }

    public function submit()
    {
        $this->validate();

        $filePath = $this->cv->store('cvs', 'public');

        // Invia email di notifica
        Mail::to(config('mail.from.address'))->send(new NewCvSubmission($this->name, $this->email, $filePath));

        session()->flash('success', 'Candidatura inviata con successo! Grazie.');

        $this->reset(['name', 'email', 'cv', 'privacy_consent']);
    }

    public function getCleanedPrivacyPolicyTextProperty(): string
    {
        // Decodifica le entità HTML (es. &lt;p&gt; -> <p>)
        $decodedText = html_entity_decode($this->privacy_policy_text);
        
        // Rimuove tutti i tag HTML
        $strippedText = strip_tags($decodedText);

        return $strippedText;
    }

    public function render()
    {
        return view('livewire.cv-upload-form');
    }
} 