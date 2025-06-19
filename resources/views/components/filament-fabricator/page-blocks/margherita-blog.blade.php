<!-- Hero Section -->
<section class="hero-section bg-primary text-white position-relative overflow-hidden py-5">
    <div class="container py-5">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4 text-gradient">
                    {{ __('Approfondimenti') }}
                </h1>
                <p class="lead mb-4">
                    {{ __('Guide tecniche, case study e best practice dal mondo della tecnologia e della sicurezza informatica.') }}
                </p>
                <div class="hero-badges d-flex flex-wrap justify-content-center gap-3">
                    <span class="badge bg-light text-dark px-3 py-2">{{ __('Intelligenza Artificiale') }}</span>
                    <span class="badge bg-light text-dark px-3 py-2">{{ __('Cybersecurity') }}</span>
                    <span class="badge bg-light text-dark px-3 py-2">{{ __('Sviluppo Web') }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-decoration"></div>
</section>

<!-- Articoli Blog -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Articolo 1: IA nei Siti Web -->
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-cpu-fill fs-4 me-3"></i>
                            <div>
                                <span class="badge bg-light text-dark">{{ __('Intelligenza Artificiale') }}</span>
                                <small class="d-block mt-1 opacity-75">{{ __('5 min di lettura') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold">
                            {{ __('Come l\'IA Rivoluziona i Siti Web Moderni') }}
                        </h5>
                        <p class="card-text text-muted">
                            {{ __('Scopri come ChatBot intelligenti, personalizzazione dinamica e analisi predittiva trasformano l\'esperienza utente online.') }}
                        </p>
                        <div class="mt-auto">
                            <h6 class="fw-bold text-primary mb-2">{{ __('Argomenti trattati:') }}</h6>
                            <ul class="list-unstyled small">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('ChatBot con NLP avanzato') }}</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Personalizzazione contenuti') }}</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Analisi comportamentale') }}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <a href="/blog/ia-siti-web" class="btn btn-outline-primary w-100">
                            {{ __('Leggi l\'articolo completo') }} <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Articolo 2: Gestione Dati Sicura -->
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-header bg-gradient-success text-white">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-shield-lock-fill fs-4 me-3"></i>
                            <div>
                                <span class="badge bg-light text-dark">{{ __('Data Security') }}</span>
                                <small class="d-block mt-1 opacity-75">{{ __('7 min di lettura') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold">
                            {{ __('Gestione Sicura dei Dati Aziendali') }}
                        </h5>
                        <p class="card-text text-muted">
                            {{ __('Strategie avanzate per proteggere informazioni sensibili, garantire compliance GDPR e implementare backup sicuri.') }}
                        </p>
                        <div class="mt-auto">
                            <h6 class="fw-bold text-success mb-2">{{ __('Argomenti trattati:') }}</h6>
                            <ul class="list-unstyled small">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Crittografia end-to-end') }}</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Compliance GDPR') }}</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Backup automatizzati') }}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <a href="/blog/gestione-dati-sicura" class="btn btn-outline-success w-100">
                            {{ __('Leggi l\'articolo completo') }} <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Articolo 3: Come Funziona un Pentest -->
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-header bg-gradient-danger text-white">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-bug-fill fs-4 me-3"></i>
                            <div>
                                <span class="badge bg-light text-dark">{{ __('Penetration Testing') }}</span>
                                <small class="d-block mt-1 opacity-75">{{ __('10 min di lettura') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold">
                            {{ __('Come Funziona un Penetration Test') }}
                        </h5>
                        <p class="card-text text-muted">
                            {{ __('Metodologie professionali, strumenti avanzati e fasi operative di un test di penetrazione completo ed efficace.') }}
                        </p>
                        <div class="mt-auto">
                            <h6 class="fw-bold text-danger mb-2">{{ __('Argomenti trattati:') }}</h6>
                            <ul class="list-unstyled small">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Metodologia OWASP') }}</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Tool professionali') }}</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>{{ __('Report dettagliati') }}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <a href="/blog/come-funziona-penetration-test" class="btn btn-outline-danger w-100">
                            {{ __('Leggi l\'articolo completo') }} <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="fw-bold mb-3">{{ __('Hai domande specifiche?') }}</h2>
                <p class="lead text-muted mb-4">
                    {{ __('Contattami per consulenze personalizzate su progetti di sviluppo, sicurezza informatica e integrazione IA.') }}
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="/contatti" class="btn btn-primary btn-lg">
                        <i class="bi bi-person-circle me-2"></i>{{ __('Contattami') }}
                    </a>
                    <a href="https://wa.me/393791264458?text={{ urlencode(__('Ciao, ho letto i tuoi approfondimenti tecnici e vorrei una consulenza personalizzata.')) }}" 
                       class="btn btn-success btn-lg" target="_blank">
                        <i class="bi bi-whatsapp me-2"></i>{{ __('WhatsApp') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h3 class="fw-bold mb-3">{{ __('Resta aggiornato') }}</h3>
                <p class="mb-0">{{ __('Ricevi notifiche sui nuovi articoli tecnici e guide pratiche.') }}</p>
            </div>
            <div class="col-lg-6">
                <div class="input-group">
                    <input type="email" class="form-control" placeholder="{{ __('Il tuo indirizzo email') }}">
                    <button class="btn btn-light" type="button">
                        <i class="bi bi-envelope-fill me-2"></i>{{ __('Iscriviti') }}
                    </button>
                </div>
                <small class="text-light opacity-75 d-block mt-2">
                    {{ __('Nessuno spam. Solo contenuti di qualità.') }}
                </small>
            </div>
        </div>
    </div>
</section>