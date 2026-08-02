import { createSSRApp, h } from "vue";
import { renderToString } from "@vue/server-renderer";
import { createInertiaApp } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
//#region node_modules/laravel-vite-plugin/inertia-helpers/index.js
async function resolvePageComponent(path, pages) {
	for (const p of Array.isArray(path) ? path : [path]) {
		const page = pages[p];
		if (typeof page === "undefined") continue;
		return typeof page === "function" ? page() : page;
	}
	throw new Error(`Page not found: ${path}`);
}
//#endregion
//#region resources/js/ssr.ts
createServer((page) => createInertiaApp({
	page,
	render: renderToString,
	title: (t) => t ? `${t} · Lamari` : "Lamari",
	resolve: (n) => resolvePageComponent(`./Pages/${n}.vue`, /* #__PURE__ */ Object.assign({
		"./Pages/Cart.vue": () => import("./assets/Cart-LmGG40KE.js"),
		"./Pages/Category.vue": () => import("./assets/Category-CnWCLwZ7.js"),
		"./Pages/Checkout.vue": () => import("./assets/Checkout-INwmoDjp.js"),
		"./Pages/FakePayment.vue": () => import("./assets/FakePayment-3yobppXJ.js"),
		"./Pages/Home.vue": () => import("./assets/Home-CZKHBL65.js"),
		"./Pages/Information.vue": () => import("./assets/Information-B0DyO3TP.js"),
		"./Pages/Product.vue": () => import("./assets/Product-CdGAgv25.js"),
		"./Pages/ThankYou.vue": () => import("./assets/ThankYou-cZsi2OZd.js")
	})),
	setup: ({ App, props, plugin }) => createSSRApp({ render: () => h(App, props) }).use(plugin)
}), { host: "127.0.0.1" });
//#endregion
export {};
