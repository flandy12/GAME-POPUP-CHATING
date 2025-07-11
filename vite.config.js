import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite'; // This import is correct if you have @tailwindcss/vite installed

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: 'localhost',
        hmr: {
            host: 'localhost',
            port: 5174, // Changed from 5173 to 5174
        },
        watch: {
            usePolling: true,
        },
        proxy: {
            '/laravel-reverb': {
                target: `ws://${process.env.VITE_REVERB_HOST ?? 'localhost'}:${process.env.VITE_REVERB_PORT ?? '8080'}`,
                ws: true,
                changeOrigin: true,
            },
        },
    }
});