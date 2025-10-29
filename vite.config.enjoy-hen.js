import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

/**
 * Configurazione Vite separata per il Web Component EnjoyHen standalone
 * 
 * Esegui con: npm run build:enjoy-hen
 * Output: public_html/js/enjoyHen.standalone.js (include CSS inline)
 */
export default defineConfig({
    plugins: [vue()],
    define: {
        'process.env.NODE_ENV': JSON.stringify('production'),
        'process.env': '({})',
        'import.meta.env.VITE_IS_WEB_COMPONENT': true,
    },
    css: {
        postcss: './postcss.config.js'
    },
    build: {
        outDir: 'public_html/js',
        emptyOutDir: false,
        lib: {
            entry: 'resources/js/enjoy-hen-element.js',
            name: 'EnjoyHen',
            fileName: (format) => `enjoyHen.${format}.js`
        },
        rollupOptions: {
            output: {
                format: 'iife',
                name: 'EnjoyHen',
                entryFileNames: 'enjoyHen.standalone.js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name.endsWith('.css')) {
                        return 'enjoyHen.standalone.[ext]';
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
