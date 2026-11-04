import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'node:fs';
import path from 'node:path';

/**
 * Third-party libraries copied from node_modules into public/assets so the
 * application never depends on a CDN. Format: [source, destination].
 */
const vendorAssets = [
    // Bootstrap 5
    ['bootstrap/dist/css/bootstrap.min.css', 'plugins/bootstrap/css/bootstrap.min.css'],
    ['bootstrap/dist/js/bootstrap.bundle.min.js', 'plugins/bootstrap/js/bootstrap.bundle.min.js'],
    // Bootstrap Icons (CSS references ./fonts/*)
    ['bootstrap-icons/font/bootstrap-icons.min.css', 'plugins/bootstrap-icons/bootstrap-icons.min.css'],
    ['bootstrap-icons/font/fonts', 'plugins/bootstrap-icons/fonts'],
    // jQuery (only for Select2 and Toastr)
    ['jquery/dist/jquery.min.js', 'plugins/jquery/jquery.min.js'],
    // DataTables (vanilla JS API, Bootstrap 5 styling)
    ['datatables.net/js/dataTables.min.js', 'plugins/datatables/dataTables.min.js'],
    ['datatables.net-bs5/js/dataTables.bootstrap5.min.js', 'plugins/datatables/dataTables.bootstrap5.min.js'],
    ['datatables.net-bs5/css/dataTables.bootstrap5.min.css', 'plugins/datatables/dataTables.bootstrap5.min.css'],
    // SweetAlert2
    ['sweetalert2/dist/sweetalert2.min.js', 'plugins/sweetalert2/sweetalert2.min.js'],
    ['sweetalert2/dist/sweetalert2.min.css', 'plugins/sweetalert2/sweetalert2.min.css'],
    // Toastr
    ['toastr/build/toastr.min.js', 'plugins/toastr/toastr.min.js'],
    ['toastr/build/toastr.min.css', 'plugins/toastr/toastr.min.css'],
    // Chart.js
    ['chart.js/dist/chart.umd.min.js', 'plugins/chartjs/chart.umd.min.js'],
    // Select2
    ['select2/dist/js/select2.min.js', 'plugins/select2/select2.min.js'],
    ['select2/dist/css/select2.min.css', 'plugins/select2/select2.min.css'],
    ['select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css', 'plugins/select2/select2-bootstrap-5-theme.min.css'],
    // Flatpickr
    ['flatpickr/dist/flatpickr.min.js', 'plugins/flatpickr/flatpickr.min.js'],
    ['flatpickr/dist/flatpickr.min.css', 'plugins/flatpickr/flatpickr.min.css'],
];

/** Inter font weights bundled locally (latin subset only). */
const fontWeights = [400, 500, 600, 700];

function copyVendorAssets() {
    const root = process.cwd();
    const modules = path.join(root, 'node_modules');
    const assets = path.join(root, 'public', 'assets');

    const copy = () => {
        for (const [source, destination] of vendorAssets) {
            const from = path.join(modules, source);
            const to = path.join(assets, destination);

            fs.mkdirSync(path.dirname(to), { recursive: true });
            fs.cpSync(from, to, { recursive: true });
        }

        for (const dir of ['css', 'js']) {
            fs.rmSync(path.join(assets, dir), { recursive: true, force: true });
        }

        for (const dir of ['css', 'js', 'images']) {
            fs.mkdirSync(path.join(assets, dir), { recursive: true });
        }

        const fontDir = path.join(assets, 'fonts', 'inter');
        fs.mkdirSync(path.join(fontDir, 'files'), { recursive: true });

        const css = fontWeights.map((weight) => {
            for (const ext of ['woff2', 'woff']) {
                const file = `inter-latin-${weight}-normal.${ext}`;
                fs.copyFileSync(path.join(modules, '@fontsource/inter/files', file), path.join(fontDir, 'files', file));
            }

            return fs.readFileSync(path.join(modules, `@fontsource/inter/latin-${weight}.css`), 'utf8');
        });

        fs.writeFileSync(path.join(fontDir, 'inter.css'), css.join('\n'));
    };

    return {
        name: 'copy-vendor-assets',
        buildStart: copy,
    };
}

const collectScripts = (folder) => fs
    .readdirSync(`resources/js/${folder}`)
    .filter((file) => file.endsWith('.js'))
    .map((file) => `resources/js/${folder}/${file}`);

export default defineConfig({
    plugins: [
        copyVendorAssets(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                ...collectScripts('plugins'),
                ...collectScripts('pages'),
            ],
            buildDirectory: 'assets',
            refresh: true,
        }),
    ],
    build: {
        emptyOutDir: false,
        cssMinify: true,
        minify: true,
        sourcemap: false,
        rollupOptions: {
            output: {
                assetFileNames: (assetInfo) => (
                    assetInfo.names?.some((n) => n.endsWith('.css'))
                        ? 'css/[name]-[hash][extname]'
                        : 'images/[name]-[hash][extname]'
                ),
                entryFileNames: 'js/[name]-[hash].js',
                chunkFileNames: 'js/[name]-[hash].js',
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
