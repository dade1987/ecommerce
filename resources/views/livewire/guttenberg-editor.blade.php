<div>

    <button wire:click="save(document.getElementById('guttenberg').value)" class="btn btn-info">Save</button>

    <textarea id="guttenberg" name="guttenberg"  hidden></textarea>

    <script src="https://unpkg.com/react@17.0.2/umd/react.production.min.js"></script>

    <script src="https://unpkg.com/react-dom@17.0.2/umd/react-dom.production.min.js"></script>

    <link rel="stylesheet" href="{{ asset('vendor/laraberg/css/laraberg.css') }}">

    <script src="{{ asset('vendor/laraberg/js/laraberg.js') }}"></script>

    <script>
        Laraberg.init('guttenberg');
    </script>
</div>
