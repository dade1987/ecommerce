<div id="contact-form">
    <div class="flex items-center justify-center p-12">
        <div class="mx-auto w-full max-w-[550px]">
            <form method="POST" wire:submit.prevent="send">
                <div class="mb-5">
                    <label for="name" class="mb-3 block text-base font-medium text-[#07074D]">
                        {{ __('contact-form.full_name') }}
                    </label>
                    <input wire:model="form_data.name" type="text" name="name" id="name"
                        placeholder="{{ __('contact-form.full_name_placeholder') }}"
                        class="w-full rounded-md border border-[#e0e0e0] bg-white py-3 px-6 text-base font-medium text-[#6B7280] outline-none focus:border-[#6A64F1] focus:shadow-md" />
                </div>
                <div class="mb-5">
                    <label for="email" class="mb-3 block text-base font-medium text-[#07074D]">
                        {{ __('contact-form.email') }}
                    </label>
                    <input wire:model="form_data.email" type="email" name="email" id="email"
                        placeholder="{{ __('contact-form.email_placeholder') }}"
                        class="w-full rounded-md border border-[#e0e0e0] bg-white py-3 px-6 text-base font-medium text-[#6B7280] outline-none focus:border-[#6A64F1] focus:shadow-md" />
                </div>
                <div class="mb-5">
                    <label for="subject" class="mb-3 block text-base font-medium text-[#07074D]">
                        {{ __('contact-form.subject') }}
                    </label>
                    <input wire:model="form_data.subject" type="text" name="subject" id="subject"
                        placeholder="{{ __('contact-form.subject_placeholder') }}"
                        class="w-full rounded-md border border-[#e0e0e0] bg-white py-3 px-6 text-base font-medium text-[#6B7280] outline-none focus:border-[#6A64F1] focus:shadow-md" />
                </div>
                <div class="mb-5">
                    <label for="message" class="mb-3 block text-base font-medium text-[#07074D]">
                        {{ __('contact-form.message') }}
                    </label>
                    <textarea wire:model="form_data.message" rows="4" name="message" id="message" placeholder="{{ __('contact-form.message_placeholder') }}"
                        class="w-full resize-none rounded-md border border-[#e0e0e0] bg-white py-3 px-6 text-base font-medium text-[#6B7280] outline-none focus:border-[#6A64F1] focus:shadow-md"></textarea>
                </div>

                <div class="mb-5">
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model="isPrivacyChecked"
                            class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out" required>
                        <span class="ml-2 text-sm text-gray-700">
                            {{ __('contact-form.privacy_policy_agreement') }}
                            <button type="button" wire:click="openPrivacyModal"
                                class="text-blue-600 hover:text-blue-500 underline">
                                {{ __('contact-form.privacy_policy') }}
                            </button>
                        </span>
                    </label>
                </div>

                <div>
                    @if (session()->has('message'))
                        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
                            role="alert">
                            {{ session('message') }}
                        </div>
                    @endif
                </div>

                <div>
                    <button type="submit"
                        class="hover:shadow-form rounded-md bg-[#6A64F1] py-3 px-8 text-base font-semibold text-white outline-none">
                        {{ __('contact-form.submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($isPrivacyModalVisible)
        <div class="fixed z-20 inset-0 flex items-center justify-center overflow-y-auto p-4"
            aria-labelledby="privacy-modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity" aria-hidden="true"></div>

            <div class="relative mx-auto max-w-2xl w-full bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-y-auto shadow-xl transform transition-all sm:p-6"
                style="margin: auto;">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="privacy-modal-title">
                        {{ __('contact-form.privacy_policy_title') }}
                    </h3>
                    <div class="prose max-w-none text-sm text-gray-700 overflow-y-auto max-h-[60vh]">
                        <p><strong>{{ __('contact-form.data_controller_title') }}</strong><br>
                            DAVIDE CAVALLINI<br>
                            via del Musonetto, 4 - Noale (VE)<br>
                            t. +39 320 4206795<br>
                            P.IVA IT04914550274<br>
                            C.F.: CVLDVD87M23L736P</p>

                        <p><strong>{{ __('contact-form.processing_purpose_title') }}</strong><br>
                            {{ __('contact-form.processing_purpose_text') }}</p>

                        <p><strong>{{ __('contact-form.legal_basis_title') }}</strong><br>
                            {{ __('contact-form.legal_basis_text') }}</p>

                        <p><strong>{{ __('contact-form.processing_methods_title') }}</strong><br>
                            {{ __('contact-form.processing_methods_text') }}</p>

                        <p><strong>{{ __('contact-form.data_subject_rights_title') }}</strong><br>
                            {{ __('contact-form.data_subject_rights_text') }}</p>

                        <p>{{ __('contact-form.exercise_rights_text') }}</p>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 flex justify-center">
                    <button type="button" wire:click="closePrivacyModal"
                        class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                        {{ __('contact-form.close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
