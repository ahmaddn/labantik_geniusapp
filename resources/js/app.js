import "../css/app.css";
import "./bootstrap";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";

const appName = import.meta.env.VITE_APP_NAME || "Genius";

createInertiaApp({
    title: (title) =>
        title
            ? `${title} - ${appName} - Web Media Pembelajaran Interaktif untuk Anak`
            : `${appName} - Web Media Pembelajaran Interaktif untuk Anak`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: "#03a5fc",
    },
});
