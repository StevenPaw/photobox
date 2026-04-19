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
</style>
