<template>
  <div class="success-page">
    <div class="success-container">
      <div class="success-icon">✓</div>
      <h1>Foto erfolgreich gespeichert!</h1>
      <p>Dein Foto wurde gespeichert und steht bereit.</p>

      <div class="actions">
        <button @click="takeAnother" class="btn-another">
          📸 Weiteres Foto aufnehmen
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { usePhotoboxStore } from '../store.js';

const router = useRouter();
const store = usePhotoboxStore();

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
</script>

