<div>
    @if (session()->has('success'))
        <div class="rounded-md bg-green-100 p-4 text-center dark:bg-green-900">
            <p class="text-lg font-semibold text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
    @else
        <form wire:submit.prevent="submit" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('cv-upload-form.full_name') }}</label>
                <input type="text" wire:model.defer="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('cv-upload-form.email_address') }}</label>
                <input type="email" wire:model.defer="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                @error('email') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="cv" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('cv-upload-form.attach_cv') }}</label>
                <div x-data="{ isUploading: false, progress: 0 }" x-on:livewire-upload-start="isUploading = true" x-on:livewire-upload-finish="isUploading = false" x-on:livewire-upload-error="isUploading = false" x-on:livewire-upload-progress="progress = $event.detail.progress" class="mt-1 flex justify-center rounded-md border-2 border-dashed border-gray-300 px-6 pt-5 pb-6 dark:border-gray-600">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600 dark:text-gray-400">
                            <label for="cv" class="relative cursor-pointer rounded-md bg-white font-medium text-orange-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-orange-500 focus-within:ring-offset-2 hover:text-orange-500 dark:bg-gray-900">
                                <span>{{ __('cv-upload-form.upload_a_file') }}</span>
                                <input id="cv" wire:model="cv" type="file" class="sr-only">
                            </label>
                            <p class="pl-1">{{ __('cv-upload-form.or_drag_and_drop') }}</p>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-500">{{ __('cv-upload-form.file_formats') }}</p>
                        <div x-show="isUploading" class="w-full">
                            <progress max="100" x-bind:value="progress" class="w-full"></progress>
                        </div>
                        @if ($cv && !$errors->has('cv'))
                            <p class="text-sm text-green-600">{{ __('cv-upload-form.file_uploaded', ['filename' => $cv->getClientOriginalName()]) }}</p>
                        @endif
                        @error('cv') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-start">
                <div class="flex h-5 items-center">
                    <input id="privacy_consent" wire:model.defer="privacy_consent" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                </div>
                <div class="ml-3 text-sm">
                    <div class="font-medium text-gray-700 dark:text-gray-300 prose dark:prose-invert max-w-none">
                        {!! $this->cleanedPrivacyPolicyText !!}
                    </div>
                    @error('privacy_consent') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <button type="submit" wire:loading.attr="disabled" class="group relative flex w-full justify-center rounded-md border border-transparent bg-blue-500 py-3 px-4 text-lg font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                    <span wire:loading.remove wire:target="submit">
                        {{ __('cv-upload-form.submit_application') }}
                    </span>
                    <span wire:loading wire:target="submit">
                        {{ __('cv-upload-form.submitting') }}
                    </span>
                </button>
            </div>
        </form>
    @endif
</div>
