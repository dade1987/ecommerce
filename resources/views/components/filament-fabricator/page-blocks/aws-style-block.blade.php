@aware(['page'])

<section class="bg-white py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <!-- Sezione "Perché AWS?" -->
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-2 lg:items-start lg:gap-16 mb-20">
            
            <!-- Colonna sinistra: Titolo e descrizione -->
            <div class="flex flex-col">
                <h2 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl mb-8">
                    {{ $percheTitle }}
                </h2>
                <div class="prose prose-lg text-gray-600 leading-relaxed" id="perche-description">
                    {!! $percheDescription !!}
                </div>
            </div>

            <!-- Colonna destra: Accordion -->
            <div class="space-y-4">
                @foreach($accordionItems ?? [] as $index => $item)
                    <div class="border border-gray-200 rounded-lg" x-data="{ open: false }">
                        <button 
                            @click="open = !open"
                            class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors duration-200"
                        >
                            <span class="font-medium text-gray-900">{{ $item['title'] }}</span>
                            <svg 
                                class="w-5 h-5 text-gray-500 transform transition-transform duration-200" 
                                :class="{ 'rotate-45': open }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </button>
                        <div 
                            x-show="open" 
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform -translate-y-2"
                            class="px-6 pb-4"
                        >
                            <div class="prose prose-sm text-gray-600">
                                {!! $item['content'] !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const descriptionElement = document.getElementById('perche-description');
    if (!descriptionElement) return;

    const content = descriptionElement.innerHTML;
    const searchTerm = 'core business';
    const index = content.toLowerCase().indexOf(searchTerm.toLowerCase());
    
    if (index === -1) return;

    const endIndex = index + searchTerm.length;
    
    const visibleText = content.slice(0, endIndex);
    const hiddenText = content.slice(endIndex);
    
    // Pulisci il contenuto originale per ricostruirlo
    descriptionElement.innerHTML = '';

    // Aggiungi la parte visibile
    const visibleSpan = document.createElement('span');
    visibleSpan.innerHTML = visibleText;
    descriptionElement.appendChild(visibleSpan);

    // Aggiungi la parte nascosta, wrappata in uno span
    const hiddenSpan = document.createElement('span');
    hiddenSpan.innerHTML = hiddenText;
    hiddenSpan.style.display = 'none';
    descriptionElement.appendChild(hiddenSpan);

    // Aggiungi il container del link "Continua a Leggere..."
    const linkContainer = document.createElement('span');
    linkContainer.innerHTML = '<br><a href="#" class="text-blue-600 hover:text-blue-800 font-medium underline cursor-pointer">Continua a Leggere...</a>';
    descriptionElement.appendChild(linkContainer);
    
    const readMoreLink = linkContainer.querySelector('a');

    readMoreLink.addEventListener('click', function(event) {
        event.preventDefault(); // Impedisce il comportamento di default del link (es. saltare in cima alla pagina)
        hiddenSpan.style.display = 'inline'; // Mostra il testo nascosto
        linkContainer.style.display = 'none'; // Nasconde il link "Continua a Leggere..."
    });
});
</script>

 