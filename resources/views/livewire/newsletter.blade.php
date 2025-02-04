<!-- Start of Selection -->
<div>
    @if ($isModalVisible)
        <div class="fixed z-10 inset-0 flex items-center justify-center overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity" aria-hidden="true"></div>

            <div
                class="inline-block align-middle bg-gray-100 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Vuoi rimanere aggiornato sulle nostre offerte?
                        </h3>
                        <div class="mt-2">
                            <input type="email" wire:model="email"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                placeholder="Inserisci la tua email">
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 flex justify-between">
                    <button type="button" wire:click="close"
                        class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                        Chiudi
                    </button>
                    <button type="button" wire:click="save"
                        class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                        Iscriviti alla newsletter
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
<!-- End of Selection -->
