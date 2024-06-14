@aware(['page'])
<section class="p-8">
    <div class="mx-auto max-w-screen-md">
        <x-curator-glider class="mb-4 h-[28rem] w-full rounded-xl object-cover" :media="$row->featuredImage" />

        <p class="block antialiased font-sans text-sm font-light leading-normal text-inherit font-medium !text-blue-500">
            @foreach ($row->tags as $tag)
                #{{ $tag->slug }} 
            @endforeach
        </p>
        <h2
            class="block antialiased tracking-normal font-sans text-4xl font-semibold leading-[1.3] text-blue-gray-900 my-4 font-black text-4xl !leading-snug">
            {{ $row->title }}
        </h2>
        <p
            class="block antialiased font-sans text-base font-light leading-relaxed text-inherit font-normal !text-gray-500">
            {{ $row->content }}
        </p>
    </div>
</section>
