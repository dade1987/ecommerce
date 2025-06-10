@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-center">Lista Ristoranti</h1>
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif
    <div class="flex justify-end mb-6">
        <a href="{{ route('restaurants.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">Aggiungi Ristorante</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($restaurants as $restaurant)
            <div class="bg-white shadow rounded-lg p-6 flex flex-col justify-between">
                <div>
                    <h2 class="text-xl font-semibold mb-2">{{ $restaurant->name }}</h2>
                    <p class="text-gray-600 mb-1"><span class="font-medium">Fascia prezzo:</span> {{ $restaurant->price_range ?? '-' }}</p>
                    <p class="text-gray-600 mb-1"><span class="font-medium">Telefono:</span> {{ $restaurant->phone_number ?? '-' }}</p>
                    <p class="text-gray-600 mb-1"><span class="font-medium">Email:</span> {{ $restaurant->email ?? '-' }}</p>
                    <p class="text-gray-600 mb-1"><span class="font-medium">Sito web:</span> 
                        @if($restaurant->website)
                            <a href="{{ $restaurant->website }}" class="text-blue-500 underline" target="_blank">{{ $restaurant->website }}</a>
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div class="flex justify-between items-center mt-4">
                    <a href="{{ route('restaurants.show', $restaurant) }}" class="text-blue-600 hover:underline">Visualizza</a>
                    <a href="{{ route('restaurants.edit', $restaurant) }}" class="text-yellow-600 hover:underline">Modifica</a>
                    <form action="{{ route('restaurants.destroy', $restaurant) }}" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questo ristorante?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Elimina</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center text-gray-500">
                Nessun ristorante trovato.
            </div>
        @endforelse
    </div>
</div>
@endsection 