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
        <BaseButton variant="primary" :icon="iconCamera" @click="takeAnother">
          Weiteres Foto aufnehmen
        </BaseButton>
        <p class="auto-restart-hint">
          Auto-Neustart in {{ autoRestartSeconds }}...
          <a href="#" class="auto-restart-extend" @click.prevent="extendAutoRestart">verlängern</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { usePhotoboxStore } from '../store.js';
import QRCode from 'qrcode';
import BaseButton from '../components/BaseButton.vue';

const iconCamera = new URL('../../icons/action_camera.svg', import.meta.url).href;

const AUTO_RESTART_SECONDS = 60;

const router = useRouter();
const store = usePhotoboxStore();
const qrCanvas = ref(null);
const autoRestartSeconds = ref(AUTO_RESTART_SECONDS);
let autoRestartInterval = null;

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

const startAutoRestart = () => {
  autoRestartSeconds.value = AUTO_RESTART_SECONDS;

  autoRestartInterval = setInterval(() => {
    autoRestartSeconds.value--;

    if (autoRestartSeconds.value <= 0) {
      clearInterval(autoRestartInterval);
      autoRestartInterval = null;
      takeAnother();
    }
  }, 1000);
};

const extendAutoRestart = () => {
  autoRestartSeconds.value = AUTO_RESTART_SECONDS;
};

onMounted(() => {
  generateQRCode();
  startAutoRestart();
});

onUnmounted(() => {
  if (autoRestartInterval) {
    clearInterval(autoRestartInterval);
  }
});
</script>

