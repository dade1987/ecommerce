@aware(['page'])

<!-- Sezione con immagine -->
<!-- component -->
<link rel="stylesheet" href="https://demos.creative-tim.com/notus-js/assets/styles/tailwind.css">
<link rel="stylesheet" href="https://demos.creative-tim.com/notus-js/assets/vendor/@fortawesome/fontawesome-free/css/all.min.css">

<main class="profile-page">
    <section class="relative block h-500-px ">
        <div class="absolute top-0 w-full h-full bg-center bg-cover"
            style="
            background-image:url('{{ $background }}');
          ">
            <span id="blackOverlay"></span>
        </div>
        <div class="top-auto bottom-0 left-0 right-0 w-full absolute pointer-events-none overflow-hidden h-70-px"
            style="transform: translateZ(0px)">
            <svg class="absolute bottom-0 overflow-hidden" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"
                version="1.1" viewBox="0 0 2560 100" x="0" y="0">
                <polygon class="text-blueGrey-200 fill-current" points="2560 0 2560 100 0 100"></polygon>
            </svg>
        </div>
    </section>

</main>



<!-- Timeline -->
<section>
    <div class="relative wrap overflow-hidden p-10 h-full">
        <div class="border-2-2 absolute border-opacity-20 border-amber-200 h-full border" style="left: 50%"></div>

@php
 $index=1   
@endphp
        @foreach ($cards as $item)
            
            <!-- Timeline destra -->
        <div class="mb-8 flex justify-between items-center w-full right-timeline">
            <div class="order-1 w-5/12"></div>
            <div class="z-20 flex items-center order-1 bg-lime-600 shadow-xl w-8 h-8 rounded-full">
                <h1 class="mx-auto font-semibold text-lg text-white">{{ $index }}</h1>
            </div>
            <div class="order-1 bg-lime-500 rounded-lg shadow-xl w-5/12 px-6 py-4">
                <h3 class="mb-3 font-bold text-gray-800 text-xl">{{ $item['title_left'] }}</h3>
                <p class="text-sm leading-snug tracking-wide text-gray-900 text-opacity-100">{{ $item['text_left'] }}</p>
            </div>
        </div>

        <!-- Timeline sinistra -->
        <div class="mb-8 flex justify-between flex-row-reverse items-center w-full left-timeline">
            <div class="order-1 w-5/12"></div>
            <div class="z-20 flex items-center order-1 bg-lime-600 shadow-xl w-8 h-8 rounded-full">
                <h1 class="mx-auto text-white font-semibold text-lg">{{ $index + 1 }}</h1>
            </div>
            <div class="order-1 bg-amber-200 rounded-lg shadow-xl w-5/12 px-6 py-4">
                <h3 class="mb-3 font-bold text-grey-800 text-xl">{{ $item['title_right'] }}</h3>
                <p class="text-sm font-medium leading-snug tracking-wide text-grey-800 text-opacity-100">
                    {{ $item['text_right'] }}</p>
            </div>
        </div> 
        @php
        $index+=2
        @endphp @endforeach

    </div>

</section>
