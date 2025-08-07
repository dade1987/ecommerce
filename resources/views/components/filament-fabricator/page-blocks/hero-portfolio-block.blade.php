@aware(['page'])
<section class="hero-section bg-primary text-white position-relative overflow-hidden">
    <div class="container py-5">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4 text-gradient">
                    {{ $heading }}
                </h1>
                <p class="lead mb-4">
                    {{ $subheading }}
                </p>
                @if($badges)
                <div class="hero-badges d-flex flex-wrap justify-content-center gap-3">
                    @foreach($badges as $badge)
                        <span class="badge bg-light text-dark px-3 py-2">{{ $badge['text'] }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="hero-decoration"></div>
</section> 