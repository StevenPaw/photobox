import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

// https://vitejs.dev/config/
export default defineConfig(({command}) => {
    return {
        server: {
            host: '0.0.0.0',
            port: 5173,
        },
        alias: {
            alias: [{find: '@', replacement: './app/client/src'}],
        },
        base: './',
        publicDir: 'app/client/public',
        build: {
          // cssCodeSplit: false,
            outDir: './app/client/dist',
            manifest: true,
            sourcemap: true,
            rollupOptions: {
                input: {
                    'main.js': './app/client/src/js/main.js',
                    'main.scss': './app/client/src/scss/main.scss',
                    'editor.scss': './app/client/src/scss/editor.scss',
                },
            },
        },
        plugins: [
            vue(),
            VitePWA({
                registerType: 'autoUpdate',
                includeAssets: ['favicon.ico', 'robots.txt', 'apple-touch-icon.png'],
                manifest: false, // Wir nutzen unser eigenes manifest.json
                workbox: {
                    globPatterns: ['**/*.{js,css,html,ico,png,svg,woff,woff2}'],
                    runtimeCaching: [
                        {
                            urlPattern: /^https:\/\/photobox\.ddev\.site\/.*/i,
                            handler: 'NetworkFirst',
                            options: {
                                cacheName: 'photobox-cache',
                                expiration: {
                                    maxEntries: 50,
                                    maxAgeSeconds: 60 * 60 * 24 * 30 // 30 Tage
                                },
                                cacheableResponse: {
                                    statuses: [0, 200]
                                }
                            }
                        }
                    ]
                },
                devOptions: {
                    enabled: true,
                    type: 'module'
                }
            })
        ]
    }
})
