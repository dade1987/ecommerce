<section class="py-24 bg-gray-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h2 class="font-manrope text-4xl font-extrabold text-gray-900 text-center mb-16">{{ $title }}</h2>
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($rows as $row)
                <div class="group w-full overflow-hidden bg-white rounded-3xl shadow-lg transition-transform transform hover:-translate-y-2 flex flex-col">
                    <div class="relative">
                        <a href="/blog/{{ $row->slug ?? $row->id }}">
                            <x-curator-glider class="rounded-t-3xl w-full" :media="$row->featuredImage" />
                            <span class="absolute top-3 right-3 bg-indigo-600 text-white text-xs font-medium px-3 py-1 rounded-lg shadow">
                                {{ date('M d, Y', strtotime($row->created_at)) }}
                            </span>
                        </a>
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <a href="/blog/{{ $row->slug ?? $row->id }}" class="text-xl text-gray-800 font-semibold leading-6 mb-3 group-hover:text-indigo-600 transition-colors min-h-[64px]">
                            {{ $row->title }}
                        </a>
                        <p class="text-gray-600 leading-6 mb-6 flex-grow">
                            {!! Str::limit($row->content, 150) !!}...
                        </p>
                        <a href="/blog/{{ $row->slug ?? $row->id }}"
                           class="text-indigo-600 font-medium hover:underline flex items-center mt-auto">
                            Read more
                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
