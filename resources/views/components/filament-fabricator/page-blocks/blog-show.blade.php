@aware(['page'])
<section class="p-8">
    <div class="mx-auto max-w-screen-md">
        <x-curator-glider class="mb-4 h-[28rem] w-full rounded-xl object-cover" :media="$row->featuredImage" />

        <p class="block antialiased font-sans text-sm font-light leading-normal text-inherit font-medium !text-blue-500">
            @foreach ($row->tags as $tag)
                #{{ $tag->slug }} 
            @endforeach
        </p>
        <h2
            class="block antialiased tracking-normal font-sans text-4xl font-semibold leading-[1.3] text-blue-gray-900 my-4 font-black text-4xl !leading-snug">
            {{ $row->title }}
        </h2>

        {{-- Data di pubblicazione e autore --}}
        <p class="block antialiased font-sans text-sm font-light leading-normal text-inherit text-gray-600 mb-4">
            Pubblicato il <span class="font-medium text-gray-800">{{ date('d M Y', strtotime($row->created_at)) }}</span>
        </p>

        <p
            class="block antialiased font-sans text-base font-light leading-relaxed text-inherit font-normal !text-gray-500">
            {!! $row->content !!}
        </p>

        {{-- Tasto Condividi su Linkedin --}}
        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(request()->fullUrl()) }}&title={{ urlencode($row->title) }}&summary={{ urlencode($row->content) }}" 
           target="_blank" 
           class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white font-medium text-sm leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out">
            Condividi su Linkedin
        </a>
    </div>
</section>
