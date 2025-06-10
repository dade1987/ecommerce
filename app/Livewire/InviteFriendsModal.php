<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;

class InviteFriendsModal extends Component
{
    public $phones = [''];
    public $message = 'Ti invito tramite MySocialTable a prendere parte alla nostra tavolata al ristorante: ';
    public $showModal = false;
    public $restaurantName = '';
    public $userName;
    public $tableName = '';
    public $reservationSuccess = false;
    public $date;
    public $time_slot;

    protected $rules = [
        'phones.*' => 'required|string',
        'message' => 'required|string',
    ];

    protected $listeners = ['openInviteFriendsModal' => 'openModal'];

    public function mount()
    {
        $user = Auth::user();
        $this->userName = $user ? $user->name : '';
    }

    public function addPhone()
    {
        $this->phones[] = '';
    }

    public function removePhone($index)
    {
        unset($this->phones[$index]);
        $this->phones = array_values($this->phones);
    }

    public function openModal($params)
    {
        $restaurantId = $params['restaurantId'] ?? null;
        if ($restaurantId) {
            $this->restaurantName = $this->getRestaurantNameById($restaurantId);
            $this->message .= $this->restaurantName;
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function getWhatsappLinksProperty()
    {
        $links = [];
        foreach ($this->phones as $phone) {
            $number = preg_replace('/[^0-9]/', '', $phone);
            $msg = urlencode($this->message);
            $links[] = "https://wa.me/$number?text=$msg";
        }
        return $links;
    }

    public function getTimeSlotsProperty()
    {
        $slots = [];
        $start = strtotime('19:00');
        $end = strtotime('22:00');
        for ($t = $start; $t <= $end; $t += 1800) {
            $slots[] = date('H:i', $t);
        }
        return $slots;
    }

    public function prenotaTavolo()
    {
        $this->validate([
            'phones.0' => 'required|string',
            'userName' => 'required|string',
            'tableName' => 'required|string',
            'date' => 'required|date',
            'time_slot' => 'required',
        ]);
        $starts_at = $this->date . ' ' . $this->time_slot . ':00';
        $ends_at = date('Y-m-d H:i:s', strtotime($starts_at . ' +2 hours'));
        $reservation = Reservation::create([
            'name' => $this->userName,
            'telephone_number' => $this->phones[0],
            'people_number' => count($this->phones),
            'allergens' => null,
            'starts_at' => $starts_at,
            'ends_at' => $ends_at,
        ]);
        $this->closeModal();

        // Invio email
        Mail::raw(
            "Prenotazione tavolo\nPrenotante: {$this->userName}\nTelefono: {$this->phones[0]}\nNome Tavolo: {$this->tableName}\nData/Ora: {$starts_at} - {$ends_at}",
            function($message) {
                $message->to('prenotazioni@cavalliniservice.com')
                        ->subject('Nuova Prenotazione Tavolo');
            }
        );
        $this->reservationSuccess = true;
        Notification::make()
            ->title('Prenotazione effettuata con successo!')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.invite-friends-modal');
    }

    private function getRestaurantNameById($restaurantId)
    {
        $restaurant = \App\Models\Restaurant::find($restaurantId);
        return $restaurant ? $restaurant->name : 'Nome Ristorante non trovato';
    }
}
