@aware(['page'])
@if (!isset($_COOKIE['displayed_logo']))
    <div class="bg-black w-full h-full animate-fade fixed z-10 flex items-center justify-center">
        <img class="h-1/2 w-auto" src="{{ $logoUrl }}" />
    </div>
@endif

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var fadeOutDiv = document.querySelector(".animate-fade");

        fadeOutDiv.addEventListener("animationend", function() {
            // Rimuovi l'elemento dal DOM alla fine dell'animazione
            fadeOutDiv.style.display = "none";
        });

        document.cookie = "displayed_logo=true";
    });
</script>
