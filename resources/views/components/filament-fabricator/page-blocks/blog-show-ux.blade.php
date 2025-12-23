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
    "name": "Interpreter",
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
    function isHtml(string $string): bool {
        return preg_match('/<[a-z][\s\S]*>/i', $string) === 1;
    }

    $content = (string) ($row->content ?? '');
    if (!isHtml($content)) {
        $content = preg_replace('/([.?!])\s*(##+)/', "$1\n\n$2", $content);
        $content = \Illuminate\Support\Str::markdown($content, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    $excerpt = \Illuminate\Support\Str::limit(trim(strip_tags($content)), 520);
@endphp

<section class="bg-slate-900 text-slate-100">
    <!-- HERO -->
    <div class="relative overflow-hidden border-b border-slate-800/70">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-32 -right-32 h-72 w-72 rounded-full bg-indigo-500/15 blur-3xl"></div>
            <div class="absolute -bottom-32 -left-32 h-72 w-72 rounded-full bg-blue-500/10 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-10 sm:py-14">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 lg:items-center">
                <!-- Immagine -->
                <div class="lg:col-span-5">
                    @if($row->featuredImage)
                        <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-950/20 shadow-xl">
                            <x-curator-glider class="h-auto w-full" :media="$row->featuredImage" :alt="$row->title" />
                        </div>
                    @else
                        <div class="rounded-2xl border border-slate-800 bg-slate-950/20 p-8 text-slate-300">
                            Nessuna immagine disponibile.
                        </div>
                    @endif
                </div>

                <!-- Testo -->
                <div class="lg:col-span-7">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-300">
                        <span class="inline-flex items-center rounded-full border border-slate-700 bg-slate-800 px-2.5 py-1">
                            Pubblicato il {{ $row->created_at->format('d/m/Y') }}
                        </span>
                        @if($row->tags?->isNotEmpty())
                            @foreach($row->tags as $tag)
                                <span class="inline-flex items-center rounded-full border border-slate-700 bg-slate-800/70 px-2.5 py-1">
                                    #{{ $tag->name }}
                                </span>
                            @endforeach
                        @endif
                    </div>

                    <h1 class="mt-4 text-3xl font-extrabold leading-tight tracking-tight sm:text-5xl">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-100 to-indigo-200">
                            {{ $row->title }}
                        </span>
                    </h1>

                    <p class="mt-4 max-w-3xl text-base leading-relaxed text-slate-200/90 sm:text-lg">
                        {{ $row->summary ?: $excerpt }}
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <!-- CTA primaria: vai a Interpreter (home) -->
                        <a
                            href="/"
                            class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 hover:from-blue-700 hover:via-blue-800 hover:to-indigo-800 transition"
                        >
                            Vai a Interpreter
                            <span class="ml-2">→</span>
                        </a>

                        <!-- Share -->
                        <a
                            href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(request()->fullUrl()) }}&title={{ urlencode($row->title) }}&summary={{ urlencode(strip_tags($row->content)) }}"
                            target="_blank"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-800 px-5 py-3 text-sm font-semibold text-slate-100 hover:bg-slate-700 transition"
                        >
                            Condividi su Linkedin
                        </a>
                    </div>

                    <!-- micro value props (no scroll) -->
                    <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-slate-800 bg-slate-950/20 p-3">
                            <div class="text-xs font-semibold text-slate-200">In breve</div>
                            <div class="mt-1 text-xs text-slate-300">Punti chiave e FAQ, senza scroll infinito.</div>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/20 p-3">
                            <div class="text-xs font-semibold text-slate-200">Mobile-first</div>
                            <div class="mt-1 text-xs text-slate-300">CTA sempre visibile e lettura comoda.</div>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/20 p-3">
                            <div class="text-xs font-semibold text-slate-200">Azione</div>
                            <div class="mt-1 text-xs text-slate-300">Prova subito Interpreter dalla home.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="mx-auto max-w-7xl px-4 py-8 sm:py-10">
        <div class="rounded-2xl border border-slate-800 bg-slate-950/30 p-5 sm:p-8">
            <!-- No-scroll by default: mostra riepilogo + contenuto espandibile -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
                <div class="lg:col-span-4">
                    <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4">
                        <div class="text-sm font-semibold text-slate-100">Riepilogo</div>
                        <div class="mt-2 text-sm text-slate-200/90 leading-relaxed">
                            {{ $row->summary ?: $excerpt }}
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <details class="group rounded-xl border border-slate-800 bg-slate-900/40 p-4">
                        <summary class="cursor-pointer list-none select-none">
                            <div class="flex items-center justify-between gap-4">
                                <div class="text-sm font-semibold text-slate-100">Apri l’articolo completo</div>
                                <div class="text-xs text-slate-300 group-open:hidden">Mostra</div>
                                <div class="text-xs text-slate-300 hidden group-open:inline">Nascondi</div>
                            </div>
                            <div class="mt-2 text-xs text-slate-300">
                                Default compatto per evitare scroll: apri solo se ti serve.
                            </div>
                        </summary>
                        <div class="mt-4 prose prose-invert max-w-none prose-headings:scroll-mt-24 prose-a:text-indigo-300 hover:prose-a:text-indigo-200">
                            {!! $content !!}
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </div>

    <!-- STICKY CTA (mobile) -->
    <div class="sm:hidden sticky bottom-0 border-t border-slate-800/80 bg-slate-900/95 backdrop-blur">
        <div class="mx-auto max-w-5xl px-4 py-3 flex items-center gap-3">
            <a
                href="/"
                class="flex-1 inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 px-4 py-3 text-sm font-bold text-white"
            >
                Vai a Interpreter
            </a>
            <a
                href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(request()->fullUrl()) }}&title={{ urlencode($row->title) }}&summary={{ urlencode(strip_tags($row->content)) }}"
                target="_blank"
                class="flex-1 inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm font-semibold text-slate-100"
            >
                Condividi
            </a>
        </div>
    </div>
</section>


