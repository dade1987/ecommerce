<div class="mx-auto max-w-6xl p-6">
    {{-- The best athlete wants his opponent at his best. --}}

    <!-- Ricerca e aggiunta ristorante -->
    <div class="flex flex-col gap-2 mb-6">
        <div class="flex flex-row gap-4 items-center">
            <input type="text" wire:model="search" placeholder="Cerca ristorante..." class="border rounded px-3 py-2 w-full" />
            <button wire:click="cerca" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition w-full md:w-auto">Cerca</button>
        </div>
        <div class="flex justify-end mt-2">
            <button wire:click="addRestaurant" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition w-full md:w-auto">Aggiungi Ristorante</button>
        </div>
    </div>
</div>
