import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

/**
 * Configurazione Vite separata per il Web Component standalone
 * 
 * Esegui con: npm run build:web-component
 * Output: public_html/js/enjoyTalk3D.standalone.js (include CSS inline)
 */
export default defineConfig({
    plugins: [vue()],
    define: {
        'process.env.NODE_ENV': JSON.stringify('production'),
        'process.env': '({})',
    },
    build: {
        outDir: 'public_html/js',
        lib: {
            entry: 'resources/js/enjoy-talk-3d-element.js',
            name: 'EnjoyTalk3D',
            fileName: (format) => `enjoyTalk3D.${format}.js`
        },
        rollupOptions: {
            output: {
                format: 'iife',
                name: 'EnjoyTalk3D',
                entryFileNames: 'enjoyTalk3D.standalone.js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name.endsWith('.css')) {
                        return 'enjoyTalk3D.standalone.[ext]';
                    }
                    return assetInfo.name;
                },
                extend: true,
                globals: {
                    process: 'undefined'
                }
            }
        },
        cssCodeSplit: false,
        minify: 'esbuild'
    }
});
