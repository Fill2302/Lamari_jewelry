import { t as StoreLayout_default } from "./StoreLayout-DpbhNzPq.js";
import { Fragment, createBlock, createTextVNode, createVNode, defineComponent, openBlock, renderList, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderAttr, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
//#region resources/js/Pages/Home.vue?vue&type=script&setup=true&lang.ts
var Home_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Home",
	__ssrInlineRender: true,
	props: { categories: {} },
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<title${_scopeId}>Авторські прикраси ручної роботи</title><meta name="description" content="Lamari Jewelry — авторські прикраси ручної роботи: кольє, чокери, сережки, браслети та каблучки."${_scopeId}><link rel="canonical" href="http://localhost"${_scopeId}><meta property="og:title" content="Lamari Jewelry"${_scopeId}>`);
					else return [
						createVNode("title", null, "Авторські прикраси ручної роботи"),
						createVNode("meta", {
							name: "description",
							content: "Lamari Jewelry — авторські прикраси ручної роботи: кольє, чокери, сережки, браслети та каблучки."
						}),
						createVNode("link", {
							rel: "canonical",
							href: "http://localhost"
						}),
						createVNode("meta", {
							property: "og:title",
							content: "Lamari Jewelry"
						})
					];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(StoreLayout_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(ssrRenderComponent(unref(Link), {
							href: "/catalog",
							class: "home-campaign",
							"aria-label": "Перейти до каталогу всіх товарів"
						}, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) _push(`<picture${_scopeId}><source media="(max-width: 600px)" type="image/webp"${ssrRenderAttr("srcset", "/images/home/summer-collection-mobile-clean-v3.webp")}${_scopeId}><img${ssrRenderAttr("src", "/images/home/summer-collection-desktop.jpg")} alt="Summer Collection Lamari"${_scopeId}></picture><span class="mobile-campaign-copy"${_scopeId}><strong${_scopeId}>SUMMER COLLECTION</strong><span${_scopeId}>Каталог</span></span>`);
								else return [createVNode("picture", null, [createVNode("source", {
									media: "(max-width: 600px)",
									type: "image/webp",
									srcset: "/images/home/summer-collection-mobile-clean-v3.webp"
								}), createVNode("img", {
									src: "/images/home/summer-collection-desktop.jpg",
									alt: "Summer Collection Lamari"
								})]), createVNode("span", { class: "mobile-campaign-copy" }, [createVNode("strong", null, "SUMMER COLLECTION"), createVNode("span", null, "Каталог")])];
							}),
							_: 1
						}, _parent, _scopeId));
						_push(`<section class="section"${_scopeId}><div class="section-title"${_scopeId}><div${_scopeId}><p class="eyebrow"${_scopeId}>КАТАЛОГ</p><h2${_scopeId}>Знайди свою прикрасу</h2></div></div><div class="department-grid"${_scopeId}><!--[-->`);
						ssrRenderList(__props.categories.filter((c) => !["sale", "summer"].includes(c.slug)), (category) => {
							_push(ssrRenderComponent(unref(Link), {
								key: category.id,
								href: `/categories/${category.slug}`,
								class: "department"
							}, {
								default: withCtx((_, _push, _parent, _scopeId) => {
									if (_push) _push(`<span${_scopeId}>${ssrInterpolate(category.name)}</span><small${_scopeId}>${ssrInterpolate(category.children.length ? `${category.children.length} розділів` : "Переглянути")}</small>`);
									else return [createVNode("span", null, toDisplayString(category.name), 1), createVNode("small", null, toDisplayString(category.children.length ? `${category.children.length} розділів` : "Переглянути"), 1)];
								}),
								_: 2
							}, _parent, _scopeId));
						});
						_push(`<!--]--></div></section><section class="brand-story"${_scopeId}><p class="eyebrow"${_scopeId}>MADE BY HAND</p><h2${_scopeId}>Особливі деталі.<br${_scopeId}>Твоя особиста історія.</h2><p${_scopeId}>Кожне замовлення безкоштовно пакуємо у брендовану подарункову коробочку.</p></section>`);
					} else return [
						createVNode(unref(Link), {
							href: "/catalog",
							class: "home-campaign",
							"aria-label": "Перейти до каталогу всіх товарів"
						}, {
							default: withCtx(() => [createVNode("picture", null, [createVNode("source", {
								media: "(max-width: 600px)",
								type: "image/webp",
								srcset: "/images/home/summer-collection-mobile-clean-v3.webp"
							}), createVNode("img", {
								src: "/images/home/summer-collection-desktop.jpg",
								alt: "Summer Collection Lamari"
							})]), createVNode("span", { class: "mobile-campaign-copy" }, [createVNode("strong", null, "SUMMER COLLECTION"), createVNode("span", null, "Каталог")])]),
							_: 1
						}),
						createVNode("section", { class: "section" }, [createVNode("div", { class: "section-title" }, [createVNode("div", null, [createVNode("p", { class: "eyebrow" }, "КАТАЛОГ"), createVNode("h2", null, "Знайди свою прикрасу")])]), createVNode("div", { class: "department-grid" }, [(openBlock(true), createBlock(Fragment, null, renderList(__props.categories.filter((c) => !["sale", "summer"].includes(c.slug)), (category) => {
							return openBlock(), createBlock(unref(Link), {
								key: category.id,
								href: `/categories/${category.slug}`,
								class: "department"
							}, {
								default: withCtx(() => [createVNode("span", null, toDisplayString(category.name), 1), createVNode("small", null, toDisplayString(category.children.length ? `${category.children.length} розділів` : "Переглянути"), 1)]),
								_: 2
							}, 1032, ["href"]);
						}), 128))])]),
						createVNode("section", { class: "brand-story" }, [
							createVNode("p", { class: "eyebrow" }, "MADE BY HAND"),
							createVNode("h2", null, [
								createTextVNode("Особливі деталі."),
								createVNode("br"),
								createTextVNode("Твоя особиста історія.")
							]),
							createVNode("p", null, "Кожне замовлення безкоштовно пакуємо у брендовану подарункову коробочку.")
						])
					];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/js/Pages/Home.vue
var _sfc_setup = Home_vue_vue_type_script_setup_true_lang_default.setup;
Home_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Home.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Home_default = Home_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { Home_default as default };
