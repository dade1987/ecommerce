<div>
    @foreach ($quotes->groupBy('thread_id') as $thread => $quotess)
        @if (count($quotess) > 2)
            <h4 class="text-xl font-semibold text-gray-800">{{ $thread }}</h4>
            <ul class="list-disc list-inside pl-4 space-y-2">
                @foreach ($quotess as $quote)
                    <li class="text-lg text-gray-700"><strong>{{ $quote->role }}</strong>:{{ $quote->content }}</li>
                @endforeach
            </ul>
            <br>
        @endif
    @endforeach
</div>
