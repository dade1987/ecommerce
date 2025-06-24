@push('structured-data')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "{{ request()->fullUrl() }}"
  },
  "headline": "{{ $row->title }}",
  "image": "{{ $row->featuredImage?->url }}",
  "datePublished": "{{ $row->created_at->toIso8601String() }}",
  "dateModified": "{{ $row->updated_at->toIso8601String() }}",
  "author": {
    "@type": "Person",
    "name": "{{ $row->author->name ?? 'Davide Cavallini' }}"
  },
   "publisher": {
    "@type": "Organization",
    "name": "Cavallini Service",
    "logo": {
      "@type": "ImageObject",
      "url": "{{ asset('images/logo15.png') }}"
    }
  },
  "description": "{{ $row->summary }}"
}
</script>
@endpush

@aware(['page'])
<section class="bg-white dark:bg-gray-900 py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Featured Image -->
            @if($row->featuredImage)
                <div class="flex justify-center mb-8">
                    <img class="h-auto max-w-full rounded-lg" src="{{ $row->featuredImage->url }}" alt="{{ $row->title }}">
                </div>
            @endif

            <!-- Article Title -->
            <h1 class="text-3xl md:text-5xl font-extrabold text-gray-900 dark:text-white mb-4 text-center">
                {{ $row->title }}
            </h1>

            <!-- Meta Data -->
            <div class="text-center text-gray-500 dark:text-gray-400 mb-8">
                <span>Pubblicato il {{ $row->created_at->format('d F Y') }}</span>
                @if($row->tags->isNotEmpty())
                    <span class="mx-2">&bull;</span>
                    <div class="inline-flex gap-2 mt-2 md:mt-0">
                        @foreach ($row->tags as $tag)
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full dark:bg-gray-700 dark:text-gray-300">#{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Article Content -->
            <div class="prose prose-lg max-w-none dark:prose-invert">
                 <style>
                    .prose ul { @apply list-disc list-inside mb-4; }
                    .prose ol { @apply list-decimal list-inside mb-4; }
                    .prose li { @apply mb-2; }
                    .prose p { @apply mb-6 leading-relaxed; }
                    .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 { @apply font-bold; }
                    .prose a { @apply text-blue-600 hover:text-blue-800 underline dark:text-blue-400 dark:hover:text-blue-300; }
                    .prose blockquote { @apply border-l-4 border-gray-300 pl-4 italic my-6 dark:border-gray-600; }
                    .prose code { @apply bg-gray-100 rounded px-1 py-0.5 text-sm dark:bg-gray-800; }
                    .prose pre { @apply bg-gray-100 rounded p-4 mb-6 overflow-x-auto dark:bg-gray-800; }
                    .prose img { @apply rounded-lg my-6; }
                    .prose table { @apply w-full border-collapse mb-6; }
                    .prose th, .prose td { @apply border border-gray-300 p-2 dark:border-gray-600; }
                </style>
                <div class="formatted-content">
                    @php
                        function isHtml(string $string): bool {
                            return preg_match('/<[a-z][\s\S]*>/i', $string);
                        }

                        $content = $row->content;
                        if (!isHtml($content)) {
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

            <!-- Share Button -->
            <div class="text-center mt-10">
                <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(request()->fullUrl()) }}&title={{ urlencode($row->title) }}&summary={{ urlencode(strip_tags($row->content)) }}" 
                   target="_blank" 
                   class="inline-flex items-center justify-center px-5 py-3 text-base font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-900">
                    Condividi su Linkedin
                    <svg class="w-5 h-5 ml-2 -mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </a>
            </div>
        </div>
    </div>
</section>
