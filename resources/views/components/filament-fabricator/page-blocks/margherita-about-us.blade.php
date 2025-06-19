<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active">{{ __('Chi Siamo') }}</li>
        </ol>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white position-relative overflow-hidden">
    <div class="container py-5">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4 text-gradient">
                    {{ __('Chi Siamo') }}
                </h1>
                <p class="lead mb-4">
                    {{ __('La storia di passione, innovazione e dedizione che ha portato alla nascita di CavalliniService, leader nelle soluzioni tecnologiche per aziende moderne.') }}
                </p>
                <div class="hero-badges d-flex flex-wrap gap-3">
                    <span class="badge bg-light text-dark px-3 py-2">{{ __('Dal 2021') }}</span>
                    <span class="badge bg-light text-dark px-3 py-2">{{ __('100+ Progetti') }}</span>
                    <span class="badge bg-light text-dark px-3 py-2">{{ __('Expertise Certificata') }}</span>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="position-relative">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center shadow-lg" style="width: 300px; height: 300px;">
                        <!-- Logo ottimizzato per dark mode con bagliore colorato -->
                        <img src="{{ asset('logo-dark.png') }}" alt="CavalliniService - Davide Cavallini" class="rounded-circle shadow-outline-colored" style="width: 250px; height: 250px; object-fit: cover;">
                    </div>
                    <div class="position-absolute top-0 start-0 w-100 h-100 rounded-circle" style="background: linear-gradient(45deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1)); animation: pulse 3s infinite;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-decoration"></div>
</section>

<!-- La Mia Storia -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="text-center mb-5">
                    <h2 class="fw-bold mb-3">{{ __('La Mia Storia') }}</h2>
                    <p class="lead text-muted">{{ __('Un percorso di crescita continua nel mondo della tecnologia') }}</p>
                </div>

                <!-- Timeline -->
                <div class="timeline-container">
                    <!-- Inizio Percorso -->
                    <div class="row mb-5">
                        <div class="col-md-6 order-md-1">
                            <div class="card border-primary h-100 shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-mortarboard-fill fs-4 me-3"></i>
                                        <div>
                                            <h5 class="mb-1">{{ __('Gli Inizi') }}</h5>
                                            <small>{{ __('2019-2021') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        {{ __('La passione per la tecnologia nasce fin da giovane. Durante gli studi universitari in Informatica, mi specializzo in cybersecurity e sviluppo web, partecipando a progetti open source e acquisendo le prime certificazioni tecniche.') }}
                                    </p>
                                    <ul class="list-unstyled small">
                                        <li><i class="bi bi-check-circle text-success me-2"></i>{{ __('Laurea in Informatica') }}</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>{{ __('Certificazioni Security+') }}</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>{{ __('Prime collaborazioni freelance') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 order-md-2 d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                    <i class="bi bi-rocket-takeoff fs-1"></i>
                                </div>
                                <h6 class="fw-bold">{{ __('Primo Passo') }}</h6>
                            </div>
                        </div>
                    </div>

                    <!-- Nascita CavalliniService -->
                    <div class="row mb-5">
                        <div class="col-md-6 order-md-2">
                            <div class="card border-success h-100 shadow-sm">
                                <div class="card-header bg-success text-white">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-building-fill fs-4 me-3"></i>
                                        <div>
                                            <h5 class="mb-1">{{ __('Nascita CavalliniService') }}</h5>
                                            <small>{{ __('2021-2022') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        {{ __('Fondo ufficialmente CavalliniService con la missione di rendere la tecnologia accessibile alle PMI italiane. I primi progetti riguardano siti web e sistemi gestionali, con un focus particolare sulla sicurezza e user experience.') }}
                                    </p>
                                    <ul class="list-unstyled small">
                                        <li><i class="bi bi-check-circle text-success me-2"></i>{{ __('Apertura P.IVA 04829980265') }}</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>{{ __('Primi 20 progetti completati') }}</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>{{ __('Partnership tecnologiche') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 order-md-1 d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                    <i class="bi bi-trophy-fill fs-1"></i>
                                </div>
                                <h6 class="fw-bold">{{ __('Fondazione') }}</h6>
                            </div>
                        </div>
                    </div>

                    <!-- Espansione e Specializzazione -->
                    <div class="row mb-5">
                        <div class="col-md-6 order-md-1">
                            <div class="card border-warning h-100 shadow-sm">
                                <div class="card-header bg-warning text-dark">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-graph-up-arrow fs-4 me-3"></i>
                                        <div>
                                            <h5 class="mb-1">{{ __('Crescita e Innovazione') }}</h5>
                                            <small>{{ __('2022-2024') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        {{ __('Espando i servizi verso intelligenza artificiale e cybersecurity avanzata. Implemento i primi progetti con IA per automatizzazione e chatbot, mentre consolido la reputazione nel penetration testing con metodologie OWASP certificate.') }}
                                    </p>
                                    <ul class="list-unstyled small">
                                        <li><i class="bi bi-check-circle text-success me-2"></i>{{ __('Certificazione OWASP') }}</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>{{ __('50+ progetti IA implementati') }}</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>{{ __('Team di collaboratori') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 order-md-2 d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                    <i class="bi bi-cpu-fill fs-1"></i>
                                </div>
                                <h6 class="fw-bold">{{ __('Innovazione') }}</h6>
                            </div>
                        </div>
                    </div>

                    <!-- Oggi -->
                    <div class="row">
                        <div class="col-md-6 order-md-2">
                            <div class="card border-info h-100 shadow-sm">
                                <div class="card-header bg-info text-white">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-stars fs-4 me-3"></i>
                                        <div>
                                            <h5 class="mb-1">{{ __('Oggi e Futuro') }}</h5>
                                            <small>{{ __('2024-2025') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        {{ __('CavalliniService è riconosciuta come partner tecnologico di fiducia per oltre 100 aziende. Continuo a investire in ricerca e sviluppo, con focus su IA generativa, quantum computing e sostenibilità digitale.') }}
                                    </p>
                                    <ul class="list-unstyled small">
                                        <li><i class="bi bi-check-circle text-success me-2"></i>{{ __('100+ clienti soddisfatti') }}</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>{{ __('Partnership internazionali') }}</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>{{ __('Ricerca e sviluppo continua') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 order-md-1 d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                    <i class="bi bi-infinity fs-1"></i>
                                </div>
                                <h6 class="fw-bold">{{ __('Futuro') }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Missione e Valori -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="text-center mb-5">
                    <h2 class="fw-bold mb-3">{{ __('Missione e Valori') }}</h2>
                    <p class="lead text-muted">{{ __('I principi che guidano ogni decisione e progetto') }}</p>
                </div>

                <!-- Missione -->
                <div class="row mb-5">
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center p-5">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 100px; height: 100px;">
                                    <i class="bi bi-bullseye fs-1"></i>
                                </div>
                                <h4 class="fw-bold mb-3">{{ __('La Nostra Missione') }}</h4>
                                <p class="text-muted">
                                    {{ __('Rendere la tecnologia un vantaggio competitivo accessibile per ogni azienda, indipendentemente dalle dimensioni. Creiamo soluzioni innovative che trasformano le sfide in opportunità di crescita.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center p-5">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 100px; height: 100px;">
                                    <i class="bi bi-eye-fill fs-1"></i>
                                </div>
                                <h4 class="fw-bold mb-3">{{ __('La Nostra Visione') }}</h4>
                                <p class="text-muted">
                                    {{ __('Essere il partner tecnologico di riferimento per le aziende che vogliono innovare responsabilmente, costruendo un futuro digitale sostenibile e sicuro per tutti.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Valori Core -->
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-lightbulb-fill fs-2"></i>
                            </div>
                            <h6 class="fw-bold">{{ __('Innovazione') }}</h6>
                            <p class="small text-muted">{{ __('Tecnologie all\'avanguardia per soluzioni uniche') }}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-gem fs-2"></i>
                            </div>
                            <h6 class="fw-bold">{{ __('Qualità') }}</h6>
                            <p class="small text-muted">{{ __('Standard elevati in ogni dettaglio') }}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-shield-check-fill fs-2"></i>
                            </div>
                            <h6 class="fw-bold">{{ __('Sicurezza') }}</h6>
                            <p class="small text-muted">{{ __('Protezione dati e privacy garantite') }}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-people-fill fs-2"></i>
                            </div>
                            <h6 class="fw-bold">{{ __('Partnership') }}</h6>
                            <p class="small text-muted">{{ __('Relazioni durature basate sulla fiducia') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Competenze e Specializzazioni -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="text-center mb-5">
                    <h2 class="fw-bold mb-3">{{ __('Competenze e Specializzazioni') }}</h2>
                    <p class="lead text-muted">{{ __('Expertise tecniche e certificazioni professionali') }}</p>
                </div>

                <div class="row g-4">
                    <!-- Sviluppo Web -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-code-slash me-2"></i>{{ __('Sviluppo Web') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small">Python/Django</span>
                                        <span class="small">95%</span>
                                    </div>
                                    <div class="progress mb-2" style="height: 6px;">
                                        <div class="progress-bar bg-primary" style="width: 95%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small">JavaScript/React</span>
                                        <span class="small">90%</span>
                                    </div>
                                    <div class="progress mb-2" style="height: 6px;">
                                        <div class="progress-bar bg-info" style="width: 90%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small">PHP/Laravel</span>
                                        <span class="small">85%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-warning" style="width: 85%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cybersecurity -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-shield-lock me-2"></i>{{ __('Cybersecurity') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small">Penetration Testing</span>
                                        <span class="small">92%</span>
                                    </div>
                                    <div class="progress mb-2" style="height: 6px;">
                                        <div class="progress-bar bg-danger" style="width: 92%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small">OWASP Metodologie</span>
                                        <span class="small">88%</span>
                                    </div>
                                    <div class="progress mb-2" style="height: 6px;">
                                        <div class="progress-bar bg-warning" style="width: 88%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small">Compliance GDPR</span>
                                        <span class="small">95%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: 95%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Intelligenza Artificiale -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-cpu me-2"></i>{{ __('Intelligenza Artificiale') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small">Machine Learning</span>
                                        <span class="small">87%</span>
                                    </div>
                                    <div class="progress mb-2" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: 87%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small">NLP/ChatBot</span>
                                        <span class="small">90%</span>
                                    </div>
                                    <div class="progress mb-2" style="height: 6px;">
                                        <div class="progress-bar bg-info" style="width: 90%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small">GPT Integration</span>
                                        <span class="small">93%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-primary" style="width: 93%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Personale -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-3">{{ __('Lavoriamo Insieme') }}</h2>
                <p class="lead mb-4">
                    {{ __('Sono sempre aperto a nuove sfide e collaborazioni. Se hai un progetto innovativo o vuoi trasformare la tua azienda con la tecnologia, contattami per una consulenza personalizzata.') }}
                </p>
                <div class="d-flex flex-wrap gap-3 mb-3">
                    <span class="badge bg-light text-dark px-3 py-2">{{ __('Consulenza Gratuita') }}</span>
                    <span class="badge bg-light text-dark px-3 py-2">{{ __('Preventivi Dettagliati') }}</span>
                    <span class="badge bg-light text-dark px-3 py-2">{{ __('Supporto Continuo') }}</span>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-column gap-3">
                    <a href="/contatti" class="btn btn-light btn-lg">
                        <i class="bi bi-person-circle me-2"></i>{{ __('Contattami') }}
                    </a>
                    <a href="https://wa.me/393791264458?text={{ urlencode(__('Ciao Davide, ho visitato il tuo sito e vorrei conoscerti meglio per un progetto.')) }}" 
                       class="btn btn-success btn-lg" target="_blank">
                        <i class="bi bi-whatsapp me-2"></i>{{ __('WhatsApp') }}
                    </a>
                    <a href="/scarica-brochure" class="btn btn-outline-light btn-lg" target="_blank">
                        <i class="bi bi-download me-2"></i>{{ __('Scarica Brochure') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>