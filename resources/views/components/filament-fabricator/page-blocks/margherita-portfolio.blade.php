<!-- Hero Section -->
<section class="hero-section bg-primary text-white position-relative overflow-hidden py-5">
    <div class="container py-5">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4 text-gradient">
                    {{ __('Portfolio Progetti') }}
                </h1>
                <p class="lead mb-4">
                    {{ __('Scopri i progetti reali realizzati per aziende italiane ed europee: siti web, software gestionali, sistemi IA e soluzioni di sicurezza informatica.') }}
                </p>
                <div class="hero-badges d-flex flex-wrap justify-content-center gap-3">
                    <span class="badge bg-light text-dark px-3 py-2">{{ __('50+ Progetti Completati') }}</span>
                    <span class="badge bg-light text-dark px-3 py-2">{{ __('100% Soddisfazione Clienti') }}</span>
                    <span class="badge bg-light text-dark px-3 py-2">{{ __('3 Anni di Esperienza') }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-decoration"></div>
</section>

<!-- Statistiche Portfolio -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="bi bi-globe fs-1 text-primary mb-3"></i>
                        <h3 class="fw-bold text-primary">52</h3>
                        <p class="text-muted mb-0">{{ __('Siti Web Realizzati') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="bi bi-gear-fill fs-1 text-success mb-3"></i>
                        <h3 class="fw-bold text-success">18</h3>
                        <p class="text-muted mb-0">{{ __('Software Gestionali') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="bi bi-shield-check fs-1 text-warning mb-3"></i>
                        <h3 class="fw-bold text-warning">25</h3>
                        <p class="text-muted mb-0">{{ __('Audit di Sicurezza') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="bi bi-cpu fs-1 text-info mb-3"></i>
                        <h3 class="fw-bold text-info">12</h3>
                        <p class="text-muted mb-0">{{ __('Progetti IA') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Casi Studio Principali -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-3">{{ __('Casi Studio Principali') }}</h2>
            <p class="lead text-muted">{{ __('Progetti che hanno trasformato il business dei nostri clienti') }}</p>
        </div>

        <!-- Caso Studio 1: E-commerce Fashion -->
        <div class="row mb-5 align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-shop fs-4 me-3"></i>
                            <div>
                                <h5 class="mb-1">{{ __('E-commerce Fashion Premium') }}</h5>
                                <small>{{ __('Milano Fashion District') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="position-relative mb-3">
                            <!-- Mockup E-commerce -->
                            <div class="bg-gradient-primary p-4 rounded text-white text-center" style="height: 200px;">
                                <i class="bi bi-laptop fs-1 mb-3"></i>
                                <h6>{{ __('Piattaforma E-commerce') }}</h6>
                                <p class="small mb-0">{{ __('Design responsive, pagamenti sicuri, gestione inventario') }}</p>
                            </div>
                        </div>
                        <h6 class="fw-bold text-primary">{{ __('Problema Risolto:') }}</h6>
                        <p class="text-muted small mb-3">{{ __('Boutique fashion con vendite solo fisiche voleva espandere online mantenendo esperienza luxury.') }}</p>
                        
                        <h6 class="fw-bold text-success">{{ __('Risultati Ottenuti:') }}</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-4 text-center">
                                <h6 class="text-success fw-bold">+340%</h6>
                                <small>{{ __('Vendite Online') }}</small>
                            </div>
                            <div class="col-4 text-center">
                                <h6 class="text-success fw-bold">€180K</h6>
                                <small>{{ __('Fatturato Mensile') }}</small>
                            </div>
                            <div class="col-4 text-center">
                                <h6 class="text-success fw-bold">2.1s</h6>
                                <small>{{ __('Tempo Caricamento') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ps-lg-4">
                    <h4 class="fw-bold mb-4">{{ __('Tecnologie Implementate') }}</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary text-white rounded-circle p-2 me-3">
                                    <i class="bi bi-code-slash"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ __('Frontend Avanzato') }}</h6>
                                    <small class="text-muted">React.js, Bootstrap 5</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success text-white rounded-circle p-2 me-3">
                                    <i class="bi bi-server"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ __('Backend Scalabile') }}</h6>
                                    <small class="text-muted">Django, PostgreSQL</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-warning text-white rounded-circle p-2 me-3">
                                    <i class="bi bi-credit-card"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ __('Pagamenti Sicuri') }}</h6>
                                    <small class="text-muted">Stripe, PayPal, Klarna</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-info text-white rounded-circle p-2 me-3">
                                    <i class="bi bi-robot"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ __('ChatBot IA') }}</h6>
                                    <small class="text-muted">GPT-4, Supporto 24/7</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h6 class="fw-bold mb-3">{{ __('Caratteristiche Uniche:') }}</h6>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Virtual Try-On con AR') }}</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Raccomandazioni IA personalizzate') }}</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Integrazione social media') }}</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Analytics avanzate') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Caso Studio 2: Software Gestionale Medicale -->
        <div class="row mb-5 align-items-center flex-row-reverse">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-success text-white">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-hospital fs-4 me-3"></i>
                            <div>
                                <h5 class="mb-1">{{ __('Gestionale Clinica Medica') }}</h5>
                                <small>{{ __('Veneto Healthcare Group') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="position-relative mb-3">
                            <!-- Mockup Gestionale -->
                            <div class="bg-gradient-success p-4 rounded text-white text-center" style="height: 200px;">
                                <i class="bi bi-clipboard-data fs-1 mb-3"></i>
                                <h6>{{ __('Sistema Gestionale Completo') }}</h6>
                                <p class="small mb-0">{{ __('Cartelle cliniche, agenda, fatturazione, GDPR compliant') }}</p>
                            </div>
                        </div>
                        <h6 class="fw-bold text-primary">{{ __('Problema Risolto:') }}</h6>
                        <p class="text-muted small mb-3">{{ __('Clinica privata con 5 sedi gestiva tutto manualmente: appuntamenti, cartelle, fatturazione dispersi su Excel e carta.') }}</p>
                        
                        <h6 class="fw-bold text-success">{{ __('Risultati Ottenuti:') }}</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-4 text-center">
                                <h6 class="text-success fw-bold">-85%</h6>
                                <small>{{ __('Tempo Amministrativo') }}</small>
                            </div>
                            <div class="col-4 text-center">
                                <h6 class="text-success fw-bold">+45%</h6>
                                <small>{{ __('Produttività') }}</small>
                            </div>
                            <div class="col-4 text-center">
                                <h6 class="text-success fw-bold">100%</h6>
                                <small>{{ __('Compliance GDPR') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="pe-lg-4">
                    <h4 class="fw-bold mb-4">{{ __('Moduli Implementati') }}</h4>
                    
                    <div class="accordion" id="moduliAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#modulo1">
                                    <i class="bi bi-calendar-check me-2"></i>{{ __('Gestione Agenda') }}
                                </button>
                            </h2>
                            <div id="modulo1" class="accordion-collapse collapse show" data-bs-parent="#moduliAccordion">
                                <div class="accordion-body">
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check text-success me-2"></i>{{ __('Prenotazioni online pazienti') }}</li>
                                        <li><i class="bi bi-check text-success me-2"></i>{{ __('Sincronizzazione multi-sede') }}</li>
                                        <li><i class="bi bi-check text-success me-2"></i>{{ __('Reminder automatici SMS/Email') }}</li>
                                        <li><i class="bi bi-check text-success me-2"></i>{{ __('Gestione liste d\'attesa') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#modulo2">
                                    <i class="bi bi-folder-fill me-2"></i>{{ __('Cartelle Cliniche') }}
                                </button>
                            </h2>
                            <div id="modulo2" class="accordion-collapse collapse" data-bs-parent="#moduliAccordion">
                                <div class="accordion-body">
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check text-success me-2"></i>{{ __('Cartelle elettroniche sicure') }}</li>
                                        <li><i class="bi bi-check text-success me-2"></i>{{ __('Prescrizioni digitali') }}</li>
                                        <li><i class="bi bi-check text-success me-2"></i>{{ __('Archivio documenti crittografato') }}</li>
                                        <li><i class="bi bi-check text-success me-2"></i>{{ __('Ricerca veloce e filtri avanzati') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#modulo3">
                                    <i class="bi bi-receipt me-2"></i>{{ __('Fatturazione Automatica') }}
                                </button>
                            </h2>
                            <div id="modulo3" class="accordion-collapse collapse" data-bs-parent="#moduliAccordion">
                                <div class="accordion-body">
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check text-success me-2"></i>{{ __('Fatturazione elettronica automatica') }}</li>
                                        <li><i class="bi bi-check text-success me-2"></i>{{ __('Integrazione con commercialista') }}</li>
                                        <li><i class="bi bi-check text-success me-2"></i>{{ __('Report finanziari real-time') }}</li>
                                        <li><i class="bi bi-check text-success me-2"></i>{{ __('Gestione pagamenti e scadenze') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Caso Studio 3: Audit Cybersecurity -->
        <div class="row mb-5 align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-danger text-white">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-shield-exclamation fs-4 me-3"></i>
                            <div>
                                <h5 class="mb-1">{{ __('Audit Cybersecurity Completo') }}</h5>
                                <small>{{ __('Azienda Manifatturiera 300 dipendenti') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="position-relative mb-3">
                            <!-- Mockup Security -->
                            <div class="bg-gradient-danger p-4 rounded text-white text-center" style="height: 200px;">
                                <i class="bi bi-bug fs-1 mb-3"></i>
                                <h6>{{ __('Penetration Test Professionale') }}</h6>
                                <p class="small mb-0">{{ __('OWASP methodology, 68 vulnerabilità identificate e risolte') }}</p>
                            </div>
                        </div>
                        <h6 class="fw-bold text-primary">{{ __('Problema Risolto:') }}</h6>
                        <p class="text-muted small mb-3">{{ __('Azienda manifatturiera aveva subito tentativi di intrusione e voleva audit completo prima di certificazione ISO 27001.') }}</p>
                        
                        <h6 class="fw-bold text-success">{{ __('Risultati Ottenuti:') }}</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-4 text-center">
                                <h6 class="text-success fw-bold">68</h6>
                                <small>{{ __('Vulnerabilità Risolte') }}</small>
                            </div>
                            <div class="col-4 text-center">
                                <h6 class="text-success fw-bold">ISO</h6>
                                <small>{{ __('27001 Ottenuta') }}</small>
                            </div>
                            <div class="col-4 text-center">
                                <h6 class="text-success fw-bold">€0</h6>
                                <small>{{ __('Breach in 18 mesi') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ps-lg-4">
                    <h4 class="fw-bold mb-4">{{ __('Fasi dell\'Audit') }}</h4>
                    
                    <div class="timeline">
                        <div class="d-flex mb-4">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                <strong>1</strong>
                            </div>
                            <div class="ms-3">
                                <h6 class="fw-bold">{{ __('Assessment Iniziale') }}</h6>
                                <p class="text-muted small mb-0">{{ __('Analisi infrastruttura, mappatura asset, identificazione superfici di attacco') }}</p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-4">
                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                <strong>2</strong>
                            </div>
                            <div class="ms-3">
                                <h6 class="fw-bold">{{ __('Penetration Testing') }}</h6>
                                <p class="text-muted small mb-0">{{ __('Test automatici e manuali, exploit di vulnerabilità critiche, lateral movement') }}</p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-4">
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                <strong>3</strong>
                            </div>
                            <div class="ms-3">
                                <h6 class="fw-bold">{{ __('Report Dettagliato') }}</h6>
                                <p class="text-muted small mb-0">{{ __('Executive summary, dettagli tecnici, piano di remediation prioritizzato') }}</p>
                            </div>
                        </div>
                        
                        <div class="d-flex">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                <strong>4</strong>
                            </div>
                            <div class="ms-3">
                                <h6 class="fw-bold">{{ __('Implementazione Sicurezza') }}</h6>
                                <p class="text-muted small mb-0">{{ __('Assistenza correzione vulnerabilità, training team, certificazione ISO 27001') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonianze Clienti -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-3">{{ __('Testimonianze Clienti') }}</h2>
            <p class="lead text-muted">{{ __('Cosa dicono le aziende che hanno scelto CavalliniService') }}</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <blockquote class="blockquote">
                            <p class="mb-3">"{{ __('Il nostro e-commerce ha triplicato le vendite in 6 mesi. Davide ha capito perfettamente le nostre esigenze e creato una piattaforma che rispecchia la qualità del nostro brand.') }}"</p>
                        </blockquote>
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div class="text-start">
                                <h6 class="mb-1">Maria Rossi</h6>
                                <small class="text-muted">{{ __('CEO, Milano Fashion') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <blockquote class="blockquote">
                            <p class="mb-3">"{{ __('Il gestionale ha rivoluzionato la nostra clinica. Abbiamo risparmiato 15 ore settimanali di lavoro amministrativo e migliorato l\'esperienza dei pazienti.') }}"</p>
                        </blockquote>
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div class="text-start">
                                <h6 class="mb-1">Dr. Luca Bianchi</h6>
                                <small class="text-muted">{{ __('Direttore, Clinica Veneto') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <blockquote class="blockquote">
                            <p class="mb-3">"{{ __('L\'audit di sicurezza è stato fondamentale per ottenere la certificazione ISO 27001. Professionalità e competenza al top level.') }}"</p>
                        </blockquote>
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div class="text-start">
                                <h6 class="mb-1">Marco Verdi</h6>
                                <small class="text-muted">{{ __('CTO, TechManufacturing') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Final -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-3">{{ __('Pronto per il Tuo Prossimo Progetto?') }}</h2>
                <p class="lead mb-4">
                    {{ __('Trasforma la tua idea in realtà con soluzioni tecnologiche su misura. Contattami per una consulenza gratuita e scopri come posso aiutare la tua azienda a crescere.') }}
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-column gap-3">
                    <a href="/contatti" class="btn btn-light btn-lg">
                        <i class="bi bi-person-circle me-2"></i>{{ __('Consulenza Gratuita') }}
                    </a>
                    <a href="https://wa.me/393791264458?text={{ urlencode(__('Ciao, ho visto il tuo portfolio e vorrei discutere di un progetto.')) }}" 
                       class="btn btn-success btn-lg" target="_blank">
                        <i class="bi bi-whatsapp me-2"></i>{{ __('WhatsApp Diretto') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>