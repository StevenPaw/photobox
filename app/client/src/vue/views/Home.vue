<template>
  <div class="home">
    <h1>Willkommen zur Photobox</h1>
    <p>Vue.js läuft erfolgreich! 🎉</p>

    <div class="test-section">
      <button @click="counter++" class="test-button">
        Klicks: {{ counter }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const counter = ref(0);
</script>

<style scoped>
.home {
  padding: 2rem;
  text-align: center;
}

.test-section {
  margin-top: 2rem;
}

.test-button {
  padding: 1rem 2rem;
  font-size: 1.2rem;
  background: #42b983;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s;
}

.test-button:hover {
  background: #359268;
}
</style>
