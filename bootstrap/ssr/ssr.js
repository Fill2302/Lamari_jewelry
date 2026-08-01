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
		"./Pages/Cart.vue": () => import("./assets/Cart-BIfd4A-a.js"),
		"./Pages/Category.vue": () => import("./assets/Category-TgJ2Qst-.js"),
		"./Pages/Checkout.vue": () => import("./assets/Checkout-BhBPlE3Z.js"),
		"./Pages/FakePayment.vue": () => import("./assets/FakePayment-CRyQUkRn.js"),
		"./Pages/Home.vue": () => import("./assets/Home-Dw9yzXpv.js"),
		"./Pages/Information.vue": () => import("./assets/Information-D-0zBfn0.js"),
		"./Pages/Product.vue": () => import("./assets/Product-Bny9nLu9.js"),
		"./Pages/ThankYou.vue": () => import("./assets/ThankYou-56x7Cd4z.js")
	})),
	setup: ({ App, props, plugin }) => createSSRApp({ render: () => h(App, props) }).use(plugin)
}), { host: "127.0.0.1" });
//#endregion
export {};
