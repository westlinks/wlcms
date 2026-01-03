import { defineConfig } from 'vite';

export default defineConfig({
    build: {
        outDir: 'public/build',
        rollupOptions: {
            input: {
                wlcms: 'resources/js/wlcms.js',
                wlcmsCss: 'resources/css/wlcms.css'
            },
            output: {
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]'
            }
        }
    }
});