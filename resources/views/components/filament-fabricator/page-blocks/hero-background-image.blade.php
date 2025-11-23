<!-- Section: Design Block -->
<section class="mb-40">


    <!-- Jumbotron -->
    <div class="relative overflow-hidden bg-cover bg-no-repeat"
        style="
        background-position: 50%;
        background-image: url('{{ $imageUrl }}');
        height: 80vh;
      ">
        <div
            class="absolute top-0 right-0 bottom-0 left-0 h-full w-full overflow-hidden bg-[hsla(0,0%,0%,0.5)] bg-fixed">
            <div class="flex h-full items-center justify-center">
                <div class="px-6 text-center text-white md:px-12">
                    <h1 class="mt-2 mb-15 text-5xl font-bold tracking-tight md:text-6xl xl:text-7xl">
                        {{ $textOne }}
                    </h1>
                    <h2 class="mt-2 mb-16 text-4xl font-bold tracking-tight md:text-5xl xl:text-6xl">
                        <span>{{ $textTwo }}</span>
                    </h2>
                    @if (!empty($linkButton))
                        <a href="{{ $linkButton }}" type="button"
                            class="inline-flex items-center justify-center rounded border-2 border-neutral-50 px-6 py-4 text-center text-sm font-medium uppercase leading-normal text-neutral-50 transition duration-150 ease-in-out hover:border-neutral-100 hover:bg-neutral-100 hover:bg-opacity-10 hover:text-neutral-100 focus:border-neutral-100 focus:text-neutral-100 focus:outline-none focus:ring-0 active:border-neutral-200 active:text-neutral-200 md:px-[46px] md:py-0 md:pt-[14px] md:pb-[12px]"
                            data-te-ripple-init data-te-ripple-color="light">
                            {{ $textButton }}
                        </a>
                    @endif

                    @if (!empty($linkSecondButton))
                        <a href="{{ $linkSecondButton }}" type="button"
                            class="ml-4 inline-flex items-center justify-center rounded border-2 border-neutral-50 px-6 py-4 text-center text-sm font-medium uppercase leading-normal text-neutral-50 transition duration-150 ease-in-out hover:border-neutral-100 hover:bg-neutral-100 hover:bg-opacity-10 hover:text-neutral-100 focus:border-neutral-100 focus:text-neutral-100 focus:outline-none focus:ring-0 active:border-neutral-200 active:text-neutral-200 md:ml-10 md:px-[46px] md:py-0 md:pt-[14px] md:pb-[12px]"
                            data-te-ripple-init data-te-ripple-color="light">
                            {{ $textSecondButton }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- Jumbotron -->
</section>
<!-- Section: Design Block -->
