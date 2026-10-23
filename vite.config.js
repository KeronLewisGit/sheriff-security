import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

// Fonts are downloaded at build time and served from /build, so visitors never hit a font CDN.
// Chakra Petch (squared, technical) is used sparingly for headings, tags and labels; Red Hat Text
// carries all reading text; Red Hat Mono sets reference numbers (RFP-26-00012) and coordinates.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Chakra Petch', {
                    alias: 'display',
                    variable: '--ff-display',
                    weights: [500, 600, 700],
                    subsets: ['latin'],
                    preload: [{ weight: 700 }],
                    fallbacks: ['ui-sans-serif', 'system-ui', 'sans-serif'],
                }),
                bunny('Red Hat Text', {
                    alias: 'sans',
                    variable: '--ff-sans',
                    weights: [400, 500, 700],
                    subsets: ['latin'],
                    preload: [{ weight: 400 }, { weight: 700 }],
                    fallbacks: ['ui-sans-serif', 'system-ui', 'sans-serif'],
                }),
                bunny('Red Hat Mono', {
                    alias: 'mono',
                    variable: '--ff-mono',
                    weights: [500],
                    subsets: ['latin'],
                    fallbacks: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'monospace'],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: { watch: { ignored: ['**/storage/framework/views/**'] } },
});
