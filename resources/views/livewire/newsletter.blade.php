<div>
    @if ($isModalVisible)
        <div class="fixed z-10 inset-0 flex items-center justify-center overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity" aria-hidden="true"></div>

            <div class="inline-block align-middle bg-gray-100 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Vuoi rimanere aggiornato sulle nostre offerte?
                        </h3>
                        <div class="mt-2">
                            <input type="email" wire:model="email"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                placeholder="Inserisci la tua email">
                        </div>
                        <div class="mt-4 text-left">
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model="isPrivacyChecked" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out" required>                                <span class="ml-2 text-sm text-gray-700">
                                    Ho letto e accetto l'
                                    <button type="button" wire:click="openPrivacyModal" class="text-blue-600 hover:text-blue-500 underline">
                                        informativa sulla privacy
                                    </button>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 flex justify-between">
                    <button type="button" wire:click="close"
                        class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                        Chiudi
                    </button>
                    <button type="button" wire:click="save"
    class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
    Iscriviti alla newsletter
</button>

                </div>
            </div>
        </div>
    @endif

    @if ($isPrivacyModalVisible)
        <div class="fixed z-20 inset-0 flex items-center justify-center overflow-y-auto p-4" aria-labelledby="privacy-modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity" aria-hidden="true"></div>

            <div class="relative mx-auto max-w-2xl w-full bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-y-auto shadow-xl transform transition-all sm:p-6" style="margin: auto;">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="privacy-modal-title">
                        Informativa Privacy ai sensi del Regolamento UE 2016/679 (GDPR)
                    </h3>
                    <div class="prose max-w-none text-sm text-gray-700 overflow-y-auto max-h-[60vh]">
                        <p><strong>Titolare del Trattamento dei Dati</strong><br>
                        CAVALLINI DAVIDE<br>
                        Via del musonetto 4<br>
                        30033 Noale (VE) - Italia<br>
                        Telefono: +39 320 4206795<br>
                        P.IVA: IT04914550274<br>
                        Codice Fiscale: CVLDVD87M23L736P</p>

                        <p><strong>Finalità del Trattamento</strong><br>
                        I Suoi dati personali, forniti volontariamente tramite la compilazione del form di iscrizione alla newsletter, saranno trattati per le seguenti finalità:</p>
                        <ul>
                            <li>Invio di comunicazioni informative, promozionali e commerciali relative ai servizi offerti da cavalliniservice.com.</li>
                            <li>Aggiornamenti su novità, offerte speciali e iniziative riservate agli iscritti.</li>
                        </ul>

                        <p><strong>Base Giuridica del Trattamento</strong><br>
                        Il trattamento dei dati è basato sul Suo consenso espresso, ai sensi dell'art. 6, par. 1, lett. a) del GDPR.</p>

                        <p><strong>Modalità del Trattamento</strong><br>
                        I dati saranno trattati in modo lecito, corretto e trasparente, mediante strumenti informatici e telematici, con logiche strettamente correlate alle finalità sopra indicate. Sono adottate misure di sicurezza idonee a prevenire la perdita, l'accesso non autorizzato o l'uso improprio dei dati.</p>

                        <p><strong>Categorie di Destinatari</strong><br>
                        I dati potranno essere comunicati a soggetti terzi, quali:</p>
                        <ul>
                            <li>Fornitori di servizi tecnici e gestionali per l'invio della newsletter.</li>
                            <li>Autorità competenti, ove previsto dalla legge.</li>
                        </ul>

                        <p><strong>Trasferimento dei Dati fuori dall'UE</strong><br>
                            Lo strumento per la gestione e l'invio delle newsletter (Mailchimp) è erogato da una società con sede principale in U.S.A., designata quale Responsabile del trattamento.  Il trattamento dei dati personali avviene in conformità alle garanzie previste al Capo V del GDPR e in dettaglio sulla base delle Clausole Contrattuali Standard e il Data Privacy Framework.</p>

                        <p><strong>Conservazione dei Dati</strong><br>
                        I dati saranno conservati fino alla revoca del consenso da parte dell'interessato o fino alla cessazione delle finalità del trattamento.</p>

                        <p><strong>Diritti dell'Interessato</strong><br>
                        Ai sensi degli artt. 15-22 del GDPR, Lei ha il diritto di:</p>
                        <ul>
                            <li>Accedere ai Suoi dati personali.</li>
                            <li>Richiederne la rettifica o la cancellazione.</li>
                            <li>Opporsi al trattamento.</li>
                            <li>Revocare il consenso in qualsiasi momento.</li>
                            <li>Richiedere la limitazione del trattamento.</li>
                            <li>Richiedere la portabilità dei dati.</li>
                        </ul>
                        <p>Per esercitare i Suoi diritti, può inviare una richiesta al Titolare del Trattamento all'indirizzo email: [inserire email] o al recapito sopra indicato.</p>

                        <p><strong>Revoca del Consenso</strong><br>
                        Può revocare il consenso al trattamento dei dati in qualsiasi momento, cliccando sul link di cancellazione presente in ogni newsletter o contattando direttamente il Titolare.</p>

                        <p><strong>Modifiche all'Informativa</strong><br>
                        La presente informativa potrà essere aggiornata nel tempo. Si invita a consultare periodicamente questa pagina per eventuali modifiche.</p>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6">
                    <button type="button" wire:click="closePrivacyModal"
                        class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                        Chiudi
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
