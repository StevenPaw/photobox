<template>
  <div class="person-selection">
    <div class="selection-container">
      <div class="photo-preview">
        <h2>Dein Foto</h2>
        <img :src="store.capturedPhoto" alt="Captured Photo" class="preview-image" />
      </div>

      <div class="person-selector">
        <h2>Wer ist auf dem Foto?</h2>
        <p class="subtitle">Wähle alle Personen aus, die auf dem Foto zu sehen sind</p>

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
            :class="['person-card', { selected: isSelected(person.ID) }]"
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
            <div class="person-name">{{ person.Title }}</div>
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

const router = useRouter();
const store = usePhotoboxStore();

const persons = ref([]);
const selectedPersons = ref([]);
const loading = ref(true);
const saving = ref(false);

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
      alert('Foto erfolgreich gespeichert! 🎉');
      // Reset and go back to capture or setup
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
onMounted(() => {
  if (!store.capturedPhoto) {
    router.push('/');
    return;
  }

  loadPersons();
});
</script>
