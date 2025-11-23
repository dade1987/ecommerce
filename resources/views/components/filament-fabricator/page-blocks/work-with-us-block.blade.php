@aware(['page'])
@props([
    'title',
    'subtitle',
    'image',
    'privacy_policy_text',
])
<section class="bg-white dark:bg-gray-900 py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-2 lg:items-center lg:gap-24">
            
            <div class="flex flex-col justify-center text-center lg:text-left">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    {{ $title }}
                </h2>
                <div class="prose dark:prose-invert mt-6 max-w-xl mx-auto lg:mx-0 text-lg leading-8 text-gray-600 dark:text-gray-300">
                    {!! $subtitle !!}
                </div>
                @if($image)
                    <div class="mt-8 flex justify-center lg:justify-start">
                        <x-curator-glider :media="$image" class="w-full max-w-md rounded-lg shadow-xl" />
                    </div>
                @endif
            </div>

            
            <div class="rounded-lg bg-gray-50 p-8 shadow-lg dark:bg-gray-800/50">
                @livewire('cv-upload-form', ['privacy_policy_text' => $privacy_policy_text])
            </div>

        </div>
    </div>
</section> 