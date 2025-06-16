<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Title -->
    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="{{ asset('images/logo15.jpeg') }}" type="image/jpeg">
    <link rel="apple-touch-icon" href="{{ asset('images/logo15.jpeg') }}">

    {{-- <!-- Meta Description -->
    <meta name="description" content="@yield('meta-description', config('metatag.default.description'))">

    <!-- Meta Robots -->
    <meta name="robots" content="@yield('meta-robots', config('metatag.default.robots'))">

    <!-- Canonical URL -->
    <link rel="canonical" href="@yield('canonical', config('metatag.default.canonical'))">

    <!-- Open Graph Tags -->
    <meta property="og:title" content="@yield('og:title', config('metatag.default.title'))">
    <meta property="og:description" content="@yield('og:description', config('metatag.default.description'))">
    <meta property="og:image" content="@yield('og:image', config('metatag.default.image'))">
    <meta property="og:url" content="@yield('og:url', config('metatag.default.canonical'))">
    <meta property="og:type" content="@yield('og:type', config('metatag.default.type'))">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="@yield('twitter:card', config('metatag.default.twitter_card'))">
    <meta name="twitter:title" content="@yield('twitter:title', config('metatag.default.title'))">
    <meta name="twitter:description" content="@yield('twitter:description', config('metatag.default.description'))">
    <meta name="twitter:image" content="@yield('twitter:image', config('metatag.default.image'))">
--}}
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @filamentStyles
    @stack('styles')

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

</body>

</html>
