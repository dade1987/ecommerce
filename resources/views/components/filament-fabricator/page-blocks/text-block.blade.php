@aware(['page'])
<div class="mx-auto max-w-6xl p-6">
    <div class="text-xl">
        @php
            // Sanitizza il contenuto prima di applicare le trasformazioni
            $safeContent = e($content);
            // Sostituisci WhatsApp con versione colorata (ora sicuro perché escaped)
            $safeContent = preg_replace('/WhatsApp/', '<span style="color: green;">Whatsapp</span>', $safeContent, 1);
        @endphp
        {!! $safeContent !!}
    </div>
</div> 