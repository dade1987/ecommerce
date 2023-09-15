<section>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex justify-between h-16">
            <form wire:submit="sendOrder" class="w-full">
                {{ $this->selectDateForm }}
                {{ $this->selectAddressForm }}

                <button type="submit"
                    class="btn mx-auto text-white bg-green-500 border-0 py-2 mt-3 px-8 focus:outline-none hover:bg-green-600 rounded text-lg">
                    <span class="sr-only sm:not-sr-only">Invia Ordine
                </button>
            </form>
        </div>
    </div>
</section>
