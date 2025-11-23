@aware(['page'])


<footer class="bg-white dark:bg-gray-900">
    <div class="mx-auto w-full max-w-7xl p-4 py-6 lg:py-8">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-3 sm:gap-6">
            {{-- Colonna Dati Anagrafici --}}
            <div>
                <address class="not-italic text-sm text-gray-900 dark:text-white">
                    <p>DAVIDE CAVALLINI</p>
                    <p>via del Musonetto 4 - Noale VE - Italy</p>
                    <p>{{ __('footer.contact') }}: +39 320 4206795</p>
                    <p>{{ __('footer.vat_number') }} IT04914550274</p>
                    <p>{{ __('footer.tax_code') }}: CVLDVD87M23L736P</p>
                    <p><a href="https://www.linkedin.com/in/davidecavallini/" class="hover:underline">LinkedIn</a></p>
                </address>
            </div>

            {{-- Colonna Bandi Europei --}}
            <div class="flex flex-col items-center">
                <div class="flex items-center space-x-3">
                    <img src="/images/european_flag.png" alt="European Union Flag" class="h-8 w-12 rounded object-cover shadow-sm">
                    <p class="max-w-md text-center text-sm text-gray-600 dark:text-gray-300">
                        {{ __('footer.eu_digitalization') }}
                    </p>
                </div>
            </div>

            {{-- Colonna Legale --}}
            <div class="md:text-right">
                <h2 class="mb-6 text-sm font-semibold uppercase text-gray-900 dark:text-white">{{ __('footer.legal') }}</h2>
                <ul class="font-medium text-gray-500 dark:text-gray-400">
                    <li class="mb-4">
                        <a href="https://www.iubenda.com/privacy-policy/75773543" class="iubenda-white iubenda-noiframe iubenda-embed hover:underline" title="Privacy Policy">Privacy Policy</a>
                        <script type="text/javascript">(function(w,d) {var loader = function () {var s = d.createElement("script"), tag = d.getElementsByTagName("script")[0]; s.src="https://cdn.iubenda.com/iubenda.js"; tag.parentNode.insertBefore(s,tag);}; if(w.addEventListener){w.addEventListener("load", loader, false);}else if(w.attachEvent){w.attachEvent("onload", loader);}})(window, document);</script>
                    </li>
                    <li>
                        <a href="#" class="hover:underline">Terms &amp; Conditions</a>
                    </li>
                </ul>
            </div>
        </div>
        <hr class="my-6 border-gray-200 sm:mx-auto dark:border-gray-700 lg:my-8" />
        <div class="sm:flex sm:items-center sm:justify-between">
          <span class="text-sm text-gray-500 sm:text-center dark:text-gray-400">© 2025 <a href="https://cavalliniservice.com/" class="hover:underline">CavalliniService.com™</a>. {{ __('footer.all_rights_reserved') }}
          </span>
          
      </div>
    </div>
</footer>
