import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from '../vue/App.vue';
import router from '../vue/router.js';

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
