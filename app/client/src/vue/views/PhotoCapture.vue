<template>
  <div class="photo-capture">
    <!-- Main Camera View -->
    <div class="camera-container" v-if="!capturedImage">
      <div class="camera-wrapper">
        <!-- Video Preview with CSS Filter -->
        <div class="video-container">
          <video ref="videoElement" autoplay playsinline :style="currentFilterStyles"></video>
          <!-- Filter Overlay Image -->
          <img
            v-if="currentFilter && currentFilter.ImageURL"
            :src="currentFilter.ImageURL"
            class="filter-overlay"
            alt="Filter Overlay"
          />
        </div>

        <!-- Filter Navigation -->
        <div class="filter-navigation">
          <button
            @click="previousFilter"
            class="nav-btn nav-btn-left"
            :disabled="!hasFilters"
          >
            ‹
          </button>
          <button
            @click="nextFilter"
            class="nav-btn nav-btn-right"
            :disabled="!hasFilters"
          >
            ›
          </button>
        </div>

        <!-- Countdown Display -->
        <div v-if="countdown > 0" class="countdown">
          {{ countdown }}
        </div>
      </div>

      <!-- Controls -->
      <div class="controls">
        <button
          @click="startCountdown"
          class="btn-capture"
          :disabled="capturing || countdown > 0"
        >
          {{ capturing ? 'Aufnahme läuft...' : '📸 Foto aufnehmen' }}
        </button>
      </div>
    </div>

    <!-- Photo Review -->
    <div class="photo-review" v-else>
      <div class="review-container">
        <h2>Aufgenommenes Foto</h2>
        <img :src="capturedImage" alt="Captured Photo" class="captured-photo" />

        <div class="review-actions">
          <button @click="retakePhoto" class="btn-retake">🔄 Erneut aufnehmen</button>
          <button @click="continueToPersonSelection" class="btn-continue">✓ Weiter</button>
        </div>
      </div>
    </div>

    <!-- Hidden Canvas for Capture -->
    <canvas ref="canvasElement" style="display: none;"></canvas>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { usePhotoboxStore } from '../store.js';

const router = useRouter();
const store = usePhotoboxStore();

const videoElement = ref(null);
const canvasElement = ref(null);
const capturedImage = ref(null);
const countdown = ref(0);
const capturing = ref(false);

// Computed
const currentFilter = computed(() => store.currentFilter);
const hasFilters = computed(() => store.hasFilters);

const currentFilterStyles = computed(() => {
  if (!currentFilter.value || !currentFilter.value.CSSStyles) {
    return {};
  }

  // Parse CSS styles string and return as object
  const styles = {};
  const cssText = currentFilter.value.CSSStyles;

  // Simple parser for CSS properties
  if (cssText) {
    try {
      // If it's a JSON object string
      if (cssText.trim().startsWith('{')) {
        Object.assign(styles, JSON.parse(cssText));
      } else {
        // If it's a CSS filter string (e.g., "filter: grayscale(100%)")
        styles.filter = cssText;
      }
    } catch (e) {
      // Fallback: treat as raw CSS filter value
      styles.filter = cssText;
    }
  }

  return styles;
});

// Methods
const attachCameraStream = () => {
  if (videoElement.value && store.mediaStream) {
    videoElement.value.srcObject = store.mediaStream;
  }
};

const previousFilter = () => {
  store.previousFilter();
};

const nextFilter = () => {
  store.nextFilter();
};

const startCountdown = () => {
  if (capturing.value) return;

  capturing.value = true;
  countdown.value = 3;

  const countdownInterval = setInterval(() => {
    countdown.value--;

    if (countdown.value === 0) {
      clearInterval(countdownInterval);
      capturePhoto();
    }
  }, 1000);
};

const capturePhoto = () => {
  const video = videoElement.value;
  const canvas = canvasElement.value;

  if (!video || !canvas) {
    capturing.value = false;
    return;
  }

  // Calculate square dimensions (use smaller dimension and crop center)
  const size = Math.min(video.videoWidth, video.videoHeight);
  canvas.width = size;
  canvas.height = size;

  const ctx = canvas.getContext('2d');

  // Calculate crop offsets to get center of video
  const offsetX = (video.videoWidth - size) / 2;
  const offsetY = (video.videoHeight - size) / 2;

  // Apply CSS filter to canvas if present
  if (currentFilter.value && currentFilter.value.CSSStyles) {
    ctx.filter = currentFilterStyles.value.filter || 'none';
  }

  // Draw video frame (cropped to square from center)
  ctx.drawImage(
    video,
    offsetX, offsetY, size, size,  // source rectangle (crop from center)
    0, 0, size, size                // destination rectangle (full canvas)
  );

  // Reset filter
  ctx.filter = 'none';

  // Draw overlay image if present
  if (currentFilter.value && currentFilter.value.ImageURL) {
    const overlayImage = new Image();
    overlayImage.crossOrigin = 'anonymous';
    overlayImage.onload = () => {
      ctx.drawImage(overlayImage, 0, 0, canvas.width, canvas.height);
      finalizeCapturePhoto();
    };
    overlayImage.onerror = () => {
      console.error('Failed to load overlay image');
      finalizeCapturePhoto();
    };
    overlayImage.src = currentFilter.value.ImageURL;
  } else {
    finalizeCapturePhoto();
  }
};

const finalizeCapturePhoto = () => {
  const canvas = canvasElement.value;
  capturedImage.value = canvas.toDataURL('image/png');
  capturing.value = false;
  // Don't stop camera - keep it running for next photo
};

const retakePhoto = async () => {
  capturedImage.value = null;
  // Wait for video element to be re-rendered
  await nextTick();
  // Re-attach camera stream to the new video element
  attachCameraStream();
};

const continueToPersonSelection = async () => {
  store.setCapturedPhoto(capturedImage.value);

  // Check if event has persons
  if (!store.selectedEvent) {
    router.push('/person-selection');
    return;
  }

  try {
    const persons = await store.fetchEventPersons(store.selectedEvent.ID);

    // If no persons exist, skip person selection and save directly
    if (persons.length === 0) {
      const result = await store.savePhoto(
        store.selectedEvent.ID,
        capturedImage.value,
        []
      );

      if (result.success) {
        router.push('/success');
      } else {
        alert('Fehler beim Speichern des Fotos.');
      }
    } else {
      // Persons exist, go to person selection
      router.push('/person-selection');
    }
  } catch (error) {
    console.error('Fehler beim Prüfen der Personen:', error);
    // Fallback to person selection page
    router.push('/person-selection');
  }
};

const goBack = () => {
  store.stopCamera();
  router.push('/');
};

// Lifecycle
onMounted(() => {
  if (!store.selectedEvent || !store.selectedCamera) {
    router.push('/');
    return;
  }

  // Attach stream if already initialized, otherwise init camera
  if (store.mediaStream) {
    attachCameraStream();
  } else {
    store.initCamera().then(() => {
      attachCameraStream();
    }).catch(() => {
      alert('Kamera-Zugriff fehlgeschlagen.');
      router.push('/');
    });
  }
});

onUnmounted(() => {
  // Don't stop camera on unmount - keep it running
  // Camera will only be stopped when going back to setup or on page unload
});
</script>

