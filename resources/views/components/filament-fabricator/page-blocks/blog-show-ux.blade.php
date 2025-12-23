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

    $compact = (bool) ($compact_mode ?? true);
    $ctaLabel = isset($cta_primary_label) ? (string) $cta_primary_label : 'Prenota una Call';
@endphp

<section class="bg-slate-900 text-slate-100">
    <!-- HERO -->
    <div class="relative overflow-hidden border-b border-slate-800/70">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-32 -right-32 h-72 w-72 rounded-full bg-indigo-500/15 blur-3xl"></div>
            <div class="absolute -bottom-32 -left-32 h-72 w-72 rounded-full bg-blue-500/10 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-5xl px-4 py-10 sm:py-14">
            <div class="flex flex-col gap-4">
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

                <h1 class="text-3xl font-extrabold leading-tight tracking-tight sm:text-5xl">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-100 to-indigo-200">
                        {{ $row->title }}
                    </span>
                </h1>

                @if($row->summary)
                    <p class="max-w-3xl text-base leading-relaxed text-slate-200/90 sm:text-lg">
                        {{ $row->summary }}
                    </p>
                @endif

                <div class="flex flex-wrap gap-3 pt-2">
                    <a
                        href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(request()->fullUrl()) }}&title={{ urlencode($row->title) }}&summary={{ urlencode(strip_tags($row->content)) }}"
                        target="_blank"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-800 px-4 py-2.5 text-sm font-semibold text-slate-100 hover:bg-slate-700 transition"
                    >
                        Condividi su Linkedin
                        <span class="ml-2">→</span>
                    </a>

                    <div class="inline-flex">
                        @livewire('open-calendar-button')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="mx-auto max-w-5xl px-4 py-8 sm:py-10">
        <div class="rounded-2xl border border-slate-800 bg-slate-950/30 p-5 sm:p-8">
            <div class="prose prose-invert max-w-none prose-headings:scroll-mt-24 prose-a:text-indigo-300 hover:prose-a:text-indigo-200">
                @if($compact)
                    <details class="group rounded-xl border border-slate-800 bg-slate-900/40 p-4" open>
                        <summary class="cursor-pointer list-none select-none">
                            <div class="flex items-center justify-between gap-4">
                                <div class="text-sm font-semibold text-slate-100">Leggi l’articolo completo</div>
                                <div class="text-xs text-slate-300 group-open:hidden">Mostra</div>
                                <div class="text-xs text-slate-300 hidden group-open:inline">Nascondi</div>
                            </div>
                            <div class="mt-2 text-xs text-slate-300">
                                Tip: su mobile puoi aprire/chiudere per non scrollare troppo.
                            </div>
                        </summary>
                        <div class="mt-4">
                            {!! $content !!}
                        </div>
                    </details>
                @else
                    {!! $content !!}
                @endif
            </div>
        </div>
    </div>

    <!-- STICKY CTA (mobile) -->
    <div class="sm:hidden sticky bottom-0 border-t border-slate-800/80 bg-slate-900/95 backdrop-blur">
        <div class="mx-auto max-w-5xl px-4 py-3 flex items-center gap-3">
            <a
                href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(request()->fullUrl()) }}&title={{ urlencode($row->title) }}&summary={{ urlencode(strip_tags($row->content)) }}"
                target="_blank"
                class="flex-1 inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm font-semibold text-slate-100"
            >
                Condividi
            </a>
            <div class="flex-1">
                @livewire('open-calendar-button')
            </div>
        </div>
    </div>
</section>


