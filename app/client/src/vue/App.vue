<template>
  <div class="photobox-app">
    <router-view v-slot="{ Component }">
      <transition name="fade" mode="out-in">
        <component v-if="Component" :is="Component" />
      </transition>
    </router-view>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { usePhotoboxStore } from './store.js';

const store = usePhotoboxStore();

// Clean up camera stream when page is unloaded
const handleBeforeUnload = () => {
  store.stopCamera();
};

onMounted(() => {
  window.addEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
  store.stopCamera();
});
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.photobox-app {
  min-height: 100vh;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
