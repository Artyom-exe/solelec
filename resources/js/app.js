import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import "@splidejs/vue-splide/css";
import { createVfm } from "vue-final-modal";
import "vue-final-modal/style.css";

// Création du plugin de notification
const notificationPlugin = {
    install: (app) => {
        app.provide("showNotification", (message, type = "success") => {
            window.dispatchEvent(
                new CustomEvent("show-notification", {
                    detail: { message, type },
                })
            );
        });
        app.provide("hideNotification", () => {
            window.dispatchEvent(new CustomEvent("hide-notification"));
        });
    },
};

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        const vfm = createVfm();

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(vfm)
            .use(notificationPlugin) // Ajout du plugin de notification
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});

// Gestion des erreurs Inertia
document.addEventListener("inertia:error", (event) => {
    if (event.detail.response.status === 419) {
        // Erreur CSRF - recharger la page pour obtenir un nouveau token
        window.location.reload();
    }
});
