<template>
  <div class="success-page">
    <div class="success-container">
      <div class="success-icon">✓</div>
      <h1>Foto erfolgreich gespeichert!</h1>
      <p>Dein Foto wurde gespeichert und steht bereit:</p>

      <!-- QR Code Section -->
      <div v-if="downloadURL" class="qr-section">
        <canvas ref="qrCanvas" class="qr-canvas"></canvas>
      </div>

      <div class="actions">
        <button @click="takeAnother" class="btn-another">
          📸 Weiteres Foto aufnehmen
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { usePhotoboxStore } from '../store.js';
import QRCode from 'qrcode';

const router = useRouter();
const store = usePhotoboxStore();
const qrCanvas = ref(null);

const downloadURL = computed(() => {
  if (!store.savedPhotoHash) return null;
  const baseURL = window.location.origin;
  return `${baseURL}/download/${store.savedPhotoHash}`;
});

const generateQRCode = async () => {
  if (!qrCanvas.value || !downloadURL.value) return;

  try {
    await QRCode.toCanvas(qrCanvas.value, downloadURL.value, {
      width: 300,
      margin: 2,
      color: {
        dark: '#000',
        light: '#ffffff',
      },
    });
  } catch (error) {
    console.error('Fehler beim Generieren des QR-Codes:', error);
  }
};

const takeAnother = () => {
  // Keep event and camera settings, reset photo
  store.setCapturedPhoto(null);
  store.setSelectedPersons([]);
  router.push('/capture');
};

const goToSetup = () => {
  // Complete reset
  store.reset();
  router.push('/');
};

onMounted(() => {
  generateQRCode();
});
</script>

