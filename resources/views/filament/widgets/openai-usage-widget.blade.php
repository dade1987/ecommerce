<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Utilizzo OpenAI (Stima)
        </x-slot>

        @if($this->error)
            <div class="text-danger-600 dark:text-danger-400">
                {{ $this->error }}
            </div>
        @elseif($this->usageData)
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Oggi</div>
                        <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                            ${{ number_format($this->usageData['today']['estimated_cost'], 4) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            ~{{ number_format($this->usageData['today']['estimated_tokens']) }} token
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-500">
                            {{ $this->usageData['today']['searches'] }} ricerche, {{ $this->usageData['today']['chats'] }} chat
                        </div>
                    </div>

                    <div class="p-4 bg-success-50 dark:bg-success-900/20 rounded-lg">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Questo Mese</div>
                        <div class="text-2xl font-bold text-success-600 dark:text-success-400">
                            ${{ number_format($this->usageData['month']['estimated_cost'], 2) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            ~{{ number_format($this->usageData['month']['estimated_tokens']) }} token
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-500">
                            {{ $this->usageData['month']['searches'] }} ricerche, {{ $this->usageData['month']['chats'] }} chat
                        </div>
                    </div>
                </div>

                <div class="text-xs text-gray-500 dark:text-gray-500 italic">
                    * Stime basate su ricerche e chat registrate. I costi reali possono variare.
                </div>
            </div>
        @else
            <div class="text-gray-500 dark:text-gray-400">
                Caricamento dati...
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>

