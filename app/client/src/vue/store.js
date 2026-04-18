import { defineStore } from 'pinia';

export const usePhotoboxStore = defineStore('photobox', {
    state: () => ({
        selectedEvent: null,
        selectedCamera: null,
        mediaStream: null,
        availableFilters: [],
        currentFilterIndex: 0,
        capturedPhoto: null,
        selectedPersons: [],
        savedPhotoHash: null,
    }),

    getters: {
        currentFilter: (state) => {
            if (state.availableFilters.length === 0) return null;
            return state.availableFilters[state.currentFilterIndex] || null;
        },
        hasEvent: (state) => state.selectedEvent !== null,
        hasFilters: (state) => state.availableFilters.length > 0,
    },

    actions: {
        async fetchEvents() {
            const response = await fetch('/api/events');
            return await response.json();
        },

        async fetchFilterSets() {
            const response = await fetch('/api/filtersets');
            return await response.json();
        },

        async fetchEventFilterSets(eventId) {
            const response = await fetch(`/api/events/${eventId}/filtersets`);
            return await response.json();
        },

        async addFilterSetToEvent(eventId, filterSetId) {
            const response = await fetch(`/api/events/${eventId}/filtersets`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ filterSetId }),
            });
            return await response.json();
        },

        async removeFilterSetFromEvent(eventId, filterSetId) {
            const response = await fetch(`/api/events/${eventId}/filtersets/${filterSetId}`, {
                method: 'DELETE',
            });
            return await response.json();
        },

        async loadFiltersForEvent(eventId) {
            // Get all filter sets for the event
            const filterSets = await this.fetchEventFilterSets(eventId);

            // Load all filters from all filter sets
            const allFilters = [];
            for (const filterSet of filterSets) {
                const response = await fetch(`/api/filtersets/${filterSet.ID}/filters`);
                const filters = await response.json();
                allFilters.push(...filters);
            }

            this.availableFilters = allFilters;
            this.currentFilterIndex = 0;
        },

        async fetchEventPersons(eventId) {
            const response = await fetch(`/api/events/${eventId}/persons`);
            return await response.json();
        },

        async savePhoto(eventId, imageData, personIds = []) {
            const response = await fetch('/api/photos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    eventId,
                    imageData,
                    personIds,
                }),
            });
            return await response.json();
        },

        setEvent(event) {
            this.selectedEvent = event;
        },

        setCamera(deviceId) {
            this.selectedCamera = deviceId;
        },

        nextFilter() {
            if (this.availableFilters.length === 0) return;
            this.currentFilterIndex = (this.currentFilterIndex + 1) % this.availableFilters.length;
        },

        previousFilter() {
            if (this.availableFilters.length === 0) return;
            this.currentFilterIndex = (this.currentFilterIndex - 1 + this.availableFilters.length) % this.availableFilters.length;
        },

        setCapturedPhoto(photoData) {
            this.capturedPhoto = photoData;
        },

        setSelectedPersons(personIds) {
            this.selectedPersons = personIds;
        },

        async initCamera() {
            try {
                // Stop existing stream if any
                this.stopCamera();

                const constraints = {
                    video: {
                        deviceId: this.selectedCamera ? { exact: this.selectedCamera } : undefined,
                        width: { ideal: 1920 },
                        height: { ideal: 1080 }
                    }
                };

                this.mediaStream = await navigator.mediaDevices.getUserMedia(constraints);
                return this.mediaStream;
            } catch (error) {
                console.error('Fehler beim Zugriff auf die Kamera:', error);
                throw error;
            }
        },

        stopCamera() {
            if (this.mediaStream) {
                this.mediaStream.getTracks().forEach(track => track.stop());
                this.mediaStream = null;
            }
        },

        setSavedPhotoHash(hash) {
            this.savedPhotoHash = hash;
        },

        reset() {
            this.stopCamera();
            this.selectedEvent = null;
            this.selectedCamera = null;
            this.availableFilters = [];
            this.currentFilterIndex = 0;
            this.capturedPhoto = null;
            this.selectedPersons = [];
            this.savedPhotoHash = null;
        },
    },
});
