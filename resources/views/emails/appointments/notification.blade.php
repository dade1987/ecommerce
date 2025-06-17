<x-mail::message>
# Nuovo Appuntamento

Un nuovo appuntamento è stato creato.

**Azienda:** {{ $appointment->customer->name }} <br>
**Data:** {{ $appointment->appointment_date->format('d/m/Y H:i') }} <br>
**Con:** {{ $appointment->with_person }} <br>
**Note:** {{ $appointment->notes }}

Grazie,<br>
{{ config('app.name') }}
</x-mail::message>
