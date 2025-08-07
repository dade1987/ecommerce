@aware(['page'])

<div class="py-12 bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center @if($alignment === 'right') lg:grid-flow-col-dense @endif">
            <div class="lg:col-span-6 @if($alignment === 'right') lg:col-start-7 @endif">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="p-4 {{ $card_header_color_class ?? 'bg-violet-600' }} text-white">
                        <div class="flex items-center">
                            <i class="{{ $card_header_icon }} text-2xl mr-3"></i>
                            <div>
                                <h5 class="font-bold">{{ $card_header_title }}</h5>
                                <small>{{ $card_header_subtitle }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="relative mb-4">
                            <div class="{{ $mockup_color_class ?? 'bg-gradient-to-r from-violet-500 to-fuchsia-500' }} p-6 rounded-lg text-white text-center">
                                <i class="{{ $mockup_icon }} text-4xl mb-3 inline-block"></i>
                                <h6 class="font-semibold">{{ $mockup_title }}</h6>
                                <p class="text-sm">{{ $mockup_text }}</p>
                            </div>
                        </div>
                        <h6 class="font-bold text-violet-600 dark:text-violet-400">Problema Risolto:</h6>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $problem_text }}</p>

                        @if(!empty($results))
                        <h6 class="font-bold text-green-600 dark:text-green-400">Risultati Ottenuti:</h6>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            @foreach($results as $result)
                            <div>
                                <h6 class="{{ $result['color_class'] ?? 'text-green-600' }} dark:{{ $result['color_class'] ?? 'text-green-400' }} font-bold text-xl">{{ $result['value'] }}</h6>
                                <small class="text-gray-500 dark:text-gray-400">{{ $result['label'] }}</small>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="lg:col-span-6">
                <div class="lg:px-4">
                    <h4 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">{{ $details_title }}</h4>

                    @if(!empty($details_builder))
                        @foreach($details_builder as $detail)
                            @php $blockType = $detail['type']; @endphp

                            @if($blockType === 'technologies')
                                <div class="grid sm:grid-cols-2 gap-4">
                                    @foreach($detail['data']['tech_items'] as $item)
                                    <div class="flex items-center p-3 bg-gray-100 dark:bg-gray-800 rounded-lg">
                                        <div class="{{ $item['icon_bg_class'] ?? 'bg-violet-600' }} text-white rounded-full p-2 mr-3">
                                            <i class="{{ $item['icon'] }}"></i>
                                        </div>
                                        <div>
                                            <h6 class="font-semibold text-gray-900 dark:text-white">{{ $item['title'] }}</h6>
                                            <small class="text-gray-600 dark:text-gray-400">{{ $item['subtitle'] }}</small>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @elseif($blockType === 'features')
                                <ul class="space-y-2">
                                     @foreach($detail['data']['feature_items'] as $item)
                                        <li class="flex items-center"><i class="{{ $item['icon'] }} text-green-500 mr-2"></i><span class="text-gray-700 dark:text-gray-300">{{ $item['text'] }}</span></li>
                                    @endforeach
                                </ul>
                            @elseif($blockType === 'accordion')
                                <div x-data="{ open: 0 }" class="space-y-2">
                                    @foreach($detail['data']['accordion_items'] as $item)
                                    <div x-data="{
                                            id: {{ $loop->index }},
                                            get expanded() { return this.id === this.open },
                                            set expanded(value) { this.open = value ? this.id : null },
                                        }" role="region" class="bg-white dark:bg-gray-800 rounded-lg shadow">
                                        <h2>
                                            <button @click="expanded = !expanded" :aria-expanded="expanded" class="flex items-center justify-between w-full p-4 font-semibold text-left text-gray-800 dark:text-white">
                                                <span><i class="{{ $item['icon'] }} mr-2"></i>{{ $item['title'] }}</span>
                                                <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': expanded }"></i>
                                            </button>
                                        </h2>
                                        <div x-show="expanded" x-collapse>
                                            <div class="p-4 text-gray-600 dark:text-gray-400">
                                                <ul class="space-y-1">
                                                    @foreach(explode("\n", $item['content']) as $line)
                                                        @if(trim($line))
                                                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2 text-xs"></i>{{ $line }}</li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @elseif($blockType === 'timeline')
                                <div class="relative border-l border-gray-200 dark:border-gray-700">
                                     @foreach($detail['data']['timeline_items'] as $item)
                                        <div class="mb-10 ml-6">
                                            <span class="absolute flex items-center justify-center w-8 h-8 rounded-full -left-4 {{ $item['color_class'] ?? 'bg-violet-600' }} text-white">
                                                {{ $item['step'] }}
                                            </span>
                                            <div class="ml-2">
                                                <h6 class="font-bold text-gray-900 dark:text-white">{{ $item['title'] }}</h6>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $item['text'] }}</p>
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
    </div>
</div> 