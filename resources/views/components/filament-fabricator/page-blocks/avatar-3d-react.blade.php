{{-- Avatar 3D React Mount Point --}}
@php
    // Filament Fabricator passa i dati via ComponentAttributeBag
    // Usiamo $attributes->get() per recuperare tutti i valori

    // String values
    $_title = $attributes->get('title') ?? 'Parla con il nostro assistente';
    $_model_url = $attributes->get('model_url') ?? '/avatar3d/models/avatar.glb';
    $_voice = $attributes->get('voice') ?? 'it-IT-ElsaNeural';
    $_height = $attributes->get('height') ?? '80vh';
    $_aspect_ratio = $attributes->get('aspect_ratio') ?? 0.75;
    $_position_bottom = $attributes->get('position_bottom') ?? '60px';
    $_position_right = $attributes->get('position_right') ?? '0';
    $_avatar_view = $attributes->get('avatar_view') ?? 'full';
    $_orbit_controls = $attributes->get('orbit_controls') ?? 'limited';

    // Boolean values
    $_fixed_position = $attributes->get('fixed_position', true);
    $_transparent_background = $attributes->get('transparent_background', true);
    $_widget_mode = $attributes->get('widget_mode', true);
    $_show_leva_panel = $attributes->get('show_leva_panel', false);
    $_enable_bone_controls = $attributes->get('enable_bone_controls', false);
    $_enable_speech = $attributes->get('enable_speech_recognition', true);
    $_enable_chat = $attributes->get('enable_chat', true);
    $_show_fps = $attributes->get('show_fps', false);
    $_show_shadow = $attributes->get('show_shadow', false);

    // Shadow settings
    $_shadow_preset = $attributes->get('shadow_preset') ?? 'fullBody';
    $_shadow_opacity = $attributes->get('shadow_opacity');
    $_shadow_blur = $attributes->get('shadow_blur');
    $_shadow_y = $attributes->get('shadow_y') ?? 0;

    // Mouse tracking
    $_mouse_tracking_radius = $attributes->get('mouse_tracking_radius');
    $_mouse_tracking_speed = $attributes->get('mouse_tracking_speed') ?? 0.08;
@endphp
<div id="avatar3dReactRoot"
    data-title="{{ $_title }}"
    data-model-url="{{ $_model_url }}"
    data-voice="{{ $_voice }}"
    data-enable-speech-recognition="{{ $_enable_speech ? 'true' : 'false' }}"
    data-enable-chat="{{ $_enable_chat ? 'true' : 'false' }}"
    data-locale="{{ app()->getLocale() }}"
    data-team-slug="{{ request()->query('teamSlug', '') }}"
    data-tts-endpoint="/api/avatar3d/tts"
    data-fixed-position="{{ $_fixed_position ? 'true' : 'false' }}"
    data-transparent-background="{{ $_transparent_background ? 'true' : 'false' }}"
    data-widget-mode="{{ $_widget_mode ? 'true' : 'false' }}"
    data-height="{{ $_height }}"
    data-aspect-ratio="{{ $_aspect_ratio }}"
    data-position-bottom="{{ $_position_bottom }}"
    data-position-right="{{ $_position_right }}"
    data-show-leva-panel="{{ $_show_leva_panel ? 'true' : 'false' }}"
    data-enable-bone-controls="{{ $_enable_bone_controls ? 'true' : 'false' }}"
    data-orbit-controls="{{ $_orbit_controls }}"
    data-avatar-view="{{ $_avatar_view }}"
    data-show-fps="{{ $_show_fps ? 'true' : 'false' }}"
    data-show-shadow="{{ $_show_shadow ? 'true' : 'false' }}"
    data-shadow-preset="{{ $_shadow_preset }}"
    @if($_shadow_opacity)data-shadow-opacity="{{ $_shadow_opacity }}"@endif
    @if($_shadow_blur)data-shadow-blur="{{ $_shadow_blur }}"@endif
    data-shadow-y="{{ $_shadow_y }}"
    @if($_mouse_tracking_radius)data-mouse-tracking-radius="{{ $_mouse_tracking_radius }}"@endif
    data-mouse-tracking-speed="{{ $_mouse_tracking_speed }}"
    class="w-full"
>
    {{-- React will mount here - Loading placeholder --}}
    <div class="flex items-center justify-center h-full" style="min-height: {{ $_height }}; background: transparent;">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
            <p class="text-gray-600">Caricamento Avatar 3D...</p>
        </div>
    </div>
</div>

@push('scripts')
@viteReactRefresh
@vite(['resources/js/app-react.js'])
@endpush