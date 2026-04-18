import { createRouter, createWebHistory } from 'vue-router';
import EventSetup from './views/EventSetup.vue';
import PhotoCapture from './views/PhotoCapture.vue';
import PersonSelection from './views/PersonSelection.vue';
import Success from './views/Success.vue';

const routes = [
    {
        path: '/',
        name: 'EventSetup',
        component: EventSetup
    },
    {
        path: '/capture',
        name: 'PhotoCapture',
        component: PhotoCapture
    },
    {
        path: '/person-selection',
        name: 'PersonSelection',
        component: PersonSelection
    },
    {
        path: '/success',
        name: 'Success',
        component: Success
    }
];

const router = createRouter({
    history: createWebHistory('/photobox/'),
    routes
});

export default router;
