<x-app-layout>
    @foreach ($products as $product)
        <li>{{ $product->name }}</li>
    @endforeach
</x-app-layout>