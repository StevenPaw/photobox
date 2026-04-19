#!/usr/bin/env node

/**
 * Post-Build-Skript: Kopiert PWA-Dateien ins public-Verzeichnis
 * damit sie vom Root der Domain aus zugänglich sind
 */

const fs = require('fs');
const path = require('path');

const distDir = path.join(__dirname, 'app/client/dist');
const publicDir = path.join(__dirname, 'public');

const filesToCopy = [
    'manifest.json',
    'sw.js',
    'sw.js.map',
    'pwa-64x64.svg',
    'pwa-192x192.svg',
    'pwa-512x512.svg',
    'maskable-icon-512x512.svg',
    'workbox-3896e580.js',
    'workbox-3896e580.js.map'
];

console.log('📦 Kopiere PWA-Dateien ins public-Verzeichnis...\n');

let copiedCount = 0;
let errorCount = 0;

filesToCopy.forEach(file => {
    const srcPath = path.join(distDir, file);
    const destPath = path.join(publicDir, file);

    try {
        if (fs.existsSync(srcPath)) {
            fs.copyFileSync(srcPath, destPath);
            console.log(`✅ ${file} kopiert`);
            copiedCount++;
        } else {
            console.log(`⚠️  ${file} nicht gefunden (übersprungen)`);
        }
    } catch (error) {
        console.error(`❌ Fehler beim Kopieren von ${file}:`, error.message);
        errorCount++;
    }
});

console.log(`\n✨ Fertig! ${copiedCount} Dateien kopiert${errorCount > 0 ? `, ${errorCount} Fehler` : ''}\n`);

process.exit(errorCount > 0 ? 1 : 0);
