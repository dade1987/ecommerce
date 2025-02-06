<div>
    <div class="flex items-center justify-center p-12">
        <div class="mx-auto w-full max-w-[550px]">
            <form method="POST" wire:submit.prevent="send">
                <div class="mb-5">
                    <label for="name" class="mb-3 block text-base font-medium text-[#07074D]">
                        Nome Completo
                    </label>
                    <input wire:model="form_data.name" type="text" name="name" id="name"
                        placeholder="Nome Completo"
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

                <div class="mb-5">
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model="isPrivacyChecked"
                            class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out" required>
                        <span class="ml-2 text-sm text-gray-700">
                            Ho letto e accetto l'
                            <button type="button" wire:click="openPrivacyModal"
                                class="text-blue-600 hover:text-blue-500 underline">
                                informativa sulla privacy
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
                        Invia
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
                        Informativa Privacy
                    </h3>
                    <div class="prose max-w-none text-sm text-gray-700 overflow-y-auto max-h-[60vh]">
                        <p><strong>Titolare del Trattamento dei Dati</strong><br>
                            FLORIAN GIULIANO<br>
                            via Bettin 13/A - I Zero Branco TV<br>
                            t. +39 391 1352526<br>
                            P.IVA IT04362300263<br>
                            C.F.: FLRGLN61M23M171N</p>

                        <p><strong>Finalità del Trattamento</strong><br>
                            I Suoi dati personali, forniti volontariamente tramite la compilazione del form di contatto,
                            saranno trattati per rispondere alle richieste inviate.</p>

                        <p><strong>Base Giuridica del Trattamento</strong><br>
                            Il trattamento dei dati è basato sul Suo consenso espresso, ai sensi dell'art. 6, par. 1,
                            lett.
                            a) del GDPR.</p>

                        <p><strong>Modalità del Trattamento</strong><br>
                            I dati saranno trattati in modo lecito, corretto e trasparente, mediante strumenti
                            informatici e
                            telematici, con logiche strettamente correlate alle finalità sopra indicate. Sono adottate
                            misure di sicurezza idonee a prevenire la perdita, l'accesso non autorizzato o l'uso
                            improprio
                            dei dati.</p>

                        <p><strong>Diritti dell'Interessato</strong><br>
                            Ai sensi degli artt. 15-22 del GDPR, Lei ha il diritto di accedere ai Suoi dati personali,
                            richiederne la rettifica o la cancellazione, opporsi al trattamento, revocare il consenso in
                            qualsiasi momento, richiedere la limitazione del trattamento e la portabilità dei dati.</p>

                        <p>Per esercitare i Suoi diritti, può inviare una richiesta al Titolare del Trattamento
                            all'indirizzo email: [inserire email] o al recapito sopra indicato.</p>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 flex justify-center">
                    <button type="button" wire:click="closePrivacyModal"
                        class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                        Chiudi
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
