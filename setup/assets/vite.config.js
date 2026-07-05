import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'path';

export default defineConfig({
    plugins: [tailwindcss()],
    build: {
        outDir: '.',
        emptyOutDir: false,
        rollupOptions: {
            input: resolve(__dirname, 'src/js/app.js'),
            output: {
                entryFileNames: 'js/app.js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name && assetInfo.name.endsWith('.css')) {
                        return 'css/app.css';
                    }

                    return 'assets/[name][extname]';
                },
            },
        },
    },
});
