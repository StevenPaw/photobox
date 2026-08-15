<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="gallery-overlay"
      @click="handleOverlayClick"
      @touchstart="onActivity"
    >
      <div class="gallery-popup">
        <div class="gallery-header">
          <h2>Galerie</h2>
          <div class="close-ring-wrap">
            <svg class="close-ring" viewBox="0 0 40 40">
              <circle class="close-ring-bg" cx="20" cy="20" r="17" />
              <circle
                class="close-ring-fill"
                cx="20" cy="20" r="17"
                :style="{ strokeDasharray: closeRingCircumference, strokeDashoffset: galleryRingOffset }"
              />
            </svg>
            <button class="gallery-close" @click="close" aria-label="Schließen">
              <img :src="iconClose" alt="" />
            </button>
          </div>
        </div>

        <div class="gallery-grid" ref="gridEl" @scroll="onActivity">
          <p v-if="!loading && photos.length === 0" class="gallery-empty">
            Noch keine Fotos für dieses Event vorhanden.
          </p>

          <button
            v-for="(photo, index) in photos"
            :key="photo.ID"
            class="gallery-item"
            @click="openLightbox(index)"
          >
            <span class="gallery-item-inner">
              <img :src="photo.ThumbnailURL" :alt="photo.FormattedDate" loading="lazy" />
            </span>
          </button>

          <div ref="sentinelEl" class="gallery-sentinel"></div>

          <p v-if="loading" class="gallery-loading">Lade weitere Fotos...</p>
        </div>
      </div>

      <div v-if="lightboxIndex !== null" class="lightbox" @click="handleLightboxClick">
        <div class="close-ring-wrap lightbox-close-wrap">
          <svg class="close-ring" viewBox="0 0 40 40">
            <circle class="close-ring-bg" cx="20" cy="20" r="17" />
            <circle
              class="close-ring-fill"
              cx="20" cy="20" r="17"
              :style="{ strokeDasharray: closeRingCircumference, strokeDashoffset: lightboxRingOffset }"
            />
          </svg>
          <button class="lightbox-close" @click="closeLightbox" aria-label="Schließen">
            <img :src="iconClose" alt="" />
          </button>
        </div>

        <button
          class="lightbox-nav lightbox-prev"
          @click="showPrev"
          :disabled="lightboxIndex === 0"
          aria-label="Vorheriges Foto"
        >
          <img :src="iconBack" alt="" />
        </button>

        <div class="lightbox-image-wrapper">
          <img
            :src="currentLightboxPhoto?.DownloadURL"
            :alt="currentLightboxPhoto?.FormattedDate"
            class="lightbox-image"
          />
          <div class="lightbox-qr">
            <canvas ref="lightboxQrCanvas"></canvas>
          </div>
        </div>

        <button
          class="lightbox-nav lightbox-next"
          @click="showNext"
          :disabled="lightboxIndex === photos.length - 1 && !hasMore"
          aria-label="Nächstes Foto"
        >
          <img :src="iconNext" alt="" />
        </button>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onBeforeUnmount } from 'vue';
import { usePhotoboxStore } from '../store.js';
import QRCode from 'qrcode';

const iconBack = new URL('../../icons/action_back.svg', import.meta.url).href;
const iconNext = new URL('../../icons/action_next.svg', import.meta.url).href;
const iconClose = new URL('../../icons/action_close.svg', import.meta.url).href;

const PAGE_SIZE = 24;
const AUTO_CLOSE_SECONDS = 60;
const CLOSE_RING_RADIUS = 17;
const closeRingCircumference = 2 * Math.PI * CLOSE_RING_RADIUS;

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  eventHash: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue']);

const store = usePhotoboxStore();

const photos = ref([]);
const loading = ref(false);
const hasMore = ref(true);
const lightboxIndex = ref(null);

const gridEl = ref(null);
const sentinelEl = ref(null);
const lightboxQrCanvas = ref(null);
let observer = null;

const gallerySecondsLeft = ref(AUTO_CLOSE_SECONDS);
const lightboxSecondsLeft = ref(AUTO_CLOSE_SECONDS);
let galleryTimer = null;
let lightboxTimer = null;

const galleryRingOffset = computed(() => {
  return closeRingCircumference * (1 - gallerySecondsLeft.value / AUTO_CLOSE_SECONDS);
});
const lightboxRingOffset = computed(() => {
  return closeRingCircumference * (1 - lightboxSecondsLeft.value / AUTO_CLOSE_SECONDS);
});

const currentLightboxPhoto = computed(() => {
  return lightboxIndex.value !== null ? photos.value[lightboxIndex.value] : null;
});

const currentDownloadURL = computed(() => {
  if (!currentLightboxPhoto.value) return null;
  return `${window.location.origin}/download/${currentLightboxPhoto.value.Hash}`;
});

const renderLightboxQr = async () => {
  await nextTick();
  if (!lightboxQrCanvas.value || !currentDownloadURL.value) return;

  try {
    await QRCode.toCanvas(lightboxQrCanvas.value, currentDownloadURL.value, {
      width: 200,
      margin: 1,
      color: {
        dark: '#000',
        light: '#ffffff',
      },
    });
  } catch (error) {
    console.error('Fehler beim Generieren des QR-Codes:', error);
  }
};

watch(currentLightboxPhoto, (photo) => {
  if (photo) {
    renderLightboxQr();
  }
});

const resetGallery = () => {
  photos.value = [];
  hasMore.value = true;
  loading.value = false;
  lightboxIndex.value = null;
};

const loadMore = async () => {
  if (loading.value || !hasMore.value || !props.eventHash) return;

  loading.value = true;
  try {
    const result = await store.fetchEventPhotos(props.eventHash, {
      limit: PAGE_SIZE,
      offset: photos.value.length,
    });
    photos.value.push(...(result.photos || []));
    hasMore.value = !!result.hasMore;
  } catch (error) {
    console.error('Fehler beim Laden der Fotos:', error);
    hasMore.value = false;
  } finally {
    loading.value = false;
  }
};

const setupObserver = async () => {
  await nextTick();
  if (!sentinelEl.value || !gridEl.value) return;

  observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting) {
        loadMore();
      }
    },
    { root: gridEl.value, rootMargin: '200px' }
  );
  observer.observe(sentinelEl.value);
};

const teardownObserver = () => {
  if (observer) {
    observer.disconnect();
    observer = null;
  }
};

const close = () => {
  emit('update:modelValue', false);
};

const startGalleryTimer = () => {
  clearInterval(galleryTimer);
  gallerySecondsLeft.value = AUTO_CLOSE_SECONDS;

  galleryTimer = setInterval(() => {
    gallerySecondsLeft.value--;
    if (gallerySecondsLeft.value <= 0) {
      clearInterval(galleryTimer);
      close();
    }
  }, 1000);
};

const stopGalleryTimer = () => {
  clearInterval(galleryTimer);
  galleryTimer = null;
};

const startLightboxTimer = () => {
  clearInterval(lightboxTimer);
  lightboxSecondsLeft.value = AUTO_CLOSE_SECONDS;

  lightboxTimer = setInterval(() => {
    lightboxSecondsLeft.value--;
    if (lightboxSecondsLeft.value <= 0) {
      clearInterval(lightboxTimer);
      closeLightbox();
    }
  }, 1000);
};

const stopLightboxTimer = () => {
  clearInterval(lightboxTimer);
  lightboxTimer = null;
};

// Any interaction (click/tap/scroll) resets the relevant timer(s), so an
// abandoned booth returns to the invite-to-capture screen after a minute
// of true inactivity, without cutting off someone actively browsing.
const onActivity = () => {
  if (props.modelValue) {
    startGalleryTimer();
  }
  if (lightboxIndex.value !== null) {
    startLightboxTimer();
  }
};

const handleOverlayClick = (event) => {
  onActivity();
  if (event.target === event.currentTarget) {
    close();
  }
};

const handleLightboxClick = (event) => {
  if (event.target === event.currentTarget) {
    closeLightbox();
  }
};

const openLightbox = (index) => {
  lightboxIndex.value = index;
  startLightboxTimer();
};

const closeLightbox = () => {
  lightboxIndex.value = null;
  stopLightboxTimer();
};

const showPrev = () => {
  if (lightboxIndex.value === null || lightboxIndex.value === 0) return;
  lightboxIndex.value--;
};

const showNext = async () => {
  if (lightboxIndex.value === null) return;

  if (lightboxIndex.value < photos.value.length - 1) {
    lightboxIndex.value++;
    return;
  }

  if (hasMore.value) {
    await loadMore();
    if (lightboxIndex.value < photos.value.length - 1) {
      lightboxIndex.value++;
    }
  }
};

const onKeydown = (event) => {
  if (!props.modelValue) return;

  onActivity();

  if (event.key === 'Escape') {
    lightboxIndex.value !== null ? closeLightbox() : close();
  } else if (lightboxIndex.value !== null) {
    if (event.key === 'ArrowLeft') showPrev();
    if (event.key === 'ArrowRight') showNext();
  }
};

watch(
  () => props.modelValue,
  async (isOpen) => {
    if (isOpen) {
      resetGallery();
      await loadMore();
      await setupObserver();
      window.addEventListener('keydown', onKeydown);
      startGalleryTimer();
    } else {
      teardownObserver();
      window.removeEventListener('keydown', onKeydown);
      stopGalleryTimer();
      stopLightboxTimer();
    }
  }
);

onBeforeUnmount(() => {
  teardownObserver();
  window.removeEventListener('keydown', onKeydown);
  stopGalleryTimer();
  stopLightboxTimer();
});
</script>
