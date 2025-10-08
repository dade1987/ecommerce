<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CavalliniService')</title>

    <!-- Favicon ottimizzato senza testo per dimensioni piccole -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo-icon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('logo-icon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('logo-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <!-- SEO Meta Tags -->
    <meta name="description" content="Davide Cavallini sviluppa soluzioni digitali su misura: siti web, software gestionali, intelligenza artificiale e cybersecurity. Servizi IT professionali a Noale (VE).">
    <meta name="keywords" content="sviluppo siti web, software gestionali, intelligenza artificiale, cybersecurity, penetration testing, Davide Cavallini, Noale, Venezia">
    <meta name="author" content="Davide Cavallini - CavalliniService">
    <meta name="robots" content="index, follow">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="CavalliniService – Siti, Gestionali e Sicurezza IT">
    <meta property="og:description" content="Davide Cavallini sviluppa soluzioni digitali su misura: web, IA e cybersecurity.">
    <meta property="og:image" content="{{ asset('logo-icon.png') }}">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    <meta property="og:image:type" content="image/png">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="CavalliniService">
    <meta property="og:locale" content="@if(app()->getLocale() == 'en') en_US @elseif(app()->getLocale() == 'es') es_ES @else it_IT @endif">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="CavalliniService – Siti, Gestionali e Sicurezza IT">
    <meta name="twitter:description" content="Davide Cavallini sviluppa soluzioni digitali su misura: web, IA e cybersecurity.">
    <meta name="twitter:image" content="{{ asset('logo-icon.png') }}">
    <meta name="twitter:image:alt" content="CavalliniService Logo">

    <!-- Business Schema Markup -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "LocalBusiness",
            "name": "CavalliniService",
            "description": "Servizi professionali IT: sviluppo siti web, software gestionali, intelligenza artificiale e cybersecurity",
            "founder": {
                "@type": "Person",
                "name": "Davide Cavallini"
            },
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "Via del Musonetto 4",
                "addressLocality": "Noale",
                "addressRegion": "VE",
                "postalCode": "30033",
                "addressCountry": "IT"
            },
            "telephone": "+393204206795",
            "email": "info@cavalliniservice.com",
            "url": "{{ url('/') }}",
            "logo": "{{ asset('logo-icon.png') }}",
            "image": "{{ asset('logo-icon.png') }}",
            "priceRange": "€€",
            "serviceArea": {
                "@type": "GeoCircle",
                "geoMidpoint": {
                    "@type": "GeoCoordinates",
                    "latitude": "45.4408",
                    "longitude": "12.0658"
                },
                "geoRadius": "50000"
            },
            "services": [
                "Sviluppo siti web",
                "Software gestionali",
                "Intelligenza artificiale",
                "Cybersecurity",
                "Penetration testing"
            ],
            "sameAs": [
                "https://wa.me/393204206795"
            ]
        }
    </script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('logo-round-main.png') }}">

    <!-- Meta tag specifici per pagina -->
    @yield('head')

    <!-- Bootstrap CSS con tema scuro Replit -->
    <link href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css" rel="stylesheet">

    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Stile Dopaminico Personalizzato -->
    <style>
        /* CSS Variables per tema chiaro/scuro */
        :root {
            --colore-bg: #F9FAFB;
            --colore-testo: #0F172A;
            --colore-accento: #6366F1;
            --colore-cta-hover: #4F46E5;
            --colore-nero: #111;

            --bg: #F8FAFC;
            --text: #0F172A;
            --accent: #6366F1;
            --bg-primary: #F8FAFC;
            --bg-secondary: #f1f5f9;
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --accent-color: #6366F1;
            --navbar-bg: rgba(248, 250, 252, 0.95);
            --card-bg: rgba(255, 255, 255, 0.9);
            --border-color: rgba(15, 23, 42, 0.1);
        }

        [data-bs-theme="light"] {
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --accent-color: #667eea;
            --navbar-bg: rgba(255, 255, 255, 0.95);
            --card-bg: rgba(248, 249, 250, 0.9);
            --border-color: rgba(0, 0, 0, 0.1);
        }

        /* Applicazione delle variabili */
        body {
            background-color: var(--colore-bg);
            color: var(--colore-testo);
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 400;
            line-height: 1.6;
            transition: background-color 0.3s ease, color 0.3s ease;
            margin: 0;
            padding: 0;
        }

        h1,
        h2,
        h3 {
            font-weight: 600;
            letter-spacing: -0.5px;
        }

        h1,
        h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        a {
            color: #1A0DAB;
            text-decoration: underline;
        }

        section {
            margin-bottom: 4rem;
        }

        /* Section styling classes */
        .section-light {
            background-color: #F9FAFB;
            color: var(--colore-testo);
        }

        .section-dark {
            background-color: #0F172A;
            color: white;
        }

        /* Background image section with overlay */
        .section-bg-img {
            position: relative;
            background: url('immagine.jpg') center/cover no-repeat;
        }

        .section-bg-img::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .section-bg-img * {
            position: relative;
            z-index: 2;
            color: white;
        }

        /* Modern button styling */
        .button,
        .cta {
            background-color: #6366F1;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s ease;
            text-decoration: none;
            display: inline-block;
            border: none;
            cursor: pointer;
        }

        .button:hover,
        .cta:hover {
            background-color: #4F46E5;
            text-decoration: none;
        }

        a.button {
            background-color: #6366F1;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s ease;
            text-decoration: none;
            display: inline-block;
            border: none;
            cursor: pointer;
        }

        a.button:hover {
            background-color: #4F46E5;
            color: white;
        }

        /* Global smooth transitions */
        * {
            transition: all 0.3s ease-in-out;
        }

        /* Card hover effects */
        .card:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        /* High contrast improvements */
        section,
        .hero,
        .content {
            color: #0F172A;
            background-color: #F9FAFB;
        }

        /* Hero overlay for better text readability */
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }

        .hero h1,
        .hero p {
            position: relative;
            z-index: 2;
            color: white;
        }

        /* Responsive typography for mobile */
        @media (max-width: 768px) {
            body {
                font-size: 16px;
            }

            h1 {
                font-size: 1.75rem;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #0F172A;
                color: #F8FAFC;
            }

            a.button {
                background: #6366F1;
                color: white;
            }
        }

        .navbar-dopaminico {
            background: var(--navbar-bg) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            box-shadow: none !important;
            outline: none !important;
            z-index: 10000 !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .navbar-dopaminico::before,
        .navbar-dopaminico::after {
            display: none !important;
        }

        /* Add padding to body to account for fixed navbar */
        body {
            padding-top: 80px !important;
            margin: 0 !important;
            border: none !important;
        }

        html {
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
        }

        * {
            box-sizing: border-box;
        }

        /* Force remove any top borders or margins that could create black line */
        .navbar,
        .navbar-expand-lg,
        .navbar-dark,
        nav {
            border-top: none !important;
            margin-top: 0 !important;
            padding-top: 0 !important;
            box-shadow: none !important;
            outline: none !important;
        }

        .container-fluid {
            margin-top: 0 !important;
            padding-top: 0.5rem !important;
        }

        /* Remove any potential Bootstrap default styling */
        .navbar::before,
        .navbar::after,
        .navbar-expand-lg::before,
        .navbar-expand-lg::after {
            display: none !important;
        }

        /* Force override any browser default or Bootstrap styles */
        nav.navbar-dopaminico {
            top: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            outline: 0 !important;
            box-shadow: none !important;
            background-clip: padding-box !important;
        }

        /* Override viewport styles that might add top spacing */
        @viewport {
            zoom: 1.0;
            width: device-width;
        }

        /* Force body and html to start at top */
        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            background-attachment: fixed !important;
        }

        .card-dopaminica {
            background: var(--card-bg) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-primary) !important;
        }

        .footer-dopaminico {
            background: var(--bg-secondary) !important;
            border-top: 1px solid var(--border-color);
        }

        /* Gradienti dopaminici principali */
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            position: relative;
            overflow: hidden;
        }

        .hero-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255, 0, 150, 0.1), rgba(0, 255, 255, 0.1));
            animation: shimmer 3s ease-in-out infinite alternate;
        }

        @keyframes shimmer {
            0% {
                opacity: 0.3;
            }

            100% {
                opacity: 0.7;
            }
        }

        /* Navbar dopaminico */
        .navbar-dopaminico {
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea) !important;
            backdrop-filter: blur(15px);
            border-bottom: 3px solid rgba(255, 255, 255, 0.2);
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
            z-index: 9999 !important;
            position: relative !important;
            margin: 0 !important;
            outline: none !important;
        }

        .navbar-brand {
            transition: all 0.4s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.1) rotate(2deg);
        }

        .nav-link {
            color: black !important;
            font-weight: 600;
            transition: all 0.3s ease;
            border-radius: 10px;
            margin: 0 8px;
            padding: 8px 16px !important;
        }

        .nav-link:hover {
            background: rgba(0, 0, 0, 0.1) !important;
            transform: translateY(-3px);
            color: #1A0DAB !important;
        }

        /* NAVBAR BUTTONS UNIFICATI */
        .navbar-btn {
            background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%) !important;
            border: none !important;
            color: white !important;
            font-weight: 600;
            border-radius: 8px !important;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 100px;
            height: 40px;
            padding: 8px 16px;
            font-size: 0.875rem;
            white-space: nowrap;
            cursor: pointer;
        }

        .navbar-btn:hover {
            background: linear-gradient(135deg, #0056b3 0%, #003d82 100%) !important;
            transform: translateY(-2px) scale(1.02);
            color: white !important;
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.4);
            text-decoration: none;
        }

        .navbar-btn:active {
            transform: translateY(0) scale(1);
        }

        .navbar-btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
        }

        /* Stili specifici per pulsante lingua */
        .navbar-btn .flag-icon {
            width: 20px;
            height: 14px;
            margin-right: 6px;
        }

        .navbar-btn .flag-text {
            font-size: 0.75rem;
            font-weight: 700;
        }

        /* Responsive navbar buttons */
        @media (max-width: 1200px) {
            .navbar-btn {
                min-width: 70px;
                padding: 5px 10px;
                font-size: 0.75rem;
            }
        }

        @media (max-width: 992px) {
            .navbar-btn {
                min-width: 60px;
                padding: 4px 8px;
                font-size: 0.7rem;
                height: 35px;
            }

            .navbar-btn .flag-icon {
                width: 16px;
                height: 12px;
                margin-right: 4px;
            }

            .navbar-btn .flag-text {
                font-size: 0.65rem;
            }

            .navbar-brand span {
                font-size: 1.2rem !important;
            }
        }

        @media (max-width: 768px) {
            .navbar-btn {
                min-width: 45px;
                padding: 2px 4px;
                font-size: 0.6rem;
                height: 30px;
            }

            .navbar-btn i {
                font-size: 0.7rem;
                margin-right: 2px;
            }

            .navbar-brand span {
                font-size: 0.9rem !important;
                display: inline !important;
            }

            .navbar-brand {
                font-size: 0.8rem !important;
            }

            .container-fluid {
                padding-left: 0.3rem !important;
                padding-right: 0.3rem !important;
            }
        }

        @media (max-width: 576px) {
            .navbar-btn {
                min-width: 40px;
                padding: 2px 3px;
                font-size: 0.55rem;
                height: 28px;
            }

            .navbar-brand span {
                font-size: 0.8rem !important;
            }

            .container-fluid {
                padding-left: 0.2rem !important;
                padding-right: 0.2rem !important;
            }
        }

        /* MOBILE MENU DROPDOWN */
        .mobile-menu-dropdown {
            position: fixed;
            top: 80px;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border-bottom: 3px solid rgba(255, 255, 255, 0.2);
            z-index: 9998;
            transform: translateY(-100%);
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            max-height: calc(100vh - 80px);
            overflow-y: auto;
        }

        .mobile-menu-dropdown.active {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }

        .mobile-menu-content {
            padding: 20px;
        }

        .mobile-menu-item {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            color: white !important;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            border-radius: 12px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .mobile-menu-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(10px);
            color: white !important;
            text-decoration: none;
        }

        .mobile-menu-item i {
            width: 24px;
            text-align: center;
        }

        .mobile-menu-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            margin: 20px 0;
        }

        .mobile-menu-controls {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .mobile-menu-control {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            color: white;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .mobile-menu-control:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(5px);
        }

        .mobile-menu-control .flag-icon {
            width: 24px;
            height: 16px;
        }

        /* Mobile menu toggle animation */
        .mobile-menu-toggle.active i {
            transform: rotate(90deg);
        }

        /* Hide mobile menu on desktop */
        @media (min-width: 992px) {
            .mobile-menu-dropdown {
                display: none !important;
            }
        }

        /* Pulsante fisso blu navbar */
        .btn-progetto-fisso {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            background: #667eea !important;
            color: white !important;
            border: none;
            border-radius: 8px;
            padding: 20px 35px;
            font-weight: 700;
            font-size: 18px;
            text-decoration: none;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            animation: pulse-glow-blue 3s infinite;
            white-space: nowrap;
            min-width: 320px;
            text-align: center;
        }

        .btn-progetto-fisso:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.6);
            background: #5a6fd8 !important;
            color: white !important;
        }

        .btn-progetto-fisso:active {
            transform: translateY(-2px) scale(1.02);
        }

        @keyframes pulse-glow-blue {

            0%,
            100% {
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            }

            50% {
                box-shadow: 0 12px 35px rgba(102, 126, 234, 0.7);
            }
        }

        /* Responsive mobile */
        @media (max-width: 768px) {
            .btn-progetto-fisso {
                bottom: 20px;
                right: 20px;
                left: 20px;
                min-width: auto;
                max-width: none;
                font-size: 16px;
                padding: 18px 25px;
            }
        }

        /* Icona animata */
        .btn-progetto-fisso i {
            transition: transform 0.3s ease;
            margin-right: 8px;
        }

        .btn-progetto-fisso:hover i {
            transform: rotate(15deg) scale(1.2);
        }

        /* Sistema pulsanti unificato dopaminico */
        .btn-principale {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white !important;
            font-weight: 700;
            border-radius: 25px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-principale:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.5);
            color: white !important;
        }

        .btn-principale:active {
            transform: translateY(-1px) scale(1.01);
        }

        /* Pulsanti secondari */
        .btn-secondario {
            background: linear-gradient(45deg, #4ecdc4, #44a08d);
            border: none;
            color: white !important;
            font-weight: 600;
            border-radius: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(68, 160, 141, 0.3);
        }

        .btn-secondario:hover {
            background: linear-gradient(45deg, #44a08d, #4ecdc4);
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 10px 25px rgba(68, 160, 141, 0.4);
            color: white !important;
        }

        /* Sistema Pulsanti Unificato Dopaminico */

        /* Pulsanti lingua eleganti */
        .btn-lingua {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 50%, #4facfe 100%);
            border: none;
            color: white !important;
            font-weight: 600;
            border-radius: 20px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            margin: 0 3px;
            box-shadow: 0 6px 18px rgba(240, 147, 251, 0.3);
            position: relative;
            overflow: hidden;
            min-width: 140px;
            max-width: 140px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .btn-lingua:hover {
            background: linear-gradient(135deg, #4facfe 0%, #f5576c 50%, #f093fb 100%);
            transform: translateY(-3px) scale(1.08);
            color: white !important;
            box-shadow: 0 12px 30px rgba(240, 147, 251, 0.5);
        }

        .btn-lingua:active {
            transform: translateY(-1px) scale(1.02);
        }

        /* ===========================================
           STILI PAGINA OFFERTE MIGLIORATA
           =========================================== */

        /* Hero Section Animations */
        .pulse-badge {
            animation: pulse-animation 2s infinite;
        }

        @keyframes pulse-animation {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .text-shadow {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* HIGHLIGHT BADGES ACCATTIVANTI */
        .highlight-badge {
            position: relative;
            text-align: center;
            padding: 25px 20px;
            border-radius: 20px;
            min-width: 160px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            overflow: hidden;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.25), rgba(255, 255, 255, 0.1));
            backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .discount-badge {
            background: linear-gradient(145deg, #ff6b6b, #ee5a24);
            border-color: #ff6b6b;
            animation: discount-pulse 3s ease-in-out infinite;
        }

        .package-badge {
            background: linear-gradient(145deg, #4ecdc4, #00d2d3);
            border-color: #4ecdc4;
            animation: package-bounce 2s ease-in-out infinite;
        }

        .support-badge {
            background: linear-gradient(145deg, #45b7d1, #3742fa);
            border-color: #45b7d1;
            animation: support-glow 2.5s ease-in-out infinite alternate;
        }

        .highlight-badge:hover {
            transform: translateY(-15px) scale(1.1) rotate(2deg);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
        }

        .highlight-badge i {
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .highlight-badge:hover i {
            transform: scale(1.3) rotate(15deg);
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.8);
        }

        .highlight-badge span {
            color: white;
            font-size: 1.1rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);
            margin-top: 5px;
        }

        .badge-glow {
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
            animation: rotate-glow 4s linear infinite;
            pointer-events: none;
        }

        @keyframes discount-pulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 20px rgba(255, 107, 107, 0);
            }
        }

        @keyframes package-bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes support-glow {
            0% {
                box-shadow: 0 8px 32px rgba(69, 183, 209, 0.3);
            }

            100% {
                box-shadow: 0 8px 32px rgba(69, 183, 209, 0.8), 0 0 0 10px rgba(69, 183, 209, 0.1);
            }
        }

        @keyframes rotate-glow {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* URGENCY BANNER ACCATTIVANTE */
        .urgency-banner {
            margin-top: 30px;
            position: relative;
        }

        .urgency-content {
            position: relative;
            background: linear-gradient(145deg, #ff4757, #c44569);
            padding: 25px 40px;
            border-radius: 50px;
            display: inline-block;
            box-shadow: 0 10px 40px rgba(255, 71, 87, 0.4);
            border: 3px solid #ffffff;
            animation: urgency-shake 3s ease-in-out infinite;
            overflow: hidden;
        }

        .urgency-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin: 0;
            animation: text-flash 2s ease-in-out infinite alternate;
        }

        .urgency-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            margin: 0;
        }

        .urgency-pulse {
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border: 3px solid rgba(255, 255, 255, 0.6);
            border-radius: 50px;
            animation: pulse-border 2s ease-in-out infinite;
        }

        @keyframes urgency-shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-2px) rotate(-1deg);
            }

            75% {
                transform: translateX(2px) rotate(1deg);
            }
        }

        @keyframes text-flash {
            0% {
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            }

            100% {
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5), 0 0 20px rgba(255, 255, 255, 0.8);
            }
        }

        @keyframes pulse-border {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            100% {
                transform: scale(1.1);
                opacity: 0;
            }
        }

        /* RESPONSIVE MOBILE */
        @media (max-width: 768px) {
            .highlight-badge {
                min-width: 120px;
                padding: 20px 15px;
            }

            .highlight-badge span {
                font-size: 0.95rem;
            }

            .urgency-content {
                padding: 20px 30px;
            }

            .urgency-title {
                font-size: 1.2rem;
            }
        }

        /* Floating Shapes Animation */
        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 15s infinite linear;
        }

        .shape-1 {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 15%;
            animation-delay: 5s;
        }

        .shape-3 {
            width: 60px;
            height: 60px;
            top: 80%;
            left: 80%;
            animation-delay: 10s;
        }

        @keyframes float {
            0% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.3;
            }

            50% {
                transform: translateY(-20px) rotate(180deg);
                opacity: 0.7;
            }

            100% {
                transform: translateY(0px) rotate(360deg);
                opacity: 0.3;
            }
        }

        /* Section Title Styling */
        .text-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .separator-line {
            width: 80px;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }

        /* Offer Cards Styling */
        .offer-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 2px solid transparent;
            overflow: hidden;
            position: relative;
        }

        .offer-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .offer-card-bestseller {
            border-color: #dc3545;
            background: linear-gradient(145deg, #ffffff 0%, #fff5f5 100%);
        }

        .offer-card-business {
            border-color: #ffc107;
            background: linear-gradient(145deg, #ffffff 0%, #fffbf0 100%);
        }

        .offer-card-innovation {
            border-color: #17a2b8;
            background: linear-gradient(145deg, #ffffff 0%, #f0fcff 100%);
        }

        /* Offer Ribbon */
        .offer-ribbon {
            position: absolute;
            top: 20px;
            right: -10px;
            background: #dc3545;
            color: white;
            padding: 8px 20px 8px 15px;
            font-weight: bold;
            font-size: 0.8rem;
            z-index: 10;
            transform: rotate(0deg);
            border-radius: 4px 0 0 4px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .offer-ribbon::after {
            content: '';
            position: absolute;
            right: -8px;
            top: 0;
            border: 20px solid transparent;
            border-left-color: #dc3545;
            border-right: none;
        }

        .ribbon-business {
            background: #ffc107;
            color: #000;
        }

        .ribbon-business::after {
            border-left-color: #ffc107;
        }

        .ribbon-innovation {
            background: #17a2b8;
        }

        .ribbon-innovation::after {
            border-left-color: #17a2b8;
        }

        .ribbon-icon {
            margin-left: 5px;
            font-size: 0.7rem;
        }

        /* Offer Header */
        .offer-header {
            padding: 40px 30px 30px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .offer-icon-container {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .offer-icon {
            font-size: 2.5rem;
            color: white;
        }

        .offer-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .offer-subtitle {
            color: #6c757d;
            font-size: 1rem;
            margin: 0;
        }

        /* Pricing Section */
        .offer-pricing {
            padding: 30px;
            background: #f8f9fa;
        }

        .price-container {
            position: relative;
        }

        .old-price {
            font-size: 1.2rem;
            color: #6c757d;
            text-decoration: line-through;
            display: block;
            margin-bottom: 5px;
        }

        .new-price {
            font-size: 3rem;
            font-weight: 900;
            color: #28a745;
            line-height: 1;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .discount-badge {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-block;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        /* Features Section */
        .offer-features {
            padding: 30px;
        }

        .features-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #2c3e50;
            text-align: center;
        }

        .features-list {
            space-y: 15px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .feature-item:last-child {
            border-bottom: none;
        }

        .feature-item:hover {
            background: #f8f9fa;
            padding-left: 10px;
            border-radius: 8px;
        }

        .feature-icon {
            color: #28a745;
            font-size: 1.2rem;
            margin-right: 15px;
            min-width: 20px;
        }

        .feature-item span {
            font-size: 1rem;
            color: #495057;
            line-height: 1.4;
        }

        /* CTA Section */
        .offer-cta {
            padding: 30px;
            text-align: center;
            background: white;
        }

        .btn-offer-primary {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
            width: 100%;
            font-size: 1.1rem;
        }

        .btn-offer-primary:hover {
            background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%);
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(220, 53, 69, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-offer-business {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #000;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 25px rgba(255, 193, 7, 0.3);
            width: 100%;
            font-size: 1.1rem;
        }

        .btn-offer-business:hover {
            background: linear-gradient(135deg, #e0a800 0%, #d39e00 100%);
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(255, 193, 7, 0.4);
            color: #000;
            text-decoration: none;
        }

        .btn-offer-innovation {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 25px rgba(23, 162, 184, 0.3);
            width: 100%;
            font-size: 1.1rem;
        }

        .btn-offer-innovation:hover {
            background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(23, 162, 184, 0.4);
            color: white;
            text-decoration: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .offer-card {
                margin-bottom: 30px;
            }

            .offer-icon-container {
                width: 60px;
                height: 60px;
            }

            .offer-icon {
                font-size: 2rem;
            }

            .new-price {
                font-size: 2.5rem;
            }

            .feature-badge {
                padding: 15px;
                margin-bottom: 15px;
            }
        }

        /* Override Bootstrap per consistenza */
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%) !important;
            border: none !important;
            color: white !important;
            font-weight: 600;
            border-radius: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3 0%, #003d82 100%) !important;
            transform: translateY(-4px) scale(1.05);
            color: white !important;
            box-shadow: 0 15px 35px rgba(13, 110, 253, 0.5);
        }

        .btn-success {
            background: linear-gradient(45deg, #56ab2f, #a8e6cf) !important;
            border: none !important;
            color: white !important;
            font-weight: 600;
            border-radius: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 25px rgba(86, 171, 47, 0.3);
        }

        .btn-success:hover {
            background: linear-gradient(45deg, #a8e6cf, #56ab2f) !important;
            transform: translateY(-4px) scale(1.05);
            color: white !important;
            box-shadow: 0 15px 35px rgba(86, 171, 47, 0.5);
        }

        .btn-info {
            background: linear-gradient(45deg, #17a2b8, #20c997) !important;
            border: none !important;
            color: white !important;
            font-weight: 600;
            border-radius: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 25px rgba(23, 162, 184, 0.3);
        }

        .btn-info:hover {
            background: linear-gradient(45deg, #20c997, #17a2b8) !important;
            transform: translateY(-4px) scale(1.05);
            color: white !important;
            box-shadow: 0 15px 35px rgba(23, 162, 184, 0.5);
        }

        .btn-warning {
            background: linear-gradient(45deg, #ffc107, #fd7e14) !important;
            border: none !important;
            color: white !important;
            font-weight: 600;
            border-radius: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 25px rgba(255, 193, 7, 0.3);
        }

        .btn-warning:hover {
            background: linear-gradient(45deg, #fd7e14, #ffc107) !important;
            transform: translateY(-4px) scale(1.05);
            color: white !important;
            box-shadow: 0 15px 35px rgba(255, 193, 7, 0.5);
        }

        /* Pulsanti outline eleganti migliorati */
        .btn-outline-light {
            background: rgba(0, 0, 0, 0.1) !important;
            border: 2px solid rgba(0, 0, 0, 0.4) !important;
            color: black !important;
            font-weight: 600;
            border-radius: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(10px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-outline-light:hover {
            background: rgba(0, 0, 0, 0.2) !important;
            border-color: rgba(0, 0, 0, 0.8) !important;
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
            color: black !important;
        }

        .btn-outline-elegante {
            background: transparent;
            border: 2px solid rgba(0, 0, 0, 0.4);
            color: black !important;
            font-weight: 600;
            border-radius: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(10px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-outline-elegante:hover {
            background: rgba(0, 0, 0, 0.15);
            border-color: rgba(0, 0, 0, 0.8);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
            color: black !important;
        }

        /* Dimensioni pulsanti consistenti */
        .btn-lg {
            padding: 15px 35px !important;
            font-size: 1.15rem !important;
            border-radius: 0 !important;
        }

        .btn-sm {
            padding: 8px 18px !important;
            font-size: 0.9rem !important;
            border-radius: 0 !important;
        }

        .btn-xs {
            padding: 2px 6px !important;
            font-size: 0.65rem !important;
            border-radius: 0 !important;
        }

        /* Effetti aggiuntivi per tutti i pulsanti */
        .btn {
            position: relative;
            overflow: hidden;
            border-radius: 0 !important;
        }

        .btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s;
        }

        .btn:hover:before {
            left: 100%;
        }

        /* Dropdown lingua elegante */
        .dropdown-menu {
            border-radius: 15px !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            backdrop-filter: blur(15px) !important;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            border-radius: 10px !important;
            margin: 2px 5px !important;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            color: #333 !important;
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 50%, #4facfe 100%) !important;
            color: white !important;
            transform: translateX(5px) scale(1.02) !important;
            box-shadow: 0 5px 15px rgba(240, 147, 251, 0.3) !important;
        }

        .dropdown-item.active {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 50%, #4facfe 100%) !important;
            color: white !important;
            box-shadow: 0 5px 15px rgba(240, 147, 251, 0.3) !important;
        }

        .dropdown-item.active:hover {
            background: linear-gradient(135deg, #4facfe 0%, #f5576c 50%, #f093fb 100%) !important;
        }

        /* Bandiere animate */
        .dropdown-item span:first-child {
            transition: transform 0.3s ease;
        }

        .dropdown-item:hover span:first-child {
            transform: scale(1.2) rotate(5deg);
        }

        /* Bandiere CSS personalizzate */
        .flag-icon {
            display: inline-block;
            width: 24px;
            height: 16px;
            border-radius: 3px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        .flag-icon:hover {
            transform: scale(1.1);
        }

        /* Bandiera Italia */
        .flag-italy {
            width: 100%;
            height: 100%;
            background: linear-gradient(to right,
                    #009246 0%, #009246 33.33%,
                    #ffffff 33.33%, #ffffff 66.66%,
                    #ce2b37 66.66%, #ce2b37 100%);
        }

        /* Bandiera Regno Unito - Union Jack */
        .flag-uk {
            width: 100%;
            height: 100%;
            background: #012169;
            position: relative;
            overflow: hidden;
        }

        /* Croce di Sant'Andrea (diagonali bianche) */
        .flag-uk:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                linear-gradient(27deg, transparent 43%, #ffffff 43%, #ffffff 57%, transparent 57%),
                linear-gradient(-27deg, transparent 43%, #ffffff 43%, #ffffff 57%, transparent 57%);
        }

        /* Croce di San Giorgio (croce centrale) */
        .flag-uk:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                linear-gradient(to right, transparent 43%, #ffffff 43%, #ffffff 57%, transparent 57%),
                linear-gradient(to bottom, transparent 43%, #ffffff 43%, #ffffff 57%, transparent 57%);
        }

        /* Diagonali rosse di Sant'Andrea */
        .flag-uk::before {
            background:
                linear-gradient(27deg, transparent 40%, #c8102e 40%, #c8102e 45%, transparent 45%),
                linear-gradient(27deg, transparent 55%, #c8102e 55%, #c8102e 60%, transparent 60%),
                linear-gradient(-27deg, transparent 40%, #c8102e 40%, #c8102e 45%, transparent 45%),
                linear-gradient(-27deg, transparent 55%, #c8102e 55%, #c8102e 60%, transparent 60%),
                linear-gradient(27deg, transparent 43%, #ffffff 43%, #ffffff 57%, transparent 57%),
                linear-gradient(-27deg, transparent 43%, #ffffff 43%, #ffffff 57%, transparent 57%);
        }

        /* Croce rossa di San Giorgio */
        .flag-uk::after {
            background:
                linear-gradient(to right, transparent 40%, #c8102e 40%, #c8102e 45%, transparent 45%),
                linear-gradient(to right, transparent 55%, #c8102e 55%, #c8102e 60%, transparent 60%),
                linear-gradient(to bottom, transparent 40%, #c8102e 40%, #c8102e 45%, transparent 45%),
                linear-gradient(to bottom, transparent 55%, #c8102e 55%, #c8102e 60%, transparent 60%),
                linear-gradient(to right, transparent 43%, #ffffff 43%, #ffffff 57%, transparent 57%),
                linear-gradient(to bottom, transparent 43%, #ffffff 43%, #ffffff 57%, transparent 57%);
        }

        /* Bandiera Spagna */
        .flag-spain {
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom,
                    #aa151b 0%, #aa151b 25%,
                    #f1bf00 25%, #f1bf00 75%,
                    #aa151b 75%, #aa151b 100%);
        }

        /* Testo sotto le bandiere */
        .flag-text {
            font-size: 10px;
            font-weight: 700;
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            line-height: 1;
            margin-top: 2px;
        }

        /* Testo arcobaleno animato */
        .testo-arcobaleno {
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #ffeaa7, #fd79a8);
            background-size: 400% 400%;
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: arcobaleno 4s ease infinite;
            font-weight: bold;
        }

        @keyframes arcobaleno {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Card hover dopaminiche */
        .card-dopaminica {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
        }

        .card-dopaminica:hover {
            transform: translateY(-15px) scale(1.03);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        }

        /* Bottone principale dopaminico */
        .btn-principale {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            font-weight: bold;
            padding: 12px 30px;
            border-radius: 25px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-principale:hover {
            background: linear-gradient(45deg, #764ba2, #f093fb);
            transform: scale(1.1);
            color: white;
            box-shadow: 0 12px 30px rgba(118, 75, 162, 0.4);
        }

        /* Footer dopaminico */
        .footer-dopaminico {
            background: linear-gradient(180deg, #2c3e50, #34495e, #2c3e50);
            position: relative;
        }

        .footer-dopaminico::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #667eea);
            background-size: 400% 400%;
            animation: footer-line 3s ease infinite;
        }

        @keyframes footer-line {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Icone e avatar piccoli ottimizzati */
        .logo-small,
        .avatar-small,
        .icon-small {
            border-radius: 50% !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
            background: rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(5px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .logo-small:hover,
        .avatar-small:hover,
        .icon-small:hover {
            transform: scale(1.1);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4) !important;
            border-color: rgba(255, 255, 255, 0.4);
        }

        /* Dimensioni specifiche per avatar */
        .avatar-xs {
            width: 32px !important;
            height: 32px !important;
        }

        .avatar-sm {
            width: 48px !important;
            height: 48px !important;
        }

        .avatar-md {
            width: 64px !important;
            height: 64px !important;
        }

        /* Bagliore esterno per evidenziare il logo su sfondi complessi */
        .shadow-outline {
            box-shadow: 0 0 12px rgba(255, 255, 255, 0.3);
        }

        .shadow-outline-strong {
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.5), 0 0 40px rgba(102, 126, 234, 0.3);
        }

        .shadow-outline-colored {
            box-shadow: 0 0 15px rgba(102, 126, 234, 0.4), 0 0 30px rgba(118, 75, 162, 0.2);
        }

        /* Logo adattivo per modalità chiara/scura */
        .logo-adaptive-dark {
            display: block;
        }

        .logo-adaptive-light {
            display: none;
        }

        /* Media query per modalità chiara del sistema */
        @media (prefers-color-scheme: light) {
            .logo-adaptive-dark {
                display: none;
            }

            .logo-adaptive-light {
                display: block;
            }
        }

        /* Override quando il body ha data-bs-theme="dark" */
        [data-bs-theme="dark"] .logo-adaptive-dark {
            display: block;
        }

        [data-bs-theme="dark"] .logo-adaptive-light {
            display: none;
        }

        [data-bs-theme="light"] .logo-adaptive-dark {
            display: none;
        }

        [data-bs-theme="light"] .logo-adaptive-light {
            display: block;
        }
    </style>

    <script id="vtag-ai-js" async src="https://r2.leadsy.ai/tag.js" data-pid="RNcqSF30wzzZrjOY"
        data-version="062024"></script>
</head>

<body data-bs-theme="dark">
    <!-- Navbar Linear Design -->
    <nav class="navbar navbar-expand-lg navbar-dopaminico" style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; z-index: 10000 !important; margin: 0 !important; border: none !important;">
        <div class="container-fluid px-3">
            <div class="d-flex w-100 align-items-center justify-content-between">
                <!-- Logo e Brand - Sinistra -->
                <div class="d-flex align-items-center">
                    <a class="navbar-brand d-flex align-items-center me-4" href="/">
                        <!-- Logo adattivo per modalità scura -->
                        <img src="{{ asset('logo-dark.png') }}" alt="CavalliniService Logo" height="40" class="me-2 rounded-circle shadow logo-adaptive-dark" style="border: 2px solid white;">
                        <!-- Logo adattivo per modalità chiara -->
                        <img src="{{ asset('logo-light.png') }}" alt="CavalliniService Logo" height="40" class="me-2 rounded-circle shadow logo-adaptive-light" style="border: 2px solid white;">
                        <span class="fs-3 fw-bold text-black d-none d-md-inline" style="font-family: 'Arial', sans-serif; letter-spacing: -0.5px; color: #000000;">CavalliniService</span>
                    </a>
                </div>

                <!-- Navigation and Controls - Ordine Specifico -->
                <div class="d-flex gap-1 align-items-center flex-wrap">
                    <!-- Navigation Links - Desktop (Ordine: Home, Chi Siamo, Portfolio, Blog, Preventivo, Offerte) -->
                    <div class="d-none d-lg-flex gap-1">
                        <a href="/" class="navbar-btn">
                            <i class="fas fa-home me-1"></i>{{ __('Home') }}
                        </a>
                        <a href="/chi-siamo" class="navbar-btn">
                            <i class="fas fa-user me-1"></i>{{ __('Chi Siamo') }}
                        </a>
                        <a href="/portfolio" class="navbar-btn">
                            <i class="fas fa-briefcase me-1"></i>{{ __('Portfolio') }}
                        </a>
                        <a href="/blog" class="navbar-btn">
                            <i class="fas fa-book me-1"></i>{{ __('Blog') }}
                        </a>
                        <a href="/preventivo" class="navbar-btn">
                            <i class="fas fa-calculator me-1"></i>{{ __('Preventivo') }}
                        </a>
                        <a href="/offerte" class="navbar-btn">
                            <i class="fas fa-fire me-1"></i>{{ __('Offerte') }}
                        </a>
                    </div>

                    <!-- Controls Always Visible (Lingua, Tema) -->
                    <div class="d-flex gap-1 align-items-center">
                        <!-- Language Selector -->
                        <button id="languageButton" class="navbar-btn d-flex align-items-center justify-content-center" onclick="changeLanguage()">
                            <span id="currentFlag" class="flag-icon me-1">
                                @if(app()->getLocale() == 'it')
                                <div class="flag-italy"></div>
                                @elseif(app()->getLocale() == 'en')
                                <div class="flag-uk"></div>
                                @elseif(app()->getLocale() == 'es')
                                <div class="flag-spain"></div>
                                @else
                                <div class="flag-italy"></div>
                                @endif
                            </span>
                            <small class="flag-text">
                                @if(app()->getLocale() == 'it')IT
                                @elseif(app()->getLocale() == 'en')EN
                                @elseif(app()->getLocale() == 'es')ES
                                @else IT
                                @endif
                            </small>
                        </button>

                        <!-- Theme Toggle -->
                        <button id="themeToggle" class="navbar-btn d-flex align-items-center justify-content-center" title="Cambia tema">
                            <i class="fas fa-moon" id="themeIcon"></i>
                        </button>

                        <!-- Mobile Menu Button -->
                        <button class="navbar-btn mobile-menu-toggle d-lg-none" id="mobileMenuToggle">
                            <i class="fas fa-bars" id="mobileMenuIcon"></i>
                        </button>
                    </div>
                </div>
            </div>
    </nav>

    <!-- Mobile Menu Dropdown -->
    <div class="mobile-menu-dropdown" id="mobileMenuDropdown">
        <div class="mobile-menu-content">
            <a href="/" class="mobile-menu-item">
                <i class="fas fa-home me-2"></i>{{ __('Home') }}
            </a>
            <a href="/chi-siamo" class="mobile-menu-item">
                <i class="fas fa-user me-2"></i>{{ __('Chi Siamo') }}
            </a>
            <a href="/portfolio" class="mobile-menu-item">
                <i class="fas fa-briefcase me-2"></i>{{ __('Portfolio') }}
            </a>
            <a href="/blog" class="mobile-menu-item">
                <i class="fas fa-book me-2"></i>{{ __('Blog') }}
            </a>
            <a href="/preventivo" class="mobile-menu-item">
                <i class="fas fa-calculator me-2"></i>{{ __('Preventivo') }}
            </a>
            <a href="/offerte" class="mobile-menu-item">
                <i class="fas fa-fire me-2"></i>{{ __('Offerte') }}
            </a>
            <a href="/contatti" class="mobile-menu-item">
                <i class="fas fa-envelope me-2"></i>{{ __('Contatti') }}
            </a>

            <div class="mobile-menu-divider"></div>

            <!-- Mobile Controls -->
            <div class="mobile-menu-controls">
                <button class="mobile-menu-control" onclick="changeLanguage()">
                    <span id="mobileCurrentFlag" class="flag-icon me-2">
                        @if(app()->getLocale() == 'it')
                        <div class="flag-italy"></div>
                        @elseif(app()->getLocale() == 'en')
                        <div class="flag-uk"></div>
                        @elseif(app()->getLocale() == 'es')
                        <div class="flag-spain"></div>
                        @else
                        <div class="flag-italy"></div>
                        @endif
                    </span>
                    <span>
                        @if(app()->getLocale() == 'it')Italiano
                        @elseif(app()->getLocale() == 'en')English
                        @elseif(app()->getLocale() == 'es')Español
                        @else Italiano
                        @endif
                    </span>
                </button>

                <button class="mobile-menu-control" id="mobileThemeToggle">
                    <i class="fas fa-moon me-2" id="mobileThemeIcon"></i>
                    <span>Tema Scuro</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Contenuto Principale -->
    <main>
        <x-filament-fabricator::page-blocks :blocks="$page->blocks" />
    </main>



    <!-- Footer Uniforme -->
    <footer class="footer-dopaminico text-white py-5 mt-auto">
        <div class="container">
            <div class="row g-4">

                <!-- Logo e Descrizione -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center mb-4">
                        <!-- Logo adattivo per modalità scura -->
                        <img src="{{ asset('logo-dark.png') }}" alt="CavalliniService Logo" height="50" class="me-3 rounded-circle shadow shadow-outline logo-adaptive-dark" style="border: 2px solid white;">
                        <!-- Logo adattivo per modalità chiara -->
                        <img src="{{ asset('logo-light.png') }}" alt="CavalliniService Logo" height="50" class="me-3 rounded-circle shadow shadow-outline logo-adaptive-light" style="border: 2px solid white;">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">CavalliniService</h5>
                            <p class="mb-0 text-dark small">{{ __('Soluzioni informatiche avanzate') }}</p>
                        </div>
                    </div>
                    <p class="text-dark mb-4">
                        {{ __('Servizi professionali e assistenza tecnica specializzata di Davide Cavallini per aziende moderne.') }}
                    </p>

                    <!-- Collegamenti Social con Stile Uniforme -->
                    <h6 class="fw-bold text-dark mb-3">{{ __('Seguici') }}</h6>
                    <div class="d-flex gap-1">
                        <a href="#" class="btn btn-primary btn-xs px-1 py-1" title="LinkedIn" style="min-width: 28px; min-height: 24px;">
                            <i class="fab fa-linkedin-in" style="font-size: 10px;"></i>
                        </a>
                        <a href="#" class="btn btn-primary btn-xs px-1 py-1" title="Facebook" style="min-width: 28px; min-height: 24px;">
                            <i class="fab fa-facebook-f" style="font-size: 10px;"></i>
                        </a>
                        <a href="#" class="btn btn-primary btn-xs px-1 py-1" title="Instagram" style="min-width: 28px; min-height: 24px;">
                            <i class="fab fa-instagram" style="font-size: 10px;"></i>
                        </a>
                        <a href="#" class="btn btn-primary btn-xs px-1 py-1" title="YouTube" style="min-width: 28px; min-height: 24px;">
                            <i class="fab fa-youtube" style="font-size: 10px;"></i>
                        </a>
                    </div>
                </div>

                <!-- Recapiti Aziendali -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold text-dark mb-3">{{ __('Informazioni Aziendali') }}</h6>
                    <div class="card border-0 shadow mb-3" style="background-color: #F9FAFB;">
                        <div class="card-body p-3">
                            <h6 class="text-dark mb-2 fw-bold">DAVIDE CAVALLINI</h6>
                            <div class="text-dark small">
                                <div class="mb-1">
                                    <strong>{{ __('Partita IVA') }}:</strong> IT04914550274
                                </div>
                                <div class="mb-1">
                                    <strong>{{ __('Codice Fiscale') }}:</strong> CVLDVD87M23L736P
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-start p-2 rounded" style="background-color: #F9FAFB;">
                        <i class="fas fa-map-marker-alt text-info me-2 mt-1"></i>
                        <div class="text-dark small fw-bold">
                            Via del Musonetto 4<br>
                            30033 Noale (VE)<br>
                            Italia
                        </div>
                    </div>
                </div>

                <!-- Servizi Principali -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold text-dark mb-3">{{ __('Servizi') }}</h6>
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-light btn-sm text-start">
                            {{ __('Sviluppo Siti Web') }}
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm text-start">
                            {{ __('Software Gestionali') }}
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm text-start">
                            {{ __('Intelligenza Artificiale') }}
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm text-start">
                            {{ __('Cyber Security') }}
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm text-start">
                            {{ __('Penetration Testing') }}
                        </a>
                    </div>
                </div>

                <!-- Contatti e WhatsApp -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold text-dark mb-3">{{ __('Contatti') }}</h6>

                    <div class="d-grid gap-2 mb-3">
                        <a href="mailto:info@cavalliniservice.com" class="btn btn-primary btn-sm">
                            <i class="fas fa-envelope me-2"></i>
                            info@cavalliniservice.com
                        </a>
                        <a href="tel:+393204206795" class="btn btn-primary btn-sm">
                            <i class="fas fa-phone me-2"></i>
                            +39 320 420 6795
                        </a>
                    </div>

                    <!-- WhatsApp Evidenziato con Stile Uniforme -->
                    <div class="mb-3">
                        <a href="https://wa.me/393791264458?text={{ urlencode(__('Ciao, ho visitato CavalliniService e vorrei informazioni sui vostri servizi.')) }}"
                            target="_blank"
                            class="btn btn-success w-100 py-3 fw-bold">
                            <i class="fab fa-whatsapp me-2 fs-5"></i>
                            {{ __('Scrivici su WhatsApp') }}
                        </a>
                    </div>

                    <!-- Link Rapidi con Stile Uniforme -->
                    <div class="d-flex gap-1">
                        <a href="/" class="btn btn-primary btn-xs px-2 py-1 fw-bold">{{ __('Home') }}</a>
                        <a href="/contatti" class="btn btn-primary btn-xs px-2 py-1 fw-bold">{{ __('Contatti') }}</a>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <hr class="my-4 border-secondary">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-dark">
                        © 2024 CavalliniService - Davide Cavallini. {{ __('Tutti i diritti riservati') }}.
                    </small>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex flex-column align-items-md-end">
                        <div class="mb-2">
                            <a href="/scarica-brochure" class="text-dark text-decoration-none me-3" target="_blank">
                                <i class="bi bi-download me-1"></i>{{ __('Brochure PDF') }}
                            </a>
                            <a href="/privacy-policy" class="text-dark text-decoration-none me-3">
                                <i class="bi bi-shield-check me-1"></i>{{ __('Privacy Policy') }}
                            </a>
                            <button type="button" class="btn btn-link text-dark p-0" onclick="showCookieSettings()">
                                <i class="bi bi-gear me-1"></i>{{ __('Cookie') }}
                            </button>
                        </div>
                        <small class="text-dark">
                            {{ __('Sviluppato con passione') }} ❤️ {{ __('in Italia') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Pulsante Fisso Progetto Gratuito -->
    <a href="/preventivo" class="btn-progetto-fisso" id="btnProgettoFisso">
        <i class="bi bi-rocket-takeoff"></i>{{ __('Richiedi un preventivo gratuito') }}
    </a>

    <!-- Cookie Banner GDPR -->
    <div id="cookieBanner" class="cookie-banner position-fixed bottom-0 start-0 end-0 bg-dark text-white p-4 shadow-lg" style="z-index: 9999; display: none;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-shield-check fs-4 text-warning me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-2">{{ __('Questo sito utilizza cookie') }}</h6>
                            <p class="mb-2 small">
                                {{ __('Utilizziamo cookie necessari per il funzionamento del sito e cookie analitici per migliorare la tua esperienza. I cookie analitici vengono attivati solo con il tuo consenso esplicito.') }}
                            </p>
                            <a href="/privacy-policy" class="text-warning text-decoration-none small">
                                <i class="bi bi-info-circle me-1"></i>{{ __('Leggi la Privacy Policy completa') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="acceptOnlyNecessary()">
                                {{ __('Solo Necessari') }}
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" onclick="acceptAllCookies()">
                                {{ __('Accetta Tutti') }}
                            </button>
                        </div>
                        <button type="button" class="btn btn-link text-warning p-0 small" onclick="showCookieSettings()">
                            {{ __('Personalizza Impostazioni') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cookie Settings Modal -->
    <div class="modal fade" id="cookieSettingsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-gear me-2"></i>{{ __('Impostazioni Cookie') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Puoi scegliere quali categorie di cookie accettare. I cookie necessari sono sempre attivi.') }}</p>

                    <!-- Cookie Necessari -->
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="bi bi-shield-check me-2"></i>{{ __('Cookie Necessari') }}
                                </h6>
                                <span class="badge bg-light text-dark">{{ __('SEMPRE ATTIVI') }}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="card-text small">{{ __('Questi cookie sono essenziali per il funzionamento del sito web e non possono essere disabilitati.') }}</p>
                            <ul class="small mb-0">
                                <li>{{ __('Preferenze di lingua') }}</li>
                                <li>{{ __('Sicurezza delle sessioni') }}</li>
                                <li>{{ __('Consenso ai cookie') }}</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Cookie Analitici -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="bi bi-bar-chart me-2"></i>{{ __('Cookie Analitici') }}
                                </h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="analyticsToggle">
                                    <label class="form-check-label" for="analyticsToggle"></label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="card-text small">{{ __('Ci aiutano a capire come interagisci con il sito web, fornendoci informazioni anonime.') }}</p>
                            <ul class="small mb-2">
                                <li>{{ __('Google Analytics 4') }}</li>
                                <li>{{ __('Statistiche delle visite') }}</li>
                                <li>{{ __('Analisi del comportamento (anonimizzato)') }}</li>
                            </ul>
                            <div class="small text-muted">
                                <strong>{{ __('Conservazione:') }}</strong> {{ __('26 mesi') }}<br>
                                <strong>{{ __('Fornitori:') }}</strong> Google LLC (USA - Clausole Standard UE)
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="acceptOnlyNecessary()">
                        {{ __('Solo Necessari') }}
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveCustomSettings()">
                        {{ __('Salva Impostazioni') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pulsante Progetto Fisso Script -->
    <script>
        // Mobile Menu Toggle
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile hamburger menu functionality
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mobileMenuDropdown = document.getElementById('mobileMenuDropdown');
            const mobileMenuIcon = document.getElementById('mobileMenuIcon');

            if (mobileMenuToggle && mobileMenuDropdown) {
                mobileMenuToggle.addEventListener('click', function() {
                    const isActive = mobileMenuDropdown.classList.contains('active');

                    if (isActive) {
                        // Close menu
                        mobileMenuDropdown.classList.remove('active');
                        mobileMenuToggle.classList.remove('active');
                        mobileMenuIcon.classList.remove('fa-times');
                        mobileMenuIcon.classList.add('fa-bars');
                    } else {
                        // Open menu
                        mobileMenuDropdown.classList.add('active');
                        mobileMenuToggle.classList.add('active');
                        mobileMenuIcon.classList.remove('fa-bars');
                        mobileMenuIcon.classList.add('fa-times');
                    }
                });

                // Close mobile menu when clicking on a menu item
                const mobileMenuItems = document.querySelectorAll('.mobile-menu-item');
                mobileMenuItems.forEach(item => {
                    item.addEventListener('click', function() {
                        mobileMenuDropdown.classList.remove('active');
                        mobileMenuToggle.classList.remove('active');
                        mobileMenuIcon.classList.remove('fa-times');
                        mobileMenuIcon.classList.add('fa-bars');
                    });
                });

                // Close mobile menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!mobileMenuToggle.contains(event.target) &&
                        !mobileMenuDropdown.contains(event.target) &&
                        mobileMenuDropdown.classList.contains('active')) {
                        mobileMenuDropdown.classList.remove('active');
                        mobileMenuToggle.classList.remove('active');
                        mobileMenuIcon.classList.remove('fa-times');
                        mobileMenuIcon.classList.add('fa-bars');
                    }
                });
            }

            // Mobile theme toggle
            const mobileThemeToggle = document.getElementById('mobileThemeToggle');
            if (mobileThemeToggle) {
                mobileThemeToggle.addEventListener('click', function() {
                    const themeToggle = document.getElementById('themeToggle');
                    if (themeToggle) {
                        themeToggle.click();
                    }
                });
            }

            // Gestione intelligente del pulsante fisso
            const btnProgetto = document.getElementById('btnProgettoFisso');
            const cookieBanner = document.getElementById('cookieBanner');
            let lastScrollTop = 0;

            // Nascondi/mostra pulsante durante scroll
            window.addEventListener('scroll', function() {
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    // Scroll down - nascondi pulsante
                    btnProgetto.style.transform = 'translateY(100px) scale(0.8)';
                    btnProgetto.style.opacity = '0.7';
                } else {
                    // Scroll up - mostra pulsante
                    btnProgetto.style.transform = 'translateY(0) scale(1)';
                    btnProgetto.style.opacity = '1';
                }
                lastScrollTop = scrollTop;
            });

            // Adatta posizione se cookie banner è visibile
            function adjustButtonPosition() {
                if (cookieBanner && cookieBanner.style.display !== 'none') {
                    btnProgetto.style.bottom = '120px'; // Sposta sopra il cookie banner
                } else {
                    btnProgetto.style.bottom = '30px'; // Posizione normale
                }
            }

            // Verifica posizione cookie banner
            const observer = new MutationObserver(adjustButtonPosition);
            if (cookieBanner) {
                observer.observe(cookieBanner, {
                    attributes: true,
                    attributeFilter: ['style']
                });
            }
            adjustButtonPosition();

            // Effetto shake occasionale per attirare attenzione
            setInterval(function() {
                if (Math.random() < 0.1) { // 10% possibilità ogni 5 secondi
                    btnProgetto.style.animation = 'shake 0.5s ease-in-out';
                    setTimeout(() => {
                        btnProgetto.style.animation = 'pulse-glow 3s infinite';
                    }, 500);
                }
            }, 5000);

            // Track click del pulsante fisso
            btnProgetto.addEventListener('click', function() {
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'click', {
                        'event_category': 'cta',
                        'event_label': 'floating_project_button',
                        'value': 1
                    });
                }
            });
        });

        // Aggiunge animazione shake
        const shakeKeyframes = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px) rotate(-5deg); }
                75% { transform: translateX(5px) rotate(5deg); }
            }
        `;

        // Inserisce keyframes nel DOM
        const styleSheet = document.createElement('style');
        styleSheet.textContent = shakeKeyframes;
        document.head.appendChild(styleSheet);
    </script>

    <!-- Cookie Management Script -->
    <script>
        // Cookie consent management
        const COOKIE_CONSENT_NAME = 'cavallini_cookie_consent';
        const COOKIE_EXPIRY_DAYS = 365;

        // Check if consent already given
        document.addEventListener('DOMContentLoaded', function() {
            const consent = getCookie(COOKIE_CONSENT_NAME);
            if (!consent) {
                setTimeout(() => {
                    document.getElementById('cookieBanner').style.display = 'block';
                }, 1000);
            } else {
                const consentData = JSON.parse(consent);
                if (consentData.analytics) {
                    // Ready for Google Analytics when GA4_MEASUREMENT_ID is available
                    initializeGoogleAnalytics();
                }
            }
        });

        function setCookie(name, value, days) {
            const expires = new Date();
            expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/;SameSite=Lax';
        }

        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        function acceptAllCookies() {
            const consentData = {
                necessary: true,
                analytics: true,
                timestamp: new Date().toISOString()
            };
            setCookie(COOKIE_CONSENT_NAME, JSON.stringify(consentData), COOKIE_EXPIRY_DAYS);
            hideCookieBanner();
            initializeGoogleAnalytics();
        }

        function acceptOnlyNecessary() {
            const consentData = {
                necessary: true,
                analytics: false,
                timestamp: new Date().toISOString()
            };
            setCookie(COOKIE_CONSENT_NAME, JSON.stringify(consentData), COOKIE_EXPIRY_DAYS);
            hideCookieBanner();
        }

        function showCookieSettings() {
            const modal = new bootstrap.Modal(document.getElementById('cookieSettingsModal'));

            // Set current preferences
            const consent = getCookie(COOKIE_CONSENT_NAME);
            if (consent) {
                const consentData = JSON.parse(consent);
                document.getElementById('analyticsToggle').checked = consentData.analytics;
            }

            modal.show();
        }

        function saveCustomSettings() {
            const analyticsConsent = document.getElementById('analyticsToggle').checked;

            const consentData = {
                necessary: true,
                analytics: analyticsConsent,
                timestamp: new Date().toISOString()
            };

            setCookie(COOKIE_CONSENT_NAME, JSON.stringify(consentData), COOKIE_EXPIRY_DAYS);

            // Close modal and banner
            const modal = bootstrap.Modal.getInstance(document.getElementById('cookieSettingsModal'));
            modal.hide();
            hideCookieBanner();

            if (analyticsConsent) {
                initializeGoogleAnalytics();
            }
        }

        function hideCookieBanner() {
            document.getElementById('cookieBanner').style.display = 'none';
        }

        function initializeGoogleAnalytics() {
            // Check if GA4 Measurement ID is available
            const measurementId = '{{ config("services.google.ga4_measurement_id") ?? "" }}';

            if (measurementId) {
                // Load Google Analytics script
                const script = document.createElement('script');
                script.async = true;
                script.src = `https://www.googletagmanager.com/gtag/js?id=${measurementId}`;
                document.head.appendChild(script);

                // Initialize gtag
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }
                window.gtag = gtag;

                gtag('js', new Date());
                gtag('config', measurementId, {
                    'anonymize_ip': true,
                    'respect_privacy': true,
                    'cookie_flags': 'SameSite=Lax;Secure'
                });

                console.log('Google Analytics 4 initialized with ID:', measurementId);
            } else {
                console.log('Google Analytics 4 ready - awaiting Measurement ID configuration');
            }
        }

        // Function to revoke consent (for privacy policy page)
        function revokeCookieConsent() {
            document.cookie = COOKIE_CONSENT_NAME + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
            location.reload();
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Theme Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');
            const body = document.body;

            // Controlla tema salvato o usa preferenza sistema
            const savedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const currentTheme = savedTheme || (systemPrefersDark ? 'dark' : 'light');

            // Applica tema iniziale
            body.setAttribute('data-bs-theme', currentTheme);
            updateThemeIcon(currentTheme);

            // Toggle tema al click
            themeToggle.addEventListener('click', function() {
                const currentTheme = body.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                body.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeIcon(newTheme);
            });

            function updateThemeIcon(theme) {
                if (theme === 'dark') {
                    themeIcon.className = 'fas fa-sun';
                    themeToggle.title = 'Modalità chiara';
                } else {
                    themeIcon.className = 'fas fa-moon';
                    themeToggle.title = 'Modalità scura';
                }
            }

            // Ascolta cambiamenti preferenza sistema
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (!localStorage.getItem('theme')) {
                    const theme = e.matches ? 'dark' : 'light';
                    body.setAttribute('data-bs-theme', theme);
                    updateThemeIcon(theme);
                }
            });

            // Language cycle functionality
            window.changeLanguage = function() {
                const currentLang = '{{ app()->getLocale() }}';
                let nextLang;

                if (currentLang === 'it') {
                    nextLang = 'en';
                } else if (currentLang === 'en') {
                    nextLang = 'es';
                } else {
                    nextLang = 'it';
                }

                window.location.href = '/set_language/' + nextLang;
            };
        });
    </script>


</body>

</html>
