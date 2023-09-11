<x-app-layout>
    @foreach ($categories as $category)
        <li><a href="{{ route('categories.products', ['category' => $category]) }}">{{ $category->name }}</a></li>
    @endforeach
</x-app-layout>
