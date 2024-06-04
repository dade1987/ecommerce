<div class="flex items-center justify-center p-12">
    <div class="mx-auto w-full max-w-[550px]">
        <form method="POST" wire:submit.prevent="send">
            <div class="mb-5">
                <label for="name" class="mb-3 block text-base font-medium text-[#07074D]">
                    Nome Completo
                </label>
                <input wire:model="form_data.name" type="text" name="name" id="name" placeholder="Nome Completo"
                    class="w-full rounded-md border border-[#e0e0e0] bg-white py-3 px-6 text-base font-medium text-[#6B7280] outline-none focus:border-[#6A64F1] focus:shadow-md" />
            </div>
            <div class="mb-5">
                <label for="email" class="mb-3 block text-base font-medium text-[#07074D]">
                    Email
                </label>
                <input wire:model="form_data.email" type="email" name="email" id="email"
                    placeholder="example@domain.com"
                    class="w-full rounded-md border border-[#e0e0e0] bg-white py-3 px-6 text-base font-medium text-[#6B7280] outline-none focus:border-[#6A64F1] focus:shadow-md" />
            </div>
            <div class="mb-5">
                <label for="subject" class="mb-3 block text-base font-medium text-[#07074D]">
                    Oggetto
                </label>
                <input wire:model="form_data.subject" type="text" name="subject" id="subject"
                    placeholder="Scrivi l'oggetto"
                    class="w-full rounded-md border border-[#e0e0e0] bg-white py-3 px-6 text-base font-medium text-[#6B7280] outline-none focus:border-[#6A64F1] focus:shadow-md" />
            </div>
            <div class="mb-5">
                <label for="message" class="mb-3 block text-base font-medium text-[#07074D]">
                    Messaggio
                </label>
                <textarea wire:model="form_data.message" rows="4" name="message" id="message" placeholder="Scrivi il messaggio"
                    class="w-full resize-none rounded-md border border-[#e0e0e0] bg-white py-3 px-6 text-base font-medium text-[#6B7280] outline-none focus:border-[#6A64F1] focus:shadow-md"></textarea>
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
                    Invia
                </button>
            </div>


        </form>


    </div>

</div>
