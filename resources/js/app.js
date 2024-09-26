import './bootstrap';
import '../css/app.css';

import '@fortawesome/fontawesome-free/css/all.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import Toast from 'vue-toastification'; // Import vue-toastification
import 'vue-toastification/dist/index.css'; // Import the CSS for Toastification


const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Toast, options) // Register Vue Toastification
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
