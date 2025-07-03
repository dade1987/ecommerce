<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Bus;
use function Safe\date;
use function Safe\strtotime;

class InviteFriendsModal extends Component
{
    public $message = '';
    public $showModal = false;
    public $restaurantName = '';
    public $restaurantId = null;
    public $userName = '';
    public $userPhone = '';
    public $date;
    public $time_slot;
    public $people_number = 1;

    protected $rules = [
        'message' => 'required|string',
        'people_number' => 'required|integer|min:1',
        'userName' => 'required|string|min:2',
        'userPhone' => 'required|string|min:8'
    ];

    protected $listeners = ['openInviteFriendsModal' => 'openModal'];

    public function mount()
    {
        $user = Auth::user();
        if ($user) {
            $this->userName = $user->name;
            $this->userPhone = $user->telephone_number ?? '';
        }
        $this->date = request()->query('date', date('Y-m-d'));
        $this->time_slot = request()->query('time_slot', '19:00');
    }

    public function openModal($params)
    {
        $this->restaurantId = $params['restaurantId'] ?? null;
        if ($this->restaurantId) {
            $this->restaurantName = $this->getRestaurantNameById($this->restaurantId);
            $formattedDate = date('d/m/Y', strtotime($this->date));
            $this->message = "Ti invito tramite MySocialTable a prendere parte alla nostra tavolata al ristorante: {$this->restaurantName} il giorno {$formattedDate} alle ore {$this->time_slot}";
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function getShareLink()
    {
        $msg = urlencode($this->message);
        return "https://wa.me/?text={$msg}";
    }

    public function prenotaTavolo()
    {
        $this->validate();

        $starts_at = $this->date . ' ' . $this->time_slot . ':00';
        $ends_at = date('Y-m-d H:i:s', strtotime($starts_at . ' +2 hours'));
        
        $reservation = Reservation::create([
            'name' => "{$this->userName} - {$this->restaurantName}",
            'telephone_number' => $this->userPhone,
            'people_number' => $this->people_number,
            'allergens' => null,
            'starts_at' => $starts_at,
            'ends_at' => $ends_at,
            'restaurant_id' => $this->restaurantId,
        ]);

        // Invio email asincrono
        Bus::dispatch(function () use ($starts_at, $ends_at) {
            Mail::raw(
                "Prenotazione tavolo\n" .
                "Ristorante: {$this->restaurantName}\n" .
                "Prenotante: {$this->userName}\n" .
                "Telefono: {$this->userPhone}\n" .
                "Numero partecipanti: {$this->people_number}\n" .
                "Data/Ora: {$starts_at} - {$ends_at}",
                function($message) {
                    $message->to('d.cavallini@cavalliniservice.com')
                            ->subject("Nuova Prenotazione - {$this->restaurantName}");
                }
            );
        });

        $this->closeModal();
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
