import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import fs from 'node:fs';
import path from 'node:path';

// The Bunny font plugin emits a content-hashed CSS file. Error pages are
// standalone Blade views and cannot read the Vite manifest, so publish a
// stable alias as well. Run last so the hashed font asset already exists.
function stableFontAlias() {
    return {
        name: 'stable-font-alias',
        writeBundle(options) {
            const outDir = options.dir || path.resolve(import.meta.dirname, 'public/build');
            const assetsDir = path.join(outDir, 'assets');
            if (!fs.existsSync(assetsDir)) {
                return;
            }

            const hashed = fs.readdirSync(assetsDir).find(
                (file) => file.startsWith('fonts-') && file.endsWith('.css') && file !== 'fonts.css',
            );

            if (hashed) {
                fs.copyFileSync(path.join(assetsDir, hashed), path.join(assetsDir, 'fonts.css'));
            }
        },
    };
}

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                    optimizedFallbacks: false,
                }),
            ],
        }),
        tailwindcss(),
        stableFontAlias(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
