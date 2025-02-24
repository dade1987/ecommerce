@aware(['page'])
<h1 class="text-2xl font-bold text-center mt-4">FORMIFICIO STF s.r.l.</h1>
<h2 class="text-xl font-semibold text-center mb-4">MODULO DI INSERIMENTO ORDINI</h2>
<div class="px-4 py-4 md:py-8 bg-gray-100">
    <div class="max-w-7xl mx-auto bg-white shadow-md rounded-lg p-6">
        <form action="/api/calzaturiero/process-order" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="flex flex-col">
                <label for="file" class="mb-2 text-lg font-medium text-gray-700">Carica il file:</label>
                <input type="file" id="file" name="file" required class="p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">Invia</button>
            </div>
        </form>
    </div>
</div>
