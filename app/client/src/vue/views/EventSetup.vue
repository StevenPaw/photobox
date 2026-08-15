<template>
  <div class="event-setup">
    <div class="setup-container">
      <h1>Photobox Setup</h1>

      <!-- Resume Choice -->
      <section class="setup-section" v-if="showResumeChoice">
        <h2>Letzte Einstellungen gefunden</h2>
        <p>
          Event: <strong>{{ lastSettings.eventTitle }}</strong><br />
          Kamera: <strong>{{ lastSettings.cameraLabel }}</strong>
        </p>

        <div v-if="isResuming" class="camera-loading">
          Photobox wird gestartet...
        </div>
        <div v-else class="resume-choice-buttons">
          <button @click="resumeLastSettings" class="btn-start">
            ▶️ Fotobox weiternutzen
          </button>
          <button @click="reconfigure" class="btn-retry">
            ⚙️ Fotobox umkonfigurieren
          </button>
        </div>
      </section>

      <template v-else>
      <!-- Event Selection -->
      <section class="setup-section">
        <h2>1. Event auswählen</h2>
        <div class="event-selector">
          <select v-model="selectedEventId" @change="onEventChange" class="event-select">
            <option value="">Bitte Event auswählen...</option>
            <option v-for="event in events" :key="event.ID" :value="event.ID">
              {{ event.FormattedDate }} - {{ event.Title }}
            </option>
          </select>
        </div>
      </section>

      <!-- Filter Sets Management -->
      <section class="setup-section" v-if="selectedEventId">
        <h2>2. Filter Sets verwalten</h2>

        <!-- Available Filter Sets -->
        <div class="filterset-manager">
          <h3>Verfügbare Filter Sets</h3>
          <div class="filterset-list">
            <div
              v-for="filterSet in availableFilterSets"
              :key="filterSet.ID"
              class="filterset-item"
            >
              <span>{{ filterSet.Title }}</span>
              <button
                @click="addFilterSet(filterSet.ID)"
                :disabled="isFilterSetSelected(filterSet.ID)"
                class="btn-add"
              >
                {{ isFilterSetSelected(filterSet.ID) ? '✓ Hinzugefügt' : '+ Hinzufügen' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Selected Filter Sets -->
        <div class="filterset-manager selected" v-if="eventFilterSets.length > 0">
          <h3>Ausgewählte Filter Sets für dieses Event</h3>
          <div class="filterset-list">
            <div
              v-for="filterSet in eventFilterSets"
              :key="filterSet.ID"
              class="filterset-item selected"
            >
              <span>{{ filterSet.Title }}</span>
              <button
                @click="removeFilterSet(filterSet.ID)"
                class="btn-remove"
              >
                × Entfernen
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Camera Selection -->
      <section class="setup-section" v-if="selectedEventId && eventFilterSets.length > 0">
        <h2>3. Kamera auswählen</h2>

        <div v-if="camerasLoading" class="camera-loading">
          Lade verfügbare Kameras...
        </div>

        <div v-else-if="cameraError" class="camera-error">
          <p>⚠️ {{ cameraError }}</p>
          <button @click="requestCameraPermission" class="btn-retry">
            🔄 Berechtigung erneut anfordern
          </button>
        </div>

        <div v-else class="camera-selector">
          <select v-model="selectedCameraId" class="camera-select">
            <option value="">Bitte Kamera auswählen...</option>
            <option v-for="camera in cameras" :key="camera.deviceId" :value="camera.deviceId">
              {{ camera.label || `Kamera ${camera.deviceId.slice(0, 8)}` }}
            </option>
          </select>

          <p class="camera-hint" v-if="cameras.length === 0">
            Keine Kameras gefunden. Bitte stelle sicher, dass eine Kamera angeschlossen ist.
          </p>

          <button
            v-if="cameras.length === 0"
            @click="requestCameraPermission"
            class="btn-refresh"
          >
            🔄 Kameras neu laden
          </button>
        </div>
      </section>

      <!-- Start Button -->
      <section class="setup-section" v-if="canStart">
        <button @click="startPhotobox" class="btn-start">
          🎥 Photobox starten
        </button>
      </section>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { usePhotoboxStore } from '../store.js';
import { getCookie, setCookie } from '../../js/cookies.js';

const LAST_SETTINGS_COOKIE = 'photobox_last_settings';

const router = useRouter();
const store = usePhotoboxStore();

const events = ref([]);
const selectedEventId = ref('');
const availableFilterSets = ref([]);
const eventFilterSets = ref([]);
const cameras = ref([]);
const selectedCameraId = ref('');
const camerasLoading = ref(false);
const cameraError = ref('');
const showResumeChoice = ref(false);
const isResuming = ref(false);
const lastSettings = ref(null);
let mediaStream = null;

// Computed
const canStart = computed(() => {
  return selectedEventId.value &&
         eventFilterSets.value.length > 0 &&
         selectedCameraId.value;
});

// Methods
const loadEvents = async () => {
  events.value = await store.fetchEvents();
};

const loadFilterSets = async () => {
  availableFilterSets.value = await store.fetchFilterSets();
};

const loadEventFilterSets = async () => {
  if (!selectedEventId.value) return;
  eventFilterSets.value = await store.fetchEventFilterSets(selectedEventId.value);
};

const loadCameras = async () => {
  try {
    camerasLoading.value = true;
    cameraError.value = '';

    // First request camera permission to get labels
    try {
      mediaStream = await navigator.mediaDevices.getUserMedia({ video: true });
      // Stop the stream immediately, we just needed permission
      mediaStream.getTracks().forEach(track => track.stop());
    } catch (permError) {
      console.error('Kamera-Berechtigung verweigert:', permError);
      cameraError.value = 'Kamera-Zugriff wurde verweigert. Bitte erlaube den Zugriff in den Browser-Einstellungen.';
      camerasLoading.value = false;
      return;
    }

    // Now enumerate devices with labels
    const devices = await navigator.mediaDevices.enumerateDevices();
    cameras.value = devices.filter(device => device.kind === 'videoinput');

    // Auto-select first camera if only one is available
    if (cameras.value.length === 1) {
      selectedCameraId.value = cameras.value[0].deviceId;
    }

    camerasLoading.value = false;
  } catch (error) {
    console.error('Fehler beim Laden der Kameras:', error);
    cameraError.value = 'Kameras konnten nicht geladen werden: ' + error.message;
    camerasLoading.value = false;
  }
};

const requestCameraPermission = async () => {
  await loadCameras();
};

const onEventChange = async () => {
  eventFilterSets.value = [];
  selectedCameraId.value = '';
  if (selectedEventId.value) {
    await loadEventFilterSets();
  }
};

const addFilterSet = async (filterSetId) => {
  try {
    await store.addFilterSetToEvent(selectedEventId.value, filterSetId);
    await loadEventFilterSets();
  } catch (error) {
    console.error('Fehler beim Hinzufügen des FilterSets:', error);
    alert('FilterSet konnte nicht hinzugefügt werden.');
  }
};

const removeFilterSet = async (filterSetId) => {
  try {
    await store.removeFilterSetFromEvent(selectedEventId.value, filterSetId);
    await loadEventFilterSets();
  } catch (error) {
    console.error('Fehler beim Entfernen des FilterSets:', error);
    alert('FilterSet konnte nicht entfernt werden.');
  }
};

const isFilterSetSelected = (filterSetId) => {
  return eventFilterSets.value.some(fs => fs.ID === filterSetId);
};

const saveLastSettings = (event, cameraId, cameraLabel) => {
  setCookie(LAST_SETTINGS_COOKIE, JSON.stringify({
    eventId: event.ID,
    eventTitle: event.Title,
    cameraId,
    cameraLabel,
  }));
};

const startPhotobox = async () => {
  // Save settings to store
  const selectedEvent = events.value.find(e => e.ID == selectedEventId.value);
  store.setEvent(selectedEvent);
  store.setCamera(selectedCameraId.value);

  // Load filters for the event
  await store.loadFiltersForEvent(selectedEventId.value);

  // Initialize camera
  try {
    await store.initCamera();
  } catch (error) {
    alert('Kamera-Zugriff fehlgeschlagen. Bitte Berechtigungen prüfen.');
    return;
  }

  // Remember settings for next time
  const selectedCamera = cameras.value.find(c => c.deviceId === selectedCameraId.value);
  saveLastSettings(selectedEvent, selectedCameraId.value, selectedCamera?.label || '');

  // Navigate to camera view
  router.push('/capture');
};

const initSetup = async () => {
  await loadEvents();
  await loadFilterSets();
  // Request camera permission immediately on mount
  await loadCameras();
};

const reconfigure = async () => {
  showResumeChoice.value = false;
  await initSetup();

  // Prefill previous choices for convenience
  if (lastSettings.value) {
    const eventExists = events.value.some(e => e.ID == lastSettings.value.eventId);
    if (eventExists) {
      selectedEventId.value = String(lastSettings.value.eventId);
      await onEventChange();
      const cameraExists = cameras.value.some(c => c.deviceId === lastSettings.value.cameraId);
      if (cameraExists) {
        selectedCameraId.value = lastSettings.value.cameraId;
      }
    }
  }
};

const resumeLastSettings = async () => {
  isResuming.value = true;

  events.value = await store.fetchEvents();
  const selectedEvent = events.value.find(e => e.ID == lastSettings.value.eventId);
  if (!selectedEvent) {
    alert('Das zuletzt verwendete Event ist nicht mehr verfügbar.');
    isResuming.value = false;
    showResumeChoice.value = false;
    await initSetup();
    return;
  }

  store.setEvent(selectedEvent);
  store.setCamera(lastSettings.value.cameraId);

  await store.loadFiltersForEvent(selectedEvent.ID);

  try {
    await store.initCamera();
  } catch (error) {
    alert('Kamera-Zugriff fehlgeschlagen. Bitte Berechtigungen prüfen.');
    isResuming.value = false;
    showResumeChoice.value = false;
    await initSetup();
    return;
  }

  saveLastSettings(selectedEvent, lastSettings.value.cameraId, lastSettings.value.cameraLabel);
  router.push('/capture');
};

// Lifecycle
onMounted(async () => {
  const cookieRaw = getCookie(LAST_SETTINGS_COOKIE);
  if (cookieRaw) {
    try {
      const parsed = JSON.parse(cookieRaw);
      if (parsed && parsed.eventId && parsed.cameraId) {
        lastSettings.value = parsed;
        showResumeChoice.value = true;
        return;
      }
    } catch (error) {
      console.error('Ungültiges Einstellungs-Cookie:', error);
    }
  }

  await initSetup();
});
</script>
