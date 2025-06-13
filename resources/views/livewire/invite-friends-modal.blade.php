<div>
    @if($showModal)
    <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <button wire:click="closeModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">&times;</button>
            <h2 class="text-2xl font-bold mb-4 text-center">Invita amici su WhatsApp</h2>
            <div class="mb-4">
                <span class="block text-lg font-semibold text-gray-700">Prenotante: {{ $userName }}</span>
            </div>
            <form wire:submit.prevent>
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Il tuo nome</label>
                    <input type="text" wire:model="userName" class="w-full border rounded px-2 py-1" placeholder="Inserisci il tuo nome" />
                </div>
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Il tuo numero di telefono</label>
                    <input type="tel" wire:model="userPhone" class="w-full border rounded px-2 py-1" placeholder="+39..." />
                </div>
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Messaggio</label>
                    <textarea wire:model="message" class="w-full border rounded px-2 py-1" rows="3"></textarea>
                </div>
                <div class="mb-6">
                    <a href="{{ $this->getShareLink() }}" target="_blank" class="block bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded transition text-center">
                        Condividi su WhatsApp
                    </a>
                </div>
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Numero di partecipanti</label>
                    <input type="number" wire:model="people_number" min="1" class="w-full border rounded px-2 py-1" />
                </div>
                <button type="button" wire:click="prenotaTavolo" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">Prenota Tavolo</button>
            </form>
        </div>
    </div>
    @endif
</div>