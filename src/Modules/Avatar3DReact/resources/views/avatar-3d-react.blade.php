{{-- Avatar 3D React Mount Point --}}
<div id="avatar3dReactRoot"
    data-title="{{ $data['title'] ?? 'Parla con il nostro assistente' }}"
    data-model-url="{{ $data['model_url'] ?? '/models/avatar.glb' }}"
    data-voice="{{ $data['voice'] ?? 'it-IT-ElsaNeural' }}"
    data-azure-speech-region="{{ $data['azure_speech_region'] ?? 'westeurope' }}"
    data-enable-speech-recognition="{{ $data['enable_speech_recognition'] ?? true ? 'true' : 'false' }}"
    data-enable-chat="{{ $data['enable_chat'] ?? true ? 'true' : 'false' }}"
    data-locale="{{ app()->getLocale() }}"
    data-team-slug="{{ request()->query('teamSlug', '') }}"
    class="w-full h-screen"
>
    {{-- React will mount here --}}
    <div class="flex items-center justify-center h-full bg-gray-100">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
            <p class="text-gray-600">Caricamento Avatar 3D...</p>
        </div>
    </div>
</div>