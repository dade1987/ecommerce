@aware(['page'])
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4 text-center">
            @if($stats)
                @foreach($stats as $stat)
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <i class="{{ $stat['icon'] }} fs-1 {{ $stat['icon_color_class'] }} mb-3"></i>
                                <h3 class="fw-bold {{ $stat['number_color_class'] }}">{{ $stat['number'] }}</h3>
                                <p class="text-muted mb-0">{{ $stat['label'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section> 