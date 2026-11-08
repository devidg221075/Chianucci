import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// GrapesJS carica Font Awesome da un CDN esterno: lo togliamo e usiamo la copia locale (nessuna risorsa di terzi).
const stripExternalCssImports = {
    name: 'strip-external-css-imports',
    enforce: 'pre',
    transform(code, id) {
        if (id.includes('grapesjs') && id.endsWith('.css')) {
            return code.replace(/@import url\([^)]*cdnjs[^)]*\);?/g, '');
        }
    },
};

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/site.css', 'resources/js/app.js', 'resources/js/page-editor.js'],
            refresh: true,
        }),
        tailwindcss(),
        stripExternalCssImports,
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
