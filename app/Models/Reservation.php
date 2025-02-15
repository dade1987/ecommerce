<?php

namespace App\Models;

use App\Notifications\FreeConsultationBookingNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'people_number', 'starts_at', 'ends_at', 'telephone_number', 'allergens'];

    protected static function booted()
    {
        static::created(function ($event) {
            $details = [
                'subject' => 'Nuova Prenotazione di Consulenza Gratuita',
                'message' => 'È stata effettuata una nuova prenotazione di consulenza gratuita.',
                'name' => $event->name,
                'starts_at' => $event->starts_at,
                'ends_at' => $event->ends_at,
            ];
            $emails = ['d.cavallini@cavalliniservice.com', 'g.florian@cavalliniservice.com'];
            foreach ($emails as $email) {
                Notification::route('mail', $email)
                    ->notify(new FreeConsultationBookingNotification($details));
            }
        });

        static::updated(function ($event) {
            $details = [
                'subject' => 'Prenotazione di Consulenza Gratuita Aggiornata',
                'message' => 'Una prenotazione di consulenza gratuita è stata aggiornata.',
                'name' => $event->name,
                'starts_at' => $event->starts_at,
                'ends_at' => $event->ends_at,
            ];
            $emails = ['d.cavallini@cavalliniservice.com', 'g.florian@cavalliniservice.com'];
            foreach ($emails as $email) {
                Notification::route('mail', $email)
                    ->notify(new FreeConsultationBookingNotification($details));
            }
        });

        static::deleted(function ($event) {
            $details = [
                'subject' => 'Prenotazione di Consulenza Gratuita Cancellata',
                'message' => 'Una prenotazione di consulenza gratuita è stata cancellata.',
                'name' => $event->name,
                'starts_at' => $event->starts_at,
                'ends_at' => $event->ends_at,
            ];
            $emails = ['d.cavallini@cavalliniservice.com', 'g.florian@cavalliniservice.com'];
            foreach ($emails as $email) {
                Notification::route('mail', $email)
                    ->notify(new FreeConsultationBookingNotification($details));
            }
        });
    }
}
