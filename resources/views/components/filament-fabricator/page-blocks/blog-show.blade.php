@aware(['page'])
<section class="p-8">
    <div class="mx-auto max-w-screen-md">
        <x-curator-glider class="mb-4 w-full rounded-xl" :media="$row->featuredImage" />

        <p class="block antialiased font-sans text-sm font-light leading-normal text-inherit font-medium !text-blue-500">
            @foreach ($row->tags as $tag)
                #{{ $tag->slug }} 
            @endforeach
        </p>
        <h1
            class="block antialiased tracking-normal font-sans text-4xl font-semibold leading-[1.3] text-blue-gray-900 my-4 font-black text-4xl !leading-snug">
            {{ $row->title }}
        </h1>

        {{-- Data di pubblicazione e autore --}}
        <p class="block antialiased font-sans text-sm font-light leading-normal text-inherit text-gray-600 mb-4">
            Pubblicato il <span class="font-medium text-gray-800">{{ date('d M Y', strtotime($row->created_at)) }}</span>
        </p>

        <div class="prose prose-lg max-w-none">
            <style>
                .prose ul { @apply list-disc list-inside mb-4 }
                .prose ol { @apply list-decimal list-inside mb-4 }
                .prose li { @apply mb-2 text-gray-700 }
                .prose p { @apply mb-4 text-gray-700 }
                .prose h1 { @apply text-3xl font-bold mb-4 text-gray-900 }
                .prose h2 { @apply text-2xl font-bold mb-3 text-gray-900 }
                .prose h3 { @apply text-xl font-bold mb-3 text-gray-900 }
                .prose a { @apply text-blue-600 hover:text-blue-800 underline }
                .prose blockquote { @apply border-l-4 border-gray-300 pl-4 italic my-4 }
                .prose code { @apply bg-gray-100 rounded px-1 py-0.5 text-sm }
                .prose pre { @apply bg-gray-100 rounded p-4 mb-4 overflow-x-auto }
                .prose img { @apply rounded-lg my-4 }
                .prose table { @apply w-full border-collapse mb-4 }
                .prose th, .prose td { @apply border border-gray-300 p-2 }
            </style>
            <div class="formatted-content">
                @php
                    function isHtml(string $string): bool {
                        return preg_match('/<[a-z][\s\S]*>/i', $string);
                    }

                    $content = $row->content;
                    if (!isHtml($content)) {
                        // GPT-generated text might have markdown headings or list items
                        // immediately following a sentence without a line break.
                        // This pre-processes the content to add the necessary line breaks
                        // for correct markdown parsing.
                        $content = preg_replace('/([.?!])\s*(##+)/', "$1\n\n$2", $content);
                        $content = \Illuminate\Support\Str::markdown($content, [
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false,
                        ]);
                    }
                @endphp
                {!! $content !!}
            </div>
        </div>

        {{-- Tasto Condividi su Linkedin --}}
        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(request()->fullUrl()) }}&title={{ urlencode($row->title) }}&summary={{ urlencode(strip_tags($row->content)) }}" 
           target="_blank" 
           class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white font-medium text-sm leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out">
            Condividi su Linkedin
        </a>
    </div>
</section>
