<div class="mx-auto max-w-6xl p-6">
    {{-- The best athlete wants his opponent at his best. --}}

    <!-- Ricerca e aggiunta ristorante -->
    <div class="flex flex-col gap-2 mb-6">
        <div class="flex flex-row gap-4 items-center">
            <input type="text" wire:model="search" placeholder="Cerca ristorante..." class="border rounded px-3 py-2 w-full" />
            <button wire:click="cerca" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition w-full md:w-auto">Cerca</button>
        </div>
        <div class="flex justify-end mt-2">
            <button wire:click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition w-full md:w-auto">Aggiungi Ristorante</button>
        </div>
    </div>

    @if($showCreateModal)
        <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg relative">
                <button wire:click="closeCreateModal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                <h2 class="text-xl font-bold mb-4">Aggiungi Ristorante</h2>
                <form wire:submit.prevent="salvaRistorante" class="space-y-4">
                    {{ $this->form }}
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" wire:click="closeCreateModal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Annulla</button>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Salva</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
