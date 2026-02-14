import { defineConfig } from 'vite';

/**
 * WLCMS Package Vite Configuration
 * 
 * This builds the package assets for distribution.
 * 
 * CONSUMING APPS MUST ADD TO THEIR VITE CONFIG:
 * 
 * export default defineConfig({
 *     plugins: [
 *         laravel({
 *             input: [
 *                 'resources/css/app.css',
 *                 'resources/js/app.js',
 *                 'resources/vendor/wlcms/js/wlcms.js',   // Add this line
 *                 'resources/vendor/wlcms/css/wlcms.css'  // Add this line
 *             ],
 *             refresh: true,
 *         }),
 *     ],
 *     // ... rest of config
 * });
 */

export default defineConfig({
    build: {
        outDir: 'dist',
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
                wlcms: 'resources/js/wlcms.js',
                wlcmsCss: 'resources/css/wlcms.css'
            },
            output: {
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]'
            }
        }
    },
    publicDir: false, // Disable public directory copying for packages
});