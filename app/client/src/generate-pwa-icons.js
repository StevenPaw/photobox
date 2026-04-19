#!/usr/bin/env node

/**
 * Dieses Skript konvertiert icon-template.svg in verschiedene PWA-Icon-Größen
 * Voraussetzung: npm install -g sharp-cli
 *
 * Verwendung: node generate-pwa-icons.js
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const publicDir = path.join(__dirname, '../public');
const svgPath = path.join(publicDir, 'icon-template.svg');

const sizes = [
  { size: 64, name: 'pwa-64x64.png' },
  { size: 192, name: 'pwa-192x192.png' },
  { size: 512, name: 'pwa-512x512.png' },
  { size: 512, name: 'maskable-icon-512x512.png' }
];

console.log('🎨 Generiere PWA Icons...\n');

// Prüfen ob SVG existiert
if (!fs.existsSync(svgPath)) {
  console.error('❌ icon-template.svg nicht gefunden!');
  process.exit(1);
}

// Icons generieren
sizes.forEach(({ size, name }) => {
  const outputPath = path.join(publicDir, name);

  try {
    // Mit ImageMagick (falls installiert)
    console.log(`📦 Generiere ${name} (${size}x${size})...`);
    execSync(`convert -background none -resize ${size}x${size} "${svgPath}" "${outputPath}"`, {
      stdio: 'inherit'
    });
    console.log(`✅ ${name} erstellt`);
  } catch (error) {
    console.log(`⚠️  ImageMagick nicht verfügbar. Bitte Icons manuell erstellen.`);
    console.log(`   Online-Tool: https://realfavicongenerator.net/`);
  }
});

console.log('\n✨ Icon-Generierung abgeschlossen!\n');
