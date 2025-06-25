<x-mail::message>
# Nuova Candidatura Ricevuta

Hai ricevuto una nuova candidatura da:

**Nome:** {{ $name }}<br>
**Email:** {{ $email }}

Il curriculum vitae è allegato a questa email.

Grazie,<br>
{{ config('app.name') }}
</x-mail::message>