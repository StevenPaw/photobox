import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from '../vue/App.vue';
import router from '../vue/router.js';

// PWA Service Worker registrieren
import { registerSW } from 'virtual:pwa-register';

const updateSW = registerSW({
    onNeedRefresh() {
        console.log('Neue Version verfügbar. Bitte Seite neu laden.');
    },
    onOfflineReady() {
        console.log('App ist offline verfügbar.');
    },
    immediate: true
});

// Vue App initialisieren
const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.mount('#vue-root');

// Menu Toggle (falls benötigt)
document.addEventListener("DOMContentLoaded", function (event) {
    const menu_button = document.querySelector('[data-behaviour="toggle-menu"]');

    if (menu_button) {
        menu_button.addEventListener('click', () => {
            document.body.classList.toggle('body--show');
        });
    }
});
