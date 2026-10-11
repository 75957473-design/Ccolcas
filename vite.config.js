import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 
                'resources/css/inicio.css', 
                'resources/css/carrito.css',
                'resources/css/pedidos.css',
                'resources/css/admin.css',
                'resources/js/app.js',
                'resources/js/inicio.js',
                'resources/js/carrito.js',
                'resources/js/admin.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
