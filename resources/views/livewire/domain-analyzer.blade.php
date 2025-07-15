<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
    <div class="p-4 border rounded-lg shadow-sm bg-white dark:bg-gray-800">
        <form wire:submit.prevent="analyze">
            <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:items-end">
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
                <div>
                    <button type="submit"
                            wire:loading.attr="disabled"
                            wire:target="analyze"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-500 border border-transparent rounded-md shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
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
                <span class="ml-4">Analisi in corso... L'operazione può richiedere anche più di 1 minuto per completarsi.</span>
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
                    <div class="flex items-center justify-between">
                        <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Aree di Attenzione Identificate:</h4>
                        @if(isset($result['critical_points']) && count($result['critical_points']) > 0)
                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full dark:bg-red-900 dark:text-red-200">
                                {{ count($result['critical_points']) }} punti critici
                            </span>
                        @endif
                    </div>
                    <ul class="mt-2 space-y-2 list-disc list-inside">
                        @forelse ($result['critical_points'] as $point)
                            <li class="text-gray-600 dark:text-gray-300">{{ $point }}</li>
                        @empty
                            <li class="text-gray-500">Nessuna criticità specifica rilevata. La sua infrastruttura presenta un buon profilo di sicurezza.</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Sezione informazioni di sicurezza avanzate --}}
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Protezione Cloudflare --}}
                    @if(isset($result['raw_data']['analysis_data']))
                        @php
                            $cloudflareProtected = false;
                            $wpDetected = false;
                            $totalOpenPorts = 0;
                            $hasVulnerabilities = false;
                            
                            foreach($result['raw_data']['analysis_data'] as $domain => $data) {
                                if(isset($data['cloudflare_detection']['is_cloudflare']) && $data['cloudflare_detection']['is_cloudflare']) {
                                    $cloudflareProtected = true;
                                }
                                if(isset($data['wordpress_analysis']['is_wordpress']) && $data['wordpress_analysis']['is_wordpress']) {
                                    $wpDetected = true;
                                }
                                if(isset($data['port_scan']['open_ports'])) {
                                    $totalOpenPorts += count($data['port_scan']['open_ports']);
                                }
                                if(isset($data['cve_analysis']['vulnerabilities']) && !empty($data['cve_analysis']['vulnerabilities'])) {
                                    $hasVulnerabilities = true;
                                }
                            }
                        @endphp
                        
                        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                            <h5 class="font-medium text-gray-700 dark:text-gray-200 mb-2">Protezione Cloudflare</h5>
                            <div class="flex items-center">
                                @if($cloudflareProtected)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Protetto
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Non rilevato
                                    </span>
                                @endif
                            </div>
                            
                            {{-- Mostra dettagli Cloudflare --}}
                            @if($cloudflareProtected)
                                <div class="mt-2 max-h-20 overflow-y-auto">
                                    @foreach($result['raw_data']['analysis_data'] as $domain => $data)
                                        @if(isset($data['cloudflare_detection']['is_cloudflare']) && $data['cloudflare_detection']['is_cloudflare'])
                                            <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">
                                                <strong>{{ $domain }}:</strong>
                                                @if(isset($data['cloudflare_detection']['indicators']) && !empty($data['cloudflare_detection']['indicators']))
                                                    <div class="mt-1">
                                                        @foreach($data['cloudflare_detection']['indicators'] as $indicator)
                                                            <span class="inline-block bg-green-100 text-green-800 px-1 py-0.5 rounded mr-1 text-xs">
                                                                {{ $indicator }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- WordPress Detection --}}
                        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                            <h5 class="font-medium text-gray-700 dark:text-gray-200 mb-2">WordPress</h5>
                            <div class="flex items-center">
                                @if($wpDetected)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                        Rilevato
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        Non rilevato
                                    </span>
                                @endif
                            </div>
                            
                            {{-- Mostra dettagli WordPress --}}
                            @if($wpDetected)
                                <div class="mt-2 max-h-32 overflow-y-auto">
                                    @foreach($result['raw_data']['analysis_data'] as $domain => $data)
                                        @if(isset($data['wordpress_analysis']['is_wordpress']) && $data['wordpress_analysis']['is_wordpress'])
                                            <div class="text-xs text-gray-600 dark:text-gray-400 mb-3 border-b pb-2">
                                                <strong>{{ $domain }}:</strong>
                                                @if(isset($data['wordpress_analysis']['version']))
                                                    <span class="inline-block bg-blue-100 text-blue-800 px-1 py-0.5 rounded mr-1">
                                                        v{{ $data['wordpress_analysis']['version'] }}
                                                    </span>
                                                @endif
                                                
                                                {{-- Plugin con versioni --}}
                                                @if(isset($data['wordpress_analysis']['plugins']) && !empty($data['wordpress_analysis']['plugins']))
                                                    <div class="mt-1">
                                                        <span class="text-xs font-medium">Plugin ({{ count($data['wordpress_analysis']['plugins']) }}):</span>
                                                        <div class="flex flex-wrap gap-1 mt-1">
                                                            @foreach(array_slice($data['wordpress_analysis']['plugins'], 0, 4, true) as $plugin => $version)
                                                                <span class="inline-block bg-yellow-100 text-yellow-800 px-1 py-0.5 rounded text-xs">
                                                                    {{ $plugin }}
                                                                    @if($version)
                                                                        <span class="text-yellow-600">v{{ $version }}</span>
                                                                    @else
                                                                        <span class="text-red-600">(?)</span>
                                                                    @endif
                                                                </span>
                                                            @endforeach
                                                            @if(count($data['wordpress_analysis']['plugins']) > 4)
                                                                <span class="text-xs text-gray-500">+{{ count($data['wordpress_analysis']['plugins']) - 4 }} altri</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                {{-- Temi con versioni --}}
                                                @if(isset($data['wordpress_analysis']['themes']) && !empty($data['wordpress_analysis']['themes']))
                                                    <div class="mt-1">
                                                        <span class="text-xs font-medium">Temi:</span>
                                                        <div class="flex flex-wrap gap-1 mt-1">
                                                            @foreach(array_slice($data['wordpress_analysis']['themes'], 0, 2, true) as $theme => $version)
                                                                <span class="inline-block bg-purple-100 text-purple-800 px-1 py-0.5 rounded text-xs">
                                                                    {{ $theme }}
                                                                    @if($version)
                                                                        <span class="text-purple-600">v{{ $version }}</span>
                                                                    @else
                                                                        <span class="text-red-600">(?)</span>
                                                                    @endif
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                {{-- Plugin vulnerabili --}}
                                                @if(isset($data['wordpress_analysis']['vulnerable_plugins']) && !empty($data['wordpress_analysis']['vulnerable_plugins']))
                                                    <div class="mt-1">
                                                        <span class="text-xs font-medium text-red-600">Plugin Vulnerabili:</span>
                                                        <div class="flex flex-wrap gap-1 mt-1">
                                                            @foreach($data['wordpress_analysis']['vulnerable_plugins'] as $vuln)
                                                                <span class="inline-block bg-red-100 text-red-800 px-1 py-0.5 rounded text-xs">
                                                                    {{ $vuln['name'] ?? 'Unknown' }}
                                                                    @if(isset($vuln['version']))
                                                                        <span class="text-red-600">v{{ $vuln['version'] }}</span>
                                                                    @endif
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                {{-- File esposti --}}
                                                @if(isset($data['wordpress_analysis']['exposed_files']) && !empty($data['wordpress_analysis']['exposed_files']))
                                                    <div class="mt-1">
                                                        <span class="text-xs font-medium text-orange-600">File Esposti ({{ count($data['wordpress_analysis']['exposed_files']) }}):</span>
                                                        <div class="flex flex-wrap gap-1 mt-1">
                                                            @foreach(array_slice($data['wordpress_analysis']['exposed_files'], 0, 3, true) as $file => $desc)
                                                                <span class="inline-block bg-orange-100 text-orange-800 px-1 py-0.5 rounded text-xs">
                                                                    {{ basename($file) }}
                                                                </span>
                                                            @endforeach
                                                            @if(count($data['wordpress_analysis']['exposed_files']) > 3)
                                                                <span class="text-xs text-gray-500">+{{ count($data['wordpress_analysis']['exposed_files']) - 3 }} altri</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Porte Aperte --}}
                        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                            <h5 class="font-medium text-gray-700 dark:text-gray-200 mb-2">Porte Aperte</h5>
                            <div class="flex items-center">
                                @if($totalOpenPorts > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $totalOpenPorts }} porte
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Nessuna porta aperta
                                    </span>
                                @endif
                            </div>
                            
                            {{-- Mostra dettagli delle porte aperte --}}
                            @if($totalOpenPorts > 0)
                                <div class="mt-2 max-h-24 overflow-y-auto">
                                    @foreach($result['raw_data']['analysis_data'] as $domain => $data)
                                        @if(isset($data['port_scan']['open_ports']) && !empty($data['port_scan']['open_ports']))
                                            <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">
                                                <strong>{{ $domain }}:</strong>
                                                @foreach($data['port_scan']['open_ports'] as $port)
                                                    <span class="inline-block bg-blue-100 text-blue-800 px-1 py-0.5 rounded mr-1">
                                                        {{ $port }}
                                                        @if(isset($data['port_scan']['services'][$port]['service']))
                                                            ({{ $data['port_scan']['services'][$port]['service'] }})
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Vulnerabilità --}}
                        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                            <h5 class="font-medium text-gray-700 dark:text-gray-200 mb-2">Vulnerabilità CVE</h5>
                            <div class="flex items-center">
                                @if($hasVulnerabilities)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Rilevate
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Nessuna vulnerabilità
                                    </span>
                                @endif
                            </div>
                            
                            {{-- Mostra dettagli delle vulnerabilità CVE --}}
                            @if($hasVulnerabilities)
                                <div class="mt-2 max-h-32 overflow-y-auto">
                                    @foreach($result['raw_data']['analysis_data'] as $domain => $data)
                                        @if(isset($data['cve_analysis']['vulnerabilities']) && !empty($data['cve_analysis']['vulnerabilities']))
                                            <div class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                                <strong>{{ $domain }}:</strong>
                                                @foreach($data['cve_analysis']['vulnerabilities'] as $vuln)
                                                    <div class="bg-red-50 border border-red-200 rounded px-2 py-1 mt-1">
                                                        <div class="flex items-center justify-between">
                                                            <span class="font-medium text-red-800">
                                                                Porta {{ $vuln['port'] ?? 'N/A' }}
                                                            </span>
                                                            @if(isset($vuln['risk_level']))
                                                                <span class="text-xs bg-red-200 text-red-800 px-1 py-0.5 rounded">
                                                                    Rischio: {{ $vuln['risk_level'] }}/10
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="text-xs text-red-600 mt-1">
                                                            {{ $vuln['software'] ?? $vuln['service'] ?? 'Servizio sconosciuto' }}
                                                        </div>
                                                        @if(isset($vuln['potential_cves']) && !empty($vuln['potential_cves']))
                                                            <div class="text-xs text-red-700 mt-1">
                                                                CVE: {{ implode(', ', array_slice($vuln['potential_cves'], 0, 3)) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
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
