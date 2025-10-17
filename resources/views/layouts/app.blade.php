<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Title -->
    <title>{{ $pageTitle ?? config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="{{ asset('images/logo15.png') }}" type="image/jpeg">
    <link rel="apple-touch-icon" href="{{ asset('images/logo15.png') }}">

    

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-D7G8J1GF0M"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-D7G8J1GF0M');
    </script>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-1001130032">
    </script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'AW-1001130032');
    </script>
    <!-- Event snippet for Lead da chiamata conversion page
In your html page, add the snippet and call gtag_report_conversion when someone clicks on the chosen link or button. -->
    <script>
        function gtag_report_conversion(url) {

            console.log('gtag_report_conversion');

            var callback = function() {
                if (typeof(url) != 'undefined') {
                    window.location = url;
                }
            };
            gtag('event', 'conversion', {
                'send_to': 'AW-1001130032/DjJfCITk1N0aELCQsN0D',
                'event_callback': callback
            });
            return false;
        }
    </script>

    <!-- Meta Description -->
    <meta name="description" content="{{ $pageDescription ?? 'Cavallini Service, a Noale: soluzioni software su misura, integrazione AI e cybersecurity. Ottimizza flussi di lavoro e proteggi i dati aziendali.' }}">

    <!-- Meta Robots -->
    <meta name="robots" content="index, follow">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ request()->url() }}">

    <!-- Open Graph Tags -->
    <meta property="og:title" content="{{ $pageTitle ?? config('app.name', 'Laravel') }}">
    <meta property="og:description" content="{{ $pageDescription ?? 'Cavallini Service, a Noale: soluzioni software su misura, integrazione AI e cybersecurity. Ottimizza flussi di lavoro e proteggi i dati aziendali.' }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/logo15.png') }}">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:type" content="website">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle ?? config('app.name', 'Laravel') }}">
    <meta name="twitter:description" content="{{ $pageDescription ?? 'Cavallini Service, a Noale: soluzioni software su misura, integrazione AI e cybersecurity. Ottimizza flussi di lavoro e proteggi i dati aziendali.' }}">
    <meta name="twitter:image" content="{{ $ogImage ?? asset('images/logo15.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @filamentStyles
    @stack('styles')

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "Cavallini Service",
            "url": "https://cavalliniservice.com",
            "logo": "{{ asset('images/logo15.png') }}"
        }
    </script>
    @stack('structured-data')

   

</head>

<body class="font-sans antialiased">
    <livewire:notifications />
    <div class="h-full bg-gray-100">
        @if (isset($header))
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endif

        <main class="h-full">
            {{ $slot }}
        </main>
    </div>

    @filamentScripts
    @livewireScriptConfig
    @stack('scripts')
    @livewireScripts
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('gtag_report_conversion', (event) => {
                if (typeof gtag_report_conversion === 'function') {
                    gtag_report_conversion();
                    console.log('gtag_report_conversion called via app.blade.php');
                } else {
                    console.error('gtag_report_conversion function not found');
                }
            });
        });
    </script>
</body>

</html>
