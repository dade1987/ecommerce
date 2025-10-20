import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel, { refreshPaths } from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/admin/theme.css'
            ],
            refresh: [
                ...refreshPaths,
                'app/Http/Livewire/**',
                'app/Tables/Columns/**',
            ],
            publicDirectory: "public_html",
        }),
    ],
});
