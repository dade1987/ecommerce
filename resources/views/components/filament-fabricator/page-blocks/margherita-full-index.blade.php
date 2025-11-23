<!-- Hero Section -->
<section class="hero-gradient text-white py-5 position-relative">
    <div class="container position-relative" style="z-index: 2;">
        <div class="row justify-content-center text-center py-5">
            <div class="col-12">
                <div class="d-flex justify-content-center mb-4">
                    <img src="{{ asset('logo-icon.png') }}" alt="CavalliniService Logo" class="img-fluid shadow-lg rounded-circle shadow-outline-strong" style="height: 280px; animation: float 3s ease-in-out infinite; border: 2px solid white;">
                </div>
                <h1 class="display-3 fw-bold testo-arcobaleno mb-4">
                    {{ __('Siti web, gestionali e intelligenza artificiale su misura. Cyber Security. Risultati reali.') }}
                </h1>
                
                <a href="/preventivo" class="btn btn-principale btn-lg px-5 py-3">
                    <i class="fas fa-rocket me-2"></i>
                    {{ __('Inizia il Tuo Progetto') }}
                </a>
            </div>
        </div>
    </div>
</section>

<style>
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
    100% { transform: translateY(0px); }
}
</style>

<!-- Services Section -->
<section class="py-5 bg-dark">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold text-white mb-3">
                    {{ __('I Nostri') }} <span class="text-dopaminic">{{ __('Servizi') }}</span>
                </h2>
                <p class="lead text-dark">
                    {{ __('Soluzioni tecnologiche avanzate per trasformare la tua azienda nel digitale') }}
                </p>
            </div>
        </div>

        <!-- Services Grid -->
        <div class="row g-4">
            
            <!-- Service 1: Sviluppo Siti Web -->
            <div class="col-md-6 col-lg-4">
                <a href="/servizi/sviluppo-web" class="text-decoration-none">
                    <div class="card h-100 card-dopaminica bg-info bg-opacity-15 border-0 shadow">
                        <div class="card-body text-center p-4">
                            <div class="mb-4">
                                <div class="bg-info rounded-circle d-inline-flex align-items-center justify-content-center shadow-lg" style="width: 90px; height: 90px;">
                                    <i class="fas fa-code text-white fs-1"></i>
                                </div>
                            </div>
                            <h3 class="card-title h4 fw-bold text-info mb-3">
                                {{ __('Sviluppo di siti web su misura') }}
                            </h3>
                            <p class="card-text text-dark fs-6">
                                {{ __('Creiamo siti web responsivi e performanti, ottimizzati per ogni dispositivo e motore di ricerca.') }}
                            </p>
                            <div class="mt-3">
                                <span class="btn btn-outline-info btn-sm">{{ __('Scopri di più') }} <i class="fas fa-arrow-right ms-2"></i></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Service 2: Software Gestionali -->
            <div class="col-md-6 col-lg-4">
                <a href="/servizi/software-gestionali" class="text-decoration-none">
                    <div class="card h-100 card-dopaminica bg-warning bg-opacity-15 border-0 shadow">
                        <div class="card-body text-center p-4">
                            <div class="mb-4">
                                <div class="bg-warning rounded-circle d-inline-flex align-items-center justify-content-center shadow-lg" style="width: 90px; height: 90px;">
                                    <i class="fas fa-cogs text-dark fs-1"></i>
                                </div>
                            </div>
                            <h3 class="card-title h4 fw-bold text-warning mb-3">
                                {{ __('Software gestionali personalizzati') }}
                            </h3>
                            <p class="card-text text-dark fs-6">
                                {{ __('Sviluppiamo software gestionali su misura per automatizzare e ottimizzare i processi aziendali.') }}
                            </p>
                            <div class="mt-3">
                                <span class="btn btn-outline-warning btn-sm">{{ __('Scopri di più') }} <i class="fas fa-arrow-right ms-2"></i></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Service 3: Integrazione IA -->
            <div class="col-md-6 col-lg-4">
                <a href="/servizi/intelligenza-artificiale" class="text-decoration-none">
                    <div class="card h-100 card-dopaminica bg-success bg-opacity-15 border-0 shadow">
                        <div class="card-body text-center p-4">
                            <div class="mb-4">
                                <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center shadow-lg" style="width: 90px; height: 90px;">
                                    <i class="fas fa-robot text-white fs-1"></i>
                                </div>
                            </div>
                            <h3 class="card-title h4 fw-bold text-success mb-3">
                                {{ __('Integrazione IA in siti e gestionali') }}
                            </h3>
                            <p class="card-text text-dark fs-6">
                                {{ __('Integriamo soluzioni di intelligenza artificiale per migliorare produttività e automazione.') }}
                            </p>
                            <div class="mt-3">
                                <span class="btn btn-outline-success btn-sm">{{ __('Scopri di più') }} <i class="fas fa-arrow-right ms-2"></i></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Service 4: Cyber Security -->
            <div class="col-md-6 col-lg-4">
                <a href="/servizi/cyber-security" class="text-decoration-none">
                    <div class="card h-100 card-dopaminica bg-danger bg-opacity-15 border-0 shadow">
                        <div class="card-body text-center p-4">
                            <div class="mb-4">
                                <div class="bg-danger rounded-circle d-inline-flex align-items-center justify-content-center shadow-lg" style="width: 90px; height: 90px;">
                                    <i class="fas fa-shield-alt text-white fs-1"></i>
                                </div>
                            </div>
                            <h3 class="card-title h4 fw-bold text-danger mb-3">
                                {{ __('Cyber Security e protezione dati') }}
                            </h3>
                            <p class="card-text text-dark fs-6">
                                {{ __('Proteggiamo i tuoi dati e sistemi con soluzioni avanzate di cyber security.') }}
                            </p>
                            <div class="mt-3">
                                <span class="btn btn-outline-danger btn-sm">{{ __('Scopri di più') }} <i class="fas fa-arrow-right ms-2"></i></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Service 5: Penetration Testing -->
            <div class="col-md-6 col-lg-4">
                <a href="/servizi/penetration-testing" class="text-decoration-none">
                    <div class="card h-100 card-dopaminica bg-primary bg-opacity-15 border-0 shadow">
                        <div class="card-body text-center p-4">
                            <div class="mb-4">
                                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center shadow-lg" style="width: 90px; height: 90px;">
                                    <i class="fas fa-search text-white fs-1"></i>
                                </div>
                            </div>
                            <h3 class="card-title h4 fw-bold text-primary mb-3">
                                {{ __('Penetration Testing') }}
                            </h3>
                            <p class="card-text text-dark fs-6">
                                {{ __('Test di sicurezza approfonditi per identificare e correggere vulnerabilità nei sistemi.') }}
                            </p>
                            <div class="mt-3">
                                <span class="btn btn-outline-primary btn-sm">{{ __('Scopri di più') }} <i class="fas fa-arrow-right ms-2"></i></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- CTA Section -->
            <div class="col-12 text-center mt-5">
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="/portfolio" class="btn btn-outline-primary btn-lg px-4 py-3 shadow">
                        <i class="fas fa-briefcase me-2"></i>
                        {{ __('Vedi Portfolio') }}
                    </a>
                    <a href="/preventivo" class="btn btn-warning btn-lg px-4 py-3 shadow">
                        <i class="fas fa-calculator me-2"></i>
                        {{ __('Richiedi Preventivo') }}
                    </a>
                    <a href="/scarica-brochure" class="btn btn-outline-success btn-lg px-4 py-3 shadow" target="_blank">
                        <i class="fas fa-download me-2"></i>
                        {{ __('Scarica Brochure') }}
                    </a>
                    <a href="/contatti" class="btn btn-principale btn-lg px-5 py-3 shadow-lg">
                        <i class="fas fa-phone me-2"></i>
                        {{ __('Contattaci Ora') }}
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Perché Scegliere CavalliniService -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-3">{{ __('Perché Scegliere CavalliniService') }}</h2>
            <p class="lead text-muted">{{ __('I vantaggi che fanno la differenza per il tuo progetto') }}</p>
        </div>
        
        <div class="row g-4">
            <!-- Soluzioni 100% Personalizzate -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 text-center hover-lift">
                    <div class="card-body p-4">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="bi bi-gear-wide-connected fs-1"></i>
                        </div>
                        <h5 class="fw-bold mb-3">{{ __('Soluzioni 100% Personalizzate') }}</h5>
                        <p class="text-muted">
                            {{ __('Ogni progetto è sviluppato su misura per le tue esigenze specifiche. Niente template preconfezionati, solo soluzioni uniche.') }}
                        </p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-center align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <small class="text-muted">{{ __('Analisi dettagliata') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Integrazione IA -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 text-center hover-lift">
                    <div class="card-body p-4">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="bi bi-cpu-fill fs-1"></i>
                        </div>
                        <h5 class="fw-bold mb-3">{{ __("Integrazione Nativa dell'Intelligenza Artificiale") }}</h5>
                        <p class="text-muted">
                            {{ __('ChatBot intelligenti, automazione dei processi e analisi predittiva integrate nativamente nei tuoi sistemi.') }}
                        </p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-center align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <small class="text-muted">{{ __('Tecnologie avanzate') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sicurezza Inclusa -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 text-center hover-lift">
                    <div class="card-body p-4">
                        <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="bi bi-shield-check-fill fs-1"></i>
                        </div>
                        <h5 class="fw-bold mb-3">{{ __('Sicurezza Informatica Inclusa') }}</h5>
                        <p class="text-muted">
                            {{ __('Ogni progetto include standard di sicurezza enterprise, crittografia avanzata e compliance GDPR automatica.') }}
                        </p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-center align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <small class="text-muted">{{ __('Protezione garantita') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comunicazione Diretta -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 text-center hover-lift">
                    <div class="card-body p-4">
                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="bi bi-chat-dots-fill fs-1"></i>
                        </div>
                        <h5 class="fw-bold mb-3">{{ __('Comunicazione Diretta e Veloce') }}</h5>
                        <p class="text-muted">
                            {{ __('Contatto diretto con Davide Cavallini. Risposte rapide, aggiornamenti costanti e massima trasparenza nel processo.') }}
                        </p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-center align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <small class="text-muted">{{ __('Risposta in 24h') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Bottom -->
        <div class="text-center mt-5">
            <h4 class="fw-bold mb-3">{{ __('Pronto a Trasformare la Tua Idea in Realtà?') }}</h4>
            <p class="text-muted mb-4">{{ __('Scopri come CavalliniService può accelerare il successo del tuo business') }}</p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="/preventivo" class="btn btn-principale btn-lg px-5 py-3">
                    <i class="bi bi-calculator me-2"></i>{{ __('Richiedi Preventivo') }}
                </a>
                <a href="/chi-siamo" class="btn btn-principale btn-lg px-5 py-3">
                    <i class="bi bi-person-circle me-2"></i>{{ __('Scopri Chi Siamo') }}
                </a>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="text-center mb-5">
                    <h2 class="fw-bold mb-3">{{ __('Domande Frequenti') }}</h2>
                    <p class="lead text-muted">{{ __('Le risposte alle domande più comuni sui nostri servizi') }}</p>
                </div>

                <div class="accordion" id="faqAccordion">
                    <!-- FAQ 1: Costo -->
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="false">
                                <i class="bi bi-currency-euro text-primary me-3 fs-5"></i>
                                {{ __('Quanto costa un sito web su misura?') }}
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p class="mb-3">
                                    {{ __('Il costo varia in base alla complessità e alle funzionalità richieste. I nostri progetti partono da:') }}
                                </p>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-light rounded">
                                            <h6 class="fw-bold text-primary">{{ __('Sito Vetrina') }}</h6>
                                            <p class="h5 text-success mb-1">€2.500 - €5.000</p>
                                            <small class="text-muted">{{ __('5-10 pagine responsive') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-light rounded">
                                            <h6 class="fw-bold text-primary">{{ __('E-commerce') }}</h6>
                                            <p class="h5 text-success mb-1">€5.000 - €15.000</p>
                                            <small class="text-muted">{{ __('Gestione prodotti completa') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-light rounded">
                                            <h6 class="fw-bold text-primary">{{ __('Piattaforma Custom') }}</h6>
                                            <p class="h5 text-success mb-1">€15.000+</p>
                                            <small class="text-muted">{{ __('Funzionalità avanzate + IA') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 p-3 bg-primary bg-opacity-10 rounded">
                                    <i class="bi bi-info-circle text-primary me-2"></i>
                                    <strong>{{ __('Incluso sempre:') }}</strong> {{ __('Hosting primo anno, SSL, backup automatici, supporto post-lancio 3 mesi.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 2: Tempi -->
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false">
                                <i class="bi bi-clock-history text-warning me-3 fs-5"></i>
                                {{ __('Quanto tempo serve per realizzare un progetto?') }}
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p class="mb-3">
                                    {{ __('I tempi dipendono dalla complessità del progetto e dalla velocità di feedback del cliente:') }}
                                </p>
                                <div class="timeline-faq">
                                    <div class="d-flex mb-3">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                                            <span class="small fw-bold">1-2</span>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">{{ __('Settimane - Sito Vetrina') }}</h6>
                                            <p class="small text-muted mb-0">{{ __('Landing page, sito aziendale semplice, blog') }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex mb-3">
                                        <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                                            <span class="small fw-bold">3-6</span>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">{{ __('Settimane - E-commerce/Gestionale') }}</h6>
                                            <p class="small text-muted mb-0">{{ __('Shop online, CRM, sistema gestionale personalizzato') }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                                            <span class="small fw-bold">2-4</span>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">{{ __('Mesi - Progetti Complessi') }}</h6>
                                            <p class="small text-muted mb-0">{{ __('Piattaforme con IA, integrazione sistemi enterprise') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 p-3 bg-warning bg-opacity-10 rounded">
                                    <i class="bi bi-lightning text-warning me-2"></i>
                                    <strong>{{ __('Sviluppo Agile:') }}</strong> {{ __('Rilasci incrementali ogni 1-2 settimane per feedback continuo.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 3: Modifiche -->
                    <div class="accordion-item border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false">
                                <i class="bi bi-pencil-square text-info me-3 fs-5"></i>
                                {{ __('Posso richiedere modifiche dopo il rilascio?') }}
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p class="mb-3">
                                    {{ __('Assolutamente! Offriamo diversi livelli di supporto post-lancio:') }}
                                </p>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <div class="p-3 border border-success rounded">
                                            <h6 class="fw-bold text-success mb-2">
                                                <i class="bi bi-gift me-2"></i>{{ __('Supporto Gratuito') }}
                                            </h6>
                                            <ul class="small mb-0">
                                                <li>{{ __('3 mesi inclusi nel progetto') }}</li>
                                                <li>{{ __('Bug fix e correzioni tecniche') }}</li>
                                                <li>{{ __('Piccole modifiche di contenuto') }}</li>
                                                <li>{{ __('Assistenza tecnica base') }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 border border-primary rounded">
                                            <h6 class="fw-bold text-primary mb-2">
                                                <i class="bi bi-tools me-2"></i>{{ __('Modifiche Aggiuntive') }}
                                            </h6>
                                            <ul class="small mb-0">
                                                <li>{{ __('Nuove funzionalità su richiesta') }}</li>
                                                <li>{{ __('Restyling grafici') }}</li>
                                                <li>{{ __('Integrazioni con nuovi servizi') }}</li>
                                                <li>{{ __('Preventivo dedicato per ogni modifica') }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 bg-info bg-opacity-10 rounded">
                                    <i class="bi bi-headset text-info me-2"></i>
                                    <strong>{{ __('Supporto Continuo:') }}</strong> {{ __('Puoi sempre contattarmi per assistenza. Risposta garantita entro 24 ore.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA FAQ -->
                <div class="text-center mt-5">
                    <h5 class="fw-bold mb-3">{{ __('Hai altre domande?') }}</h5>
                    <p class="text-muted mb-4">{{ __('Contattami direttamente per una consulenza personalizzata e gratuita') }}</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="/preventivo" class="btn btn-principale">
                            <i class="bi bi-calculator me-2"></i>{{ __('Richiedi Preventivo') }}
                        </a>
                        <a href="https://wa.me/393791264458?text={{ urlencode(__('Ciao, ho alcune domande sui vostri servizi.')) }}" 
                           class="btn btn-success" target="_blank">
                            <i class="bi bi-whatsapp me-2"></i>{{ __('WhatsApp Diretto') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonial Section con Avatar Piccoli -->
<section class="py-5 bg-secondary bg-opacity-25">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold text-white mb-3">
                {{ __('Cosa Dicono i Clienti') }} <span class="testo-arcobaleno">⭐</span>
            </h2>
            <p class="lead text-white">{{ __('Feedback reali da progetti completati con successo') }}</p>
        </div>
        
        <div class="row g-4">
            <!-- Testimonial 1 -->
            <div class="col-lg-4">
                <div class="card bg-dark border-0 shadow-lg h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <!-- Avatar piccolo ottimizzato con bagliore -->
                            <img src="{{ asset('logo-icon.png') }}" 
                                 alt="Cliente" 
                                 class="avatar-small avatar-sm me-3 shadow-outline" style="border: 2px solid white;">
                            <div>
                                <h6 class="fw-bold text-white mb-0">{{ __('Marco R.') }}</h6>
                                <small class="text-muted">{{ __('E-commerce Fashion') }}</small>
                            </div>
                        </div>
                        <p class="text-light">{{ __('Sito velocissimo e vendite raddoppiate in 3 mesi. Davide ha capito esattamente cosa serviva al mio business.') }}</p>
                        <div class="text-warning">⭐⭐⭐⭐⭐</div>
                    </div>
                </div>
            </div>
            
            <!-- Testimonial 2 -->
            <div class="col-lg-4">
                <div class="card bg-dark border-0 shadow-lg h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <!-- Avatar piccolo ottimizzato con bagliore -->
                            <img src="{{ asset('logo-icon.png') }}" 
                                 alt="Cliente" 
                                 class="avatar-small avatar-sm me-3 shadow-outline" style="border: 2px solid white;">
                            <div>
                                <h6 class="fw-bold text-white mb-0">{{ __('Laura T.') }}</h6>
                                <small class="text-muted">{{ __('Studio Professionale') }}</small>
                            </div>
                        </div>
                        <p class="text-light">{{ __('Gestionale personalizzato che ha rivoluzionato il nostro lavoro quotidiano. Supporto sempre disponibile.') }}</p>
                        <div class="text-warning">⭐⭐⭐⭐⭐</div>
                    </div>
                </div>
            </div>
            
            <!-- Testimonial 3 -->
            <div class="col-lg-4">
                <div class="card bg-dark border-0 shadow-lg h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <!-- Avatar piccolo ottimizzato con bagliore -->
                            <img src="{{ asset('logo-icon.png') }}" 
                                 alt="Cliente" 
                                 class="avatar-small avatar-sm me-3 shadow-outline" style="border: 2px solid white;">
                            <div>
                                <h6 class="fw-bold text-white mb-0">{{ __('Alessandro M.') }}</h6>
                                <small class="text-muted">{{ __('Startup Tech') }}</small>
                            </div>
                        </div>
                        <p class="text-light">{{ __('Audit di sicurezza approfondito e miglioramenti sostanziali. Ora dormo sonni tranquilli.') }}</p>
                        <div class="text-warning">⭐⭐⭐⭐⭐</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>