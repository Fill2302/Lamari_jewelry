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
		"./Pages/Cart.vue": () => import("./assets/Cart-DVe-31Rh.js"),
		"./Pages/Category.vue": () => import("./assets/Category-TM3Ti1WM.js"),
		"./Pages/Checkout.vue": () => import("./assets/Checkout-Js4w-y3N.js"),
		"./Pages/FakePayment.vue": () => import("./assets/FakePayment-C3rKa_T5.js"),
		"./Pages/Home.vue": () => import("./assets/Home-Ch9S0AbD.js"),
		"./Pages/Information.vue": () => import("./assets/Information-D1AemnN6.js"),
		"./Pages/Product.vue": () => import("./assets/Product-CBPFV_1W.js"),
		"./Pages/ThankYou.vue": () => import("./assets/ThankYou-ZDApYR1-.js")
	})),
	setup: ({ App, props, plugin }) => createSSRApp({ render: () => h(App, props) }).use(plugin)
}), { host: "127.0.0.1" });
//#endregion
export {};
