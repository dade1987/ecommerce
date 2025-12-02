<div class="flex flex-col h-[70vh]">
    {{-- Area messaggi scrollabile --}}
    <div class="flex-1 space-y-4 overflow-y-auto pr-1">
        @foreach($messages as $message)
            @php
                $isUser = $message->role === 'user';
            @endphp

            <div class="flex {{ $isUser ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%] rounded-2xl px-4 py-3 shadow-sm border border-slate-700/60
                    {{ $isUser
                        ? 'bg-slate-800 text-slate-50'
                        : 'bg-slate-900 text-slate-100' }}">

                    <div class="flex items-center gap-2 mb-1 text-[11px]">
                        <span class="inline-flex items-center gap-1 font-semibold
                            {{ $isUser ? 'text-sky-300' : 'text-emerald-300' }}">
                            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full text-[10px]
                                {{ $isUser ? 'bg-sky-500 text-white' : 'bg-emerald-500 text-white' }}">
                                {{ $isUser ? 'U' : 'B' }}
                            </span>
                            {{ $isUser ? 'Utente' : 'Chatbot' }}
                        </span>

                        <span class="text-[11px] text-slate-400">
                            {{ $message->created_at->format('d/m/Y H:i:s') }}
                        </span>
                    </div>

                    <div class="text-sm leading-relaxed whitespace-pre-wrap break-words">
                        {{ $message->content }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Footer info thread --}}
    <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <span class="font-semibold">Thread ID:</span>
                <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-[11px] break-all">
                    {{ $threadId }}
                </code>
            </div>
            <div>
                <span class="font-semibold">Totale messaggi:</span>
                {{ count($messages) }}
            </div>
        </div>
    </div>
</div>

