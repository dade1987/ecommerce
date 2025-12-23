@aware(['page'])

@php
    /** @var \Illuminate\Support\Collection|\App\Models\MenuItem[] $items */
    $items = $items ?? collect();
    $title = isset($title) ? (string) $title : '';
@endphp

@if($items->isNotEmpty())
    <section class="w-full py-6">
        <div class="mx-auto max-w-6xl px-4">
            <div class="rounded-2xl border border-slate-700/60 bg-slate-900/60 backdrop-blur px-4 py-4">
                @if(trim($title) !== '')
                    <div class="mb-3 text-sm font-semibold tracking-wide text-slate-200">
                        {{ $title }}
                    </div>
                @endif

                <div class="flex flex-wrap gap-2">
                    @foreach($items as $item)
                        <a
                            href="{{ $item->href }}"
                            rel="noopener noreferrer"
                            class="inline-flex items-center rounded-full border border-slate-600/70 bg-slate-800/60 px-3 py-2 text-sm font-medium text-slate-100 hover:bg-indigo-500/20 hover:border-indigo-400/60 hover:text-white transition"
                        >
                            {{ $item->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif


