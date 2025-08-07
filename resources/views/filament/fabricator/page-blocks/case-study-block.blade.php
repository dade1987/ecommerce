@aware(['page'])

<div class="row mb-5 align-items-center @if($alignment === 'right') flex-row-reverse @endif">
    <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="card border-0 shadow-lg">
            <div class="card-header {{ $card_header_color_class }} text-white">
                <div class="d-flex align-items-center">
                    <i class="{{ $card_header_icon }} fs-4 me-3"></i>
                    <div>
                        <h5 class="mb-1">{{ $card_header_title }}</h5>
                        <small>{{ $card_header_subtitle }}</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="position-relative mb-3">
                    <div class="{{ $mockup_color_class }} p-4 rounded text-white text-center" style="height: 200px;">
                        <i class="{{ $mockup_icon }} fs-1 mb-3"></i>
                        <h6>{{ $mockup_title }}</h6>
                        <p class="small mb-0">{{ $mockup_text }}</p>
                    </div>
                </div>
                <h6 class="fw-bold text-primary">Problema Risolto:</h6>
                <p class="text-muted small mb-3">{{ $problem_text }}</p>

                @if($results)
                <h6 class="fw-bold text-success">Risultati Ottenuti:</h6>
                <div class="row g-2 mb-3">
                    @foreach($results as $result)
                    <div class="col-4 text-center">
                        <h6 class="{{ $result['color_class'] }} fw-bold">{{ $result['value'] }}</h6>
                        <small>{{ $result['label'] }}</small>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="@if($alignment === 'left') ps-lg-4 @else pe-lg-4 @endif">
            <h4 class="fw-bold mb-4">{{ $details_title }}</h4>

            @if($details_builder)
                @foreach($details_builder as $detail)
                    @if($detail['type'] === 'technologies')
                        <div class="row g-3">
                            @foreach($detail['data']['tech_items'] as $item)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="{{ $item['icon_bg_class'] }} text-white rounded-circle p-2 me-3">
                                        <i class="{{ $item['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ $item['title'] }}</h6>
                                        <small class="text-muted">{{ $item['subtitle'] }}</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @elseif($detail['type'] === 'features')
                        <ul class="list-unstyled">
                             @foreach($detail['data']['feature_items'] as $item)
                                <li><i class="{{ $item['icon'] }} text-success me-2"></i>{{ $item['text'] }}</li>
                            @endforeach
                        </ul>
                    @elseif($detail['type'] === 'accordion')
                        <div class="accordion" id="accordion-{{ $loop->parent->index }}">
                            @foreach($detail['data']['accordion_items'] as $item)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button @if(!$loop->first) collapsed @endif" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $loop->parent->parent->index }}-{{ $loop->index }}">
                                        <i class="{{ $item['icon'] }} me-2"></i>{{ $item['title'] }}
                                    </button>
                                </h2>
                                <div id="collapse-{{ $loop->parent->parent->index }}-{{ $loop->index }}" class="accordion-collapse collapse @if($loop->first) show @endif" data-bs-parent="#accordion-{{ $loop->parent->index }}">
                                    <div class="accordion-body">
                                        <ul class="list-unstyled">
                                            @foreach(explode("\n", $item['content']) as $line)
                                                <li><i class="bi bi-check text-success me-2"></i>{{ $line }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @elseif($detail['type'] === 'timeline')
                        <div class="timeline">
                             @foreach($detail['data']['timeline_items'] as $item)
                                <div class="d-flex mb-4">
                                    <div class="{{ $item['color_class'] }} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                        <strong>{{ $item['step'] }}</strong>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="fw-bold">{{ $item['title'] }}</h6>
                                        <p class="text-muted small mb-0">{{ $item['text'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</div> 