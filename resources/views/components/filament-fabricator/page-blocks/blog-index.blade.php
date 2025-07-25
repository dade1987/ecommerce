@props([
    'title',
    'rows',
    'tags',
    'selectedTag'
])
<section class="py-24 bg-gray-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-16">
            <h2 class="font-manrope text-4xl font-extrabold text-gray-900 text-center md:text-left">{{ $title }}</h2>
            <form method="GET" action="" class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('blog-index.search_placeholder') }}" class="w-full md:w-64 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <select name="tag" class="w-full md:w-64 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('blog-index.all_articles') }}</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag->id }}" @if($tag->id == $selectedTag) selected @endif>{{ $tag->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full md:w-auto px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">{{ __('blog-index.filter_button') }}</button>
            </form>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
            @forelse ($rows as $row)
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
                        <a href="/blog/{{ $row->slug ?? $row->id }}"
                           class="text-xl text-gray-800 font-semibold leading-6 mb-3 group-hover:text-indigo-600 transition-colors min-h-[64px]">
                            {{ $row->title }}
                        </a>
                        <p class="text-gray-600 leading-6 mb-6 flex-grow">
                            {{ Str::limit(strip_tags($row->content), 150) }}
                        </p>
                        <div class="flex justify-between items-center">
                            <div class="flex gap-2">
                                @foreach($row->tags as $tag)
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                            <a href="/blog/{{ $row->slug ?? $row->id }}"
                               class="text-indigo-600 font-medium hover:underline flex items-center">
                                {{ __('blog-index.read_more') }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 w-4 h-4" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center text-gray-500">
                    <p>{{ __('blog-index.no_articles_found') }}</p>
                </div>
            @endforelse
        </div>
        <div class="mt-16">
            {{ $rows->withQueryString()->links() }}
        </div>
    </div>
</section>
