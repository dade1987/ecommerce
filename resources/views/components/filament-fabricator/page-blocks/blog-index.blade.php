@aware(['page'])
<section class="py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h2 class="font-manrope text-4xl font-bold text-gray-900 text-center mb-16">Cavallini Service Blog</h2>
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($rows as $row)
                <div class="group w-full border border-gray-300 rounded-2xl">
                    <div class="flex items-center">
                        <x-curator-glider class="rounded-t-2xl w-full" :media="$row->featuredImage" />
                    </div>
                    <div class="p-4 lg:p-6 transition-all duration-300 rounded-b-2xl group-hover:bg-gray-50">
                        <span
                            class="text-indigo-600 font-medium mb-3 block">{{ date('M d, Y', strtotime($row->created_at)) }}</span>
                        <h4 class="text-xl text-gray-900 font-medium leading-8 mb-5">{{ $row->title }}</h4>
                        <p class="text-gray-500 leading-6 mb-10">{{ $row->content }}</p>
                        <a href="javascript:;" class="cursor-pointer text-lg text-indigo-600 font-semibold">Read
                            more..</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
