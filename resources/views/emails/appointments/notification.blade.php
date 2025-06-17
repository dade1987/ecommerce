@component('mail::message')
# {{ $isDeletion ? 'Appuntamento Annullato' : 'Nuovo Appuntamento' }}

@if($isDeletion)
Un appuntamento è stato annullato.
@else
È stato creato un nuovo appuntamento.
@endif

**Cliente:** {{ $customerName }}<br>
**Data Appuntamento:** {{ $appointmentDate }}<br>
**Con:** {{ $withPerson }}<br>
**Telefono Cliente:** {{ $customerPhone }}

Grazie,<br>
{{ config('app.name') }}
@endcomponent
