<x-app-layout>
    <div class="flex items-center justify-center" style="height: calc(100vh - 200px);">
        <div class="text-center">
            <h1 class="text-6xl font-bold text-gray-800">404</h1>
            <p class="text-2xl font-light text-gray-600 mb-4">Pagina Non Trovata</p>
            <p class="mb-8 text-gray-500">Oops! La pagina che stai cercando non esiste o è stata spostata.</p>
            <a href="{{ url('/') }}" class="px-6 py-3 text-white bg-blue-600 rounded-md hover:bg-blue-700">
                Torna alla Home
            </a>
        </div>
    </div>
</x-app-layout> 