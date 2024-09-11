<div class="flex bg-white h-screen">

    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden" id="quoteModal">
        <div class="bg-white rounded-lg overflow-hidden w-11/12 md:w-1/2 lg:w-1/3">
            <div class="p-4 flex justify-between items-center border-b">
                <h5 class="text-xl font-bold" id="quoteModalLabel">AI-799</h5>
                <button type="button" class="text-gray-400" id="closeModal">&times;</button>
            </div>
            <div class="p-4">
                <div id="quoteContent" class="p-4"></div>
            </div>
            <div class="p-4 border-t flex justify-end">
                <button type="button" class="bg-gray-500 text-white p-2 rounded mr-2" id="closeModalButton">Chiudi</button>
                <button type="button" class="bg-blue-500 text-white p-2 rounded" id="downloadPdf">Scarica PDF</button>
                <button type="button" class="bg-green-500 text-white p-2 rounded" id="confirmQuote">Conferma Preventivo</button>
            </div>
        </div>
    </div>
    
    

    <div id="chatbox-window" class="flex flex-col flex-1 overflow-hidden">
        <div class="border-dashed border-2 border-gray-300 p-5" x-data="{ isDropping: false }"
            x-on:dragover.prevent="isDropping = true" x-on:dragleave.prevent="isDropping = false"
            x-on:drop.prevent="
        isDropping = false;
        $event.target.files = $event.dataTransfer.files;
        $wire.uploadMultiple('files', $event.target.files, () => {
            $wire.uploadFile();
        });
    "
            x-on:click="$refs.fileInput.click()" :class="{ 'bg-gray-200': isDropping }">
            <p>Trascina qui i file o clicca per caricare</p>
            <input type="file" multiple class="hidden" wire:model="files" x-ref="fileInput"
                x-on:change="
            $wire.uploadMultiple('files', $event.target.files, () => {
                $wire.uploadFile();
            });">
        </div>

        <div id="chatbox" class="flex-1 overflow-y-auto p-4 bg-gray-50">
            @foreach ($chat as $msg)
                <div
                    class="p-4 rounded-lg mt-2 border @if ($msg['sender'] == 'Me') border-blue-500 text-black @else border-gray-200 text-black @endif ">
                    <h5>{{ $msg['sender'] }}</h5>
                    {{ $msg['content'] }}
                </div>
            @endforeach
        </div>
        <div class="pb-4 flex items-center p-2 bg-gray-100">
            <input wire:model="message" type="text" id="user-input" class="flex-grow border rounded-l-lg p-2"
                placeholder="Scrivi un messaggio...">
            <button wire:click="sendMessage" id="send-button"
                class="bg-blue-500 text-white p-2 rounded-r-lg">Invia</button>
        </div>
        <button wire:click="generateQuote" id="generate-quote-button" class="bg-blue-500 text-white rounded-lg w-full p-4">Genera
            Preventivo</button>
    </div>

</div>
