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
@php
    $isHtml = static fn(string $string): bool => preg_match('/<[a-z][\s\S]*>/i', $string) === 1;

    $content = (string) ($row->content ?? '');
    if (! $isHtml($content)) {
        $content = preg_replace('/([.?!])\s*(##+)/', "$1\n\n$2", $content);
        $content = \Illuminate\Support\Str::markdown($content, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    $excerpt = \Illuminate\Support\Str::limit(trim(strip_tags($content)), 520);
    $keyPoints = array_values(array_filter(array_map('trim', preg_split('/(?<=[.?!])\s+/', $row->summary ?: $excerpt) ?: [])));
    $keyPoints = array_slice($keyPoints, 0, 3);
@endphp

<section class="relative bg-white text-slate-900 h-[calc(100svh-8rem)] overflow-hidden">
    <style>
        @keyframes blogFloat {
            0% { transform: translate3d(0, 0, 0) scale(1); }
            50% { transform: translate3d(8px, -10px, 0) scale(1.02); }
            100% { transform: translate3d(0, 0, 0) scale(1); }
        }
        .blog-blob { animation: blogFloat 9s ease-in-out infinite; }
        .blog-blob2 { animation: blogFloat 11s ease-in-out infinite reverse; }
    </style>

    <!-- Background WOW -->
    <div class="absolute inset-0 pointer-events-none">
        <div class="blog-blob absolute -top-28 -right-28 h-72 w-72 rounded-full bg-indigo-500/18 blur-3xl"></div>
        <div class="blog-blob2 absolute -bottom-28 -left-28 h-72 w-72 rounded-full bg-sky-500/16 blur-3xl"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-white via-white/70 to-white"></div>
    </div>

    <div class="relative mx-auto max-w-7xl h-full px-4 py-6 sm:py-8">
        <div class="h-full grid grid-rows-[auto,1fr] gap-4">
            <!-- HEADER / HERO (no page scroll) -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-stretch">
                <div class="lg:col-span-7">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-white/70 px-2.5 py-1 shadow-sm">
                            Pubblicato il {{ $row->created_at->format('d/m/Y') }}
                        </span>
                        @if($row->tags?->isNotEmpty())
                            @foreach($row->tags->take(4) as $tag)
                                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white/60 px-2.5 py-1 shadow-sm">
                                    #{{ $tag->name }}
                                </span>
                            @endforeach
                        @endif
                    </div>

                    <h1 class="mt-3 text-3xl font-extrabold leading-tight tracking-tight sm:text-5xl">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-700">
                            {{ $row->title }}
                        </span>
                    </h1>

                    <p class="mt-3 max-w-3xl text-sm leading-relaxed text-slate-700 sm:text-base">
                        {{ $row->summary ?: $excerpt }}
                    </p>

                    <!-- CTA: semplici e dirette -->
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a
                            href="https://cavalliniservice.com/home"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 hover:from-blue-700 hover:via-blue-800 hover:to-indigo-800 transition"
                        >
                            Sono una software house <span class="ml-2">→</span>
                        </a>

                        <a
                            href="https://cavalliniservice.com/gestionale-operativo"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white/80 px-5 py-3 text-sm font-bold text-slate-900 hover:bg-white transition shadow-sm"
                        >
                            Sono un’azienda <span class="ml-2 text-indigo-700">→</span>
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="h-full overflow-hidden rounded-2xl border border-slate-200 bg-white/70 shadow-xl">
                        @if($row->featuredImage)
                            <x-curator-glider class="h-full w-full object-cover" :media="$row->featuredImage" :alt="$row->title" />
                        @else
                            <div class="h-full grid place-items-center p-8 text-slate-600">
                                Nessuna immagine disponibile.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- BODY (internal scroll only) -->
            <div class="min-h-0 grid grid-cols-1 gap-4 lg:grid-cols-12">
                <!-- Side: key points -->
                <div class="lg:col-span-4 min-h-0">
                    <div class="h-full rounded-2xl border border-slate-200 bg-white/70 p-4 sm:p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-900">In breve</div>
                        <div class="mt-2 text-xs text-slate-500">Scansiona in 10 secondi, poi approfondisci a destra.</div>

                        @if(count($keyPoints) > 0)
                            <ul class="mt-4 space-y-3 text-sm text-slate-700">
                                @foreach($keyPoints as $point)
                                    <li class="flex gap-3">
                                        <span class="mt-1 inline-flex h-5 w-5 flex-none items-center justify-center rounded-full bg-indigo-600/10 text-indigo-700 text-xs">
                                            ✓
                                        </span>
                                        <span class="leading-relaxed text-slate-700">{{ $point }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="mt-4 text-sm text-slate-700">
                                {{ $excerpt }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Content panel -->
                <div class="lg:col-span-8 min-h-0">
                    <div class="h-full rounded-2xl border border-slate-200 bg-white/70 overflow-hidden flex flex-col shadow-sm">
                        <div class="flex items-center justify-between gap-4 border-b border-slate-200 bg-white/70 px-4 py-3 sm:px-6">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-slate-900">Articolo completo</div>
                                <div class="text-xs text-slate-500">Scroll interno: la pagina resta “una schermata”.</div>
                            </div>
                        </div>

                        <div class="min-h-0 overflow-y-auto px-4 pb-6 pt-4 sm:px-6">
                            <div class="prose max-w-none prose-headings:scroll-mt-24 prose-a:text-indigo-700 hover:prose-a:text-indigo-800">
                                {!! $content !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sticky CTA (mobile) -->
    <div class="sm:hidden sticky bottom-0 border-t border-slate-200/80 bg-white/90 backdrop-blur">
        <div class="mx-auto max-w-7xl px-4 py-3 flex items-center gap-3">
            <a
                href="https://cavalliniservice.com/home"
                target="_blank"
                rel="noopener"
                class="flex-1 inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 px-4 py-3 text-sm font-bold text-white"
            >
                Software house
            </a>
            <a
                href="https://cavalliniservice.com/gestionale-operativo"
                target="_blank"
                rel="noopener"
                class="flex-1 inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white/90 px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm"
            >
                Azienda
            </a>
        </div>
    </div>
</section>
