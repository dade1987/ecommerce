<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
    <div class="p-4 border rounded-lg shadow-sm bg-white dark:bg-gray-800">
        <form wire:submit.prevent="analyze">
            <div class="flex items-center space-x-4">
                <div class="flex-grow">
                    <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Dominio da Analizzare</label>
                    <input wire:model.defer="domain" id="domain" type="text" placeholder="esempio.com"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           required>
                    @error('domain') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="flex-grow">
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email</label>
                    <input wire:model.defer="email" id="email" type="email" placeholder="tua@email.com"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           required>
                    @error('email') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="flex-grow">
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Telefono (facoltativo)</label>
                    <input wire:model.defer="phone" id="phone" type="tel" placeholder="+39 123 4567890"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('phone') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="pt-5">
                    <button type="submit"
                            wire:loading.attr="disabled"
                            wire:target="analyze"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50">
                        <svg wire:loading wire:target="analyze" class="w-5 h-5 mr-2 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Analizza
                    </button>
                </div>
            </div>
        </form>

        <div wire:loading.flex wire:target="analyze" class="items-center justify-center p-8 mt-6 text-center">
            <div class="text-lg font-semibold">
                <svg class="inline w-8 h-8 mr-3 text-gray-200 animate-spin dark:text-gray-600 fill-primary-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0492C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                </svg>
                <span class="ml-4">{{ $statusMessage }}</span>
            </div>
        </div>

        @if ($error)
            <div class="p-4 mt-6 text-red-700 bg-red-100 border border-red-200 rounded-md">
                <h3 class="text-lg font-semibold">Errore durante l'Analisi</h3>
                <p>Si è verificato un errore durante l'analisi. Riprova più tardi.</p>
            </div>
        @endif

        @if ($result && !$error)
            <div class="p-6 mt-6 border rounded-lg">
                <h3 class="text-xl font-bold text-center text-gray-800 dark:text-gray-100">Rapporto di Analisi per: {{ $domain }}</h3>

                <div class="my-6 text-center">
                    <p class="text-lg text-gray-600 dark:text-gray-300">Valutazione del Rischio di Compromissione (prossimi 3 mesi)</p>
                    <p class="text-6xl font-extrabold {{ $result['risk_percentage'] > 50 ? 'text-red-500' : 'text-green-500' }}">
                        {{ $result['risk_percentage'] }}%
                    </p>
                </div>

                @if ($result['risk_percentage'] > 40)
                    <div class="p-4 my-6 text-center text-yellow-800 bg-yellow-100 border-l-4 border-yellow-500 rounded-md">
                        <p class="font-bold">Il suo livello di rischio risulta elevato</p>
                        <p class="text-sm">Si consiglia di pianificare una consulenza professionale per implementare le misure di sicurezza appropriate e mitigare i rischi identificati.</p>
                        @livewire('open-calendar-button')
                    </div>
                @else
                    <div class="p-4 my-6 text-center text-blue-800 bg-blue-100 border-l-4 border-blue-500 rounded-md">
                        <p class="font-bold">Il suo livello di rischio risulta contenuto</p>
                        <p class="text-sm">Tuttavia, la sicurezza informatica è un processo continuo che richiede monitoraggio costante. Una consulenza professionale può aiutarla a ottimizzare ulteriormente la protezione del suo sistema.</p>
                        @livewire('open-calendar-button')
                    </div>
                @endif

                <div class="mt-6">
                    <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Aree di Attenzione Identificate:</h4>
                    <ul class="mt-2 space-y-2 list-disc list-inside">
                        @forelse ($result['critical_points'] as $point)
                            <li class="text-gray-600 dark:text-gray-300">{{ $point }}</li>
                        @empty
                            <li class="text-gray-500">Nessuna criticità specifica rilevata. La sua infrastruttura presenta un buon profilo di sicurezza.</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Per debug, si può de-commentare --}}
                {{-- <details class="mt-6">
                    <summary>Dati Grezzi Raccolti</summary>
                    <pre class="p-4 mt-2 text-xs text-gray-800 bg-gray-100 rounded-md whitespace-pre-wrap">{{ json_encode($result['raw_data'], JSON_PRETTY_PRINT) }}</pre>
                </details> --}}
            </div>
        @endif
    </div>
</div>
