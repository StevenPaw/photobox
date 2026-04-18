<template>
  <div class="person-selection">
    <div class="selection-container">
      <div class="photo-preview">
        <h2>Dein Foto</h2>
        <img ref="photoImage" :src="store.capturedPhoto" alt="Captured Photo" class="preview-image" />
        <canvas ref="detectionCanvas" class="detection-canvas"></canvas>
      </div>

      <div class="person-selector">
        <h2>Wer ist auf dem Foto?</h2>

        <div v-if="recognizing" class="loading">
          🤖 Erkenne Gesichter...
        </div>

        <div v-if="loading" class="loading">
          Lade Personen...
        </div>

        <div v-else-if="persons.length === 0" class="no-persons">
          <p>Keine Personen für dieses Event verfügbar.</p>
          <button @click="skipPersonSelection" class="btn-skip">
            Ohne Personen-Auswahl fortfahren
          </button>
        </div>

        <div v-else class="persons-grid">
          <div
            v-for="person in persons"
            :key="person.ID"
            @click="togglePerson(person.ID)"
            :class="['person-card', { selected: isSelected(person.ID), 'auto-detected': isAutoDetected(person.ID) }]"
          >
            <div class="person-image">
              <img
                v-if="person.ImageURL"
                :src="person.ImageURL"
                :alt="person.Title"
              />
              <div v-else class="person-placeholder">
                {{ getInitials(person) }}
              </div>
            </div>
            <div class="person-name">
              {{ person.Title }}
              <span v-if="isAutoDetected(person.ID)" class="auto-badge">🤖</span>
            </div>
            <div class="selection-indicator" v-if="isSelected(person.ID)">✓</div>
          </div>
        </div>

        <div class="actions" v-if="persons.length > 0">
          <button @click="goBack" class="btn-back">← Zurück</button>
          <button @click="savePhoto" class="btn-save" :disabled="saving">
            {{ saving ? 'Speichere...' : '💾 Foto speichern' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { usePhotoboxStore } from '../store.js';
import * as faceapi from 'face-api.js';

const router = useRouter();
const store = usePhotoboxStore();

const persons = ref([]);
const selectedPersons = ref([]);
const autoDetectedPersons = ref([]);
const loading = ref(true);
const saving = ref(false);
const recognizing = ref(false);
const photoImage = ref(null);
const detectionCanvas = ref(null);

let modelsLoaded = false;

// Check if WebGL is supported
const isWebGLSupported = () => {
  try {
    const canvas = document.createElement('canvas');
    return !!(
      window.WebGLRenderingContext &&
      (canvas.getContext('webgl') || canvas.getContext('experimental-webgl'))
    );
  } catch (e) {
    return false;
  }
};

// Load face-api.js models
const loadModels = async () => {
  if (modelsLoaded) return;

  try {
    // Try to use CPU backend if WebGL is not supported
    if (!isWebGLSupported()) {
      console.log('WebGL nicht verfügbar, verwende CPU-Backend für Gesichtserkennung');
      // face-api.js will automatically fall back to CPU
    }

    const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model';
    await Promise.all([
      faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
      faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
      faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);
    modelsLoaded = true;
  } catch (error) {
    console.error('Fehler beim Laden der Gesichtserkennungs-Modelle:', error);
    throw error;
  }
};

// Perform face recognition
const performFaceRecognition = async () => {
  if (!store.selectedEvent?.UsePersonRecognition || persons.value.length === 0) {
    return;
  }

  try {
    recognizing.value = true;

    // Load models if not already loaded
    try {
      await loadModels();
    } catch (modelError) {
      console.warn('Gesichtserkennung nicht verfügbar:', modelError.message);
      // Gracefully fall back to manual selection
      return;
    }

    // Create labeled face descriptors from person images
    const labeledDescriptors = [];

    for (const person of persons.value) {
      if (!person.ImageURL) continue;

      try {
        const img = await faceapi.fetchImage(person.ImageURL);
        const detection = await faceapi
          .detectSingleFace(img)
          .withFaceLandmarks()
          .withFaceDescriptor();

        if (detection) {
          labeledDescriptors.push(
            new faceapi.LabeledFaceDescriptors(
              person.ID.toString(),
              [detection.descriptor]
            )
          );
        }
      } catch (error) {
        console.warn(`Konnte Gesicht für ${person.Title} nicht erkennen:`, error);
      }
    }

    if (labeledDescriptors.length === 0) {
      console.log('Keine Gesichter in Personen-Bildern gefunden');
      return;
    }

    // Detect faces in captured photo
    const img = photoImage.value;
    const detections = await faceapi
      .detectAllFaces(img)
      .withFaceLandmarks()
      .withFaceDescriptors();

    if (detections.length === 0) {
      console.log('Keine Gesichter im Foto gefunden');
      return;
    }

    // Match detected faces with known persons
    const faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.6);
    const detected = [];

    detections.forEach(detection => {
      const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
      if (bestMatch.label !== 'unknown') {
        const personId = parseInt(bestMatch.label);
        if (!detected.includes(personId)) {
          detected.push(personId);
        }
      }
    });

    // Auto-select detected persons
    autoDetectedPersons.value = detected;
    selectedPersons.value = [...detected];

    console.log(`${detected.length} Gesicht(er) erkannt und automatisch ausgewählt`);

  } catch (error) {
    console.warn('Gesichtserkennung fehlgeschlagen, manuelle Auswahl erforderlich:', error.message);
    // Don't show alert, just fall back to manual selection
  } finally {
    recognizing.value = false;
  }
};

// Methods
const loadPersons = async () => {
  if (!store.selectedEvent) {
    router.push('/');
    return;
  }

  try {
    loading.value = true;
    persons.value = await store.fetchEventPersons(store.selectedEvent.ID);
  } catch (error) {
    console.error('Fehler beim Laden der Personen:', error);
    alert('Personen konnten nicht geladen werden.');
  } finally {
    loading.value = false;
  }
};

const togglePerson = (personId) => {
  const index = selectedPersons.value.indexOf(personId);
  if (index > -1) {
    selectedPersons.value.splice(index, 1);
  } else {
    selectedPersons.value.push(personId);
  }
};

const isSelected = (personId) => {
  return selectedPersons.value.includes(personId);
};

const isAutoDetected = (personId) => {
  return autoDetectedPersons.value.includes(personId);
};

const getInitials = (person) => {
  const first = person.FirstName ? person.FirstName.charAt(0) : '';
  const last = person.LastName ? person.LastName.charAt(0) : '';
  return (first + last).toUpperCase() || '?';
};

const skipPersonSelection = async () => {
  await savePhotoToBackend([]);
};

const savePhoto = async () => {
  await savePhotoToBackend(selectedPersons.value);
};

const savePhotoToBackend = async (personIds) => {
  if (!store.capturedPhoto || !store.selectedEvent) {
    alert('Foto oder Event fehlt.');
    return;
  }

  try {
    saving.value = true;

    const result = await store.savePhoto(
      store.selectedEvent.ID,
      store.capturedPhoto,
      personIds
    );

    if (result.success) {
      // Save photo hash for QR code generation
      if (result.hash) {
        store.setSavedPhotoHash(result.hash);
      }
      // Go to success page
      router.push('/success');
    } else {
      alert('Fehler beim Speichern des Fotos.');
    }
  } catch (error) {
    console.error('Fehler beim Speichern:', error);
    alert('Foto konnte nicht gespeichert werden.');
  } finally {
    saving.value = false;
  }
};

const goBack = () => {
  router.push('/capture');
};

// Lifecycle
onMounted(async () => {
  if (!store.capturedPhoto) {
    router.push('/');
    return;
  }

  await loadPersons();

  // Perform face recognition if enabled and persons have images
  if (store.selectedEvent?.UsePersonRecognition && persons.value.length > 0) {
    // Wait for image to load
    if (photoImage.value?.complete) {
      await performFaceRecognition();
    } else {
      photoImage.value?.addEventListener('load', performFaceRecognition);
    }
  }
});
</script>
