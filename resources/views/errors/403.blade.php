@auth
    @extends('layouts.app')
    @section('content')
        <h1>Non sei autorizzato a vedere questa pagina</h1>
    @endauth
    @guest
        <script type="text/javascript">
            window.location = "{{ route('login') }}";
        </script>
    @endguest
