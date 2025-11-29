import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import vue from '@vitejs/plugin-vue';
import laravel, { refreshPaths } from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        react(),
        vue(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/app-react.js',
                'resources/css/filament/admin/theme.css'
            ],
            refresh: [
                ...refreshPaths,
                'app/Http/Livewire/**',
                'app/Tables/Columns/**',
            ],
            publicDirectory: "public",
            valetTls: false,
            detectTls: false
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: {
            port: 5173,
            host: 'localhost',
            protocol: 'ws'
        },
        watch: {
            usePolling: true
        },
        origin: 'https://avatar-3d-v1.local'
    }
});
