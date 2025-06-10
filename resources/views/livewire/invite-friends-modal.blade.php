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
                    <label class="block font-semibold mb-1">Numeri di telefono</label>
                    @foreach($phones as $index => $phone)
                    <div class="flex mb-2">
                        <input type="text" wire:model="phones.{{ $index }}" placeholder="+39..." class="flex-1 border rounded px-2 py-1 mr-2" />
                        @if(count($phones) > 1)
                        <button type="button" wire:click="removePhone({{ $index }})" class="text-red-500">Rimuovi</button>
                        @endif
                    </div>
                    @endforeach
                    <button type="button" wire:click="addPhone" class="text-blue-600 hover:underline text-sm">+ Aggiungi numero</button>
                </div>
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Messaggio</label>

                    <textarea wire:model="message" class="w-full border rounded px-2 py-1" rows="3"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Link WhatsApp generati:</label>
                    <div class="space-y-2">
                        @foreach($this->whatsappLinks as $index => $link)
                        @if(!empty($phones[$index]))
                        <a href="{{ $link }}" target="_blank" class="block bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition">
                            Invia a {{ $phones[$index] }}
                        </a>
                        @endif
                        @endforeach


                        @if(!empty($phones[0]))
                        <div class="mb-2">
                            <label class="block font-semibold mb-1">Nome Tavolo</label>
                            <input type="text" wire:model="tableName" class="w-full border rounded px-2 py-1" placeholder="Nome Tavolo" />
                        </div>
                        <div class="mb-2">
                            <label for="date" class="block font-semibold mb-1">Data</label>
                            <input type="date" id="date" wire:model="date" class="w-full border rounded px-2 py-1">
                        </div>
                        <div class="mb-2">
                            <label for="time_slot" class="block font-semibold mb-1">Orario</label>
                            <select id="time_slot" wire:model="time_slot" class="w-full border rounded px-2 py-1">
                                <option value="">Seleziona un orario</option>
                                @foreach($this->timeSlots as $slot)
                                    <option value="{{ $slot }}">{{ $slot }}</option>
                                @endforeach
                            </select>
                            <small class="text-gray-500">Durata automatica: 2 ore</small>
                        </div>
                        <button type="button" wire:click="prenotaTavolo" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">Prenota Tavolo</button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>