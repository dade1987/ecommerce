@props([
    'rows',
    'tags',
    'selectedTag',
])

@php
    $selectedTag = $selectedTag ?? request('tag');
    $search = request('search');

    $isHtml = static fn(string $string): bool => preg_match('/<[a-z][\s\S]*>/i', $string) === 1;
    $excerptFromContent = static function (?string $raw) use ($isHtml): string {
        $raw = (string) ($raw ?? '');
        $html = $raw;

        if (! $isHtml($html)) {
            $html = preg_replace('/([.?!])\s*(##+)/', "$1\n\n$2", $html);
            $html = \Illuminate\Support\Str::markdown($html, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
        }

        return \Illuminate\Support\Str::limit(trim(strip_tags($html)), 160);
    };
@endphp

<section class="bg-slate-900 text-slate-100">
    <!-- HERO -->
    <div class="relative overflow-hidden border-b border-slate-800/70">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-32 -right-32 h-80 w-80 rounded-full bg-indigo-500/15 blur-3xl"></div>
            <div class="absolute -bottom-32 -left-32 h-80 w-80 rounded-full bg-blue-500/10 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-10 sm:py-14">
            <div class="flex flex-col gap-6">
                <h1 class="text-3xl font-extrabold tracking-tight sm:text-5xl">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-100 to-indigo-200">
                        Blog
                    </span>
                </h1>
                <p class="max-w-3xl text-base text-slate-200/90 sm:text-lg">
                    Articoli pratici su interpretariato, traduzione e comunicazione multilingue. Scegli un tema, leggi in 2 minuti, poi prova Interpreter.
                </p>

                <!-- Filters -->
                <form method="GET" action="" class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                    <div class="sm:col-span-6">
                        <label class="sr-only" for="search">Cerca</label>
                        <input
                            id="search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Cerca un articolo…"
                            class="w-full rounded-xl border border-slate-700 bg-slate-800/70 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-400 focus:border-indigo-400/70 focus:ring-0"
                        />
                    </div>
                    <div class="sm:col-span-4">
                        <label class="sr-only" for="tag">Tag</label>
                        <select
                            id="tag"
                            name="tag"
                            class="w-full rounded-xl border border-slate-700 bg-slate-800/70 px-4 py-3 text-sm text-slate-100 focus:border-indigo-400/70 focus:ring-0"
                        >
                            <option value="">Tutti i tag</option>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" @if((string) $tag->id === (string) $selectedTag) selected @endif>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <button
                            type="submit"
                            class="w-full rounded-xl bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 px-4 py-3 text-sm font-bold text-white hover:from-blue-700 hover:via-blue-800 hover:to-indigo-800 transition"
                        >
                            Filtra
                        </button>
                    </div>
                </form>

                <div class="flex flex-wrap gap-3">
                    <a href="/" class="inline-flex items-center rounded-xl border border-slate-700 bg-slate-800 px-4 py-2.5 text-sm font-semibold text-slate-100 hover:bg-slate-700 transition">
                        Vai a Interpreter →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- GRID -->
    <div class="mx-auto max-w-7xl px-4 py-8 sm:py-10">
        @if($rows->count() === 0)
            <div class="rounded-2xl border border-slate-800 bg-slate-950/30 p-8 text-center text-slate-300">
                Nessun articolo trovato. Prova a cambiare filtro.
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($rows as $row)
                    <article class="group overflow-hidden rounded-2xl border border-slate-800 bg-slate-950/30 hover:bg-slate-950/50 transition">
                        <a href="/blog/{{ $row->slug ?? $row->id }}" class="block">
                            <div class="relative">
                                <x-curator-glider class="w-full aspect-[16/9] object-cover" :media="$row->featuredImage" />
                                <div class="absolute top-3 left-3 inline-flex items-center rounded-full border border-slate-700 bg-slate-900/70 px-2.5 py-1 text-xs text-slate-200">
                                    {{ $row->created_at->format('d/m/Y') }}
                                </div>
                            </div>
                            <div class="p-5">
                                <h2 class="text-lg font-bold leading-snug text-slate-100 group-hover:text-indigo-200 transition line-clamp-2">
                                    {{ $row->title }}
                                </h2>

                                <p class="mt-2 text-sm text-slate-300 line-clamp-3">
                                    {{ $excerptFromContent($row->content) }}
                                </p>

                                @if($row->tags?->isNotEmpty())
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @foreach($row->tags->take(3) as $tag)
                                            <span class="inline-flex items-center rounded-full border border-slate-700 bg-slate-800/70 px-2.5 py-1 text-xs text-slate-200">
                                                #{{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-5 flex items-center justify-between">
                                    <span class="text-sm font-semibold text-indigo-300 group-hover:text-indigo-200 transition">
                                        Leggi →
                                    </span>
                                    <span class="text-xs text-slate-400">
                                        2–4 min
                                    </span>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $rows->withQueryString()->links() }}
            </div>
        @endif
    </div>
</section>


