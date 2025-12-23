@aware(['page'])

@php
    /** @var \Illuminate\Support\Collection|\App\Models\MenuItem[] $items */
    $items = $items ?? collect();
    $title = isset($title) ? (string) $title : '';
@endphp

@if($items->isNotEmpty())
    <section class="w-full bg-slate-900 border-t border-slate-800/70">
        <div class="mx-auto max-w-7xl px-4 py-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="text-sm font-semibold text-slate-200">
                    {{ trim($title) !== '' ? $title : 'Link utili' }}
                </div>

                <nav class="flex flex-wrap gap-2">
                    @foreach($items as $item)
                        <a
                            href="{{ $item->href }}"
                            rel="noopener noreferrer"
                            class="inline-flex items-center rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm font-medium text-slate-200 hover:bg-slate-700 hover:text-white hover:border-indigo-400/60 transition"
                        >
                            {{ $item->name }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </section>
@endif


