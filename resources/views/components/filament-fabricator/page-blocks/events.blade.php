@aware(['page'])


<section class="grid min-h-screen p-8 place-items-center bg-amber-200">
    <div class="container grid grid-cols-1 gap-8 my-auto lg:grid-cols-2">
        @foreach ($rows as $row)
            <div
                class="relative flex-col bg-clip-border rounded-xl bg-transparent text-gray-700 shadow-none grid gap-2 item sm:grid-cols-2">
                <div class="relative bg-clip-border rounded-xl overflow-hidden bg-white text-gray-700 shadow-lg m-0">

                    <x-curator-glider class="object-cover w-full h-full" :media="$row->featuredImage" />
                </div>
                <div class="p-6 px-2 sm:pr-6 sm:pl-4">
                    <a href="#"
                        class="block antialiased tracking-normal font-sans text-xl font-semibold leading-snug text-blue-gray-900 mb-2 normal-case transition-colors hover:text-gray-700">
                        {{ $row->name }}
                    </a>
                    <p
                        class="block antialiased font-sans text-base leading-relaxed text-inherit mb-8 font-normal !text-gray-500">
                        {{ $row->description }}</p>
                    <div class="flex items-center gap-4"><img src='{{ $logoUrl }}' alt="Logo"
                            class="inline-block relative object-cover object-center !rounded-full w-12 h-12 rounded-lg" />
                        <div>

                            <p class="block antialiased font-sans text-sm leading-normal text-gray-700 font-normal">
                                {{ $row->starts_at }} <br> {{ $row->ends_at }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>
