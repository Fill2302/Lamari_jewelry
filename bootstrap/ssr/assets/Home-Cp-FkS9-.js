import { t as StoreLayout_default } from "./StoreLayout-CSgOPgrw.js";
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
					if (_push) _push(`<title${_scopeId}>Сучасні прикраси</title><meta name="description" content="Lamari — сучасні прикраси з виразним характером."${_scopeId}><link rel="canonical" href="http://localhost"${_scopeId}><meta property="og:title" content="Lamari — сучасні прикраси"${_scopeId}>`);
					else return [
						createVNode("title", null, "Сучасні прикраси"),
						createVNode("meta", {
							name: "description",
							content: "Lamari — сучасні прикраси з виразним характером."
						}),
						createVNode("link", {
							rel: "canonical",
							href: "http://localhost"
						}),
						createVNode("meta", {
							property: "og:title",
							content: "Lamari — сучасні прикраси"
						})
					];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(StoreLayout_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<section class="hero"${_scopeId}><p class="eyebrow"${_scopeId}>NEW OBJECTS · 2026</p><h1${_scopeId}>Твоя форма.<br${_scopeId}><i${_scopeId}>Твоя історія.</i></h1><p${_scopeId}>Скульптурні прикраси для моментів, які хочеться запам’ятати.</p>`);
						_push(ssrRenderComponent(unref(Link), {
							href: "/categories/rings",
							class: "button"
						}, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) _push(`Дивитися колекцію`);
								else return [createTextVNode("Дивитися колекцію")];
							}),
							_: 1
						}, _parent, _scopeId));
						_push(`</section><!--[-->`);
						ssrRenderList(__props.categories, (category) => {
							_push(`<section class="section"${_scopeId}><div class="section-title"${_scopeId}><h2${_scopeId}>${ssrInterpolate(category.name)}</h2>`);
							_push(ssrRenderComponent(unref(Link), { href: `/categories/${category.slug}` }, {
								default: withCtx((_, _push, _parent, _scopeId) => {
									if (_push) _push(`Усі моделі →`);
									else return [createTextVNode("Усі моделі →")];
								}),
								_: 2
							}, _parent, _scopeId));
							_push(`</div><div class="grid"${_scopeId}><!--[-->`);
							ssrRenderList(category.products, (product) => {
								_push(ssrRenderComponent(unref(Link), {
									key: product.id,
									href: `/products/${product.slug}`,
									class: "card"
								}, {
									default: withCtx((_, _push, _parent, _scopeId) => {
										if (_push) _push(`<img${ssrRenderAttr("src", product.image_url)}${ssrRenderAttr("alt", product.name)}${_scopeId}><h3${_scopeId}>${ssrInterpolate(product.name)}</h3><p${_scopeId}>${ssrInterpolate((product.variants[0].price_amount / 100).toLocaleString("uk-UA"))} ₴</p>`);
										else return [
											createVNode("img", {
												src: product.image_url,
												alt: product.name
											}, null, 8, ["src", "alt"]),
											createVNode("h3", null, toDisplayString(product.name), 1),
											createVNode("p", null, toDisplayString((product.variants[0].price_amount / 100).toLocaleString("uk-UA")) + " ₴", 1)
										];
									}),
									_: 2
								}, _parent, _scopeId));
							});
							_push(`<!--]--></div></section>`);
						});
						_push(`<!--]-->`);
					} else return [createVNode("section", { class: "hero" }, [
						createVNode("p", { class: "eyebrow" }, "NEW OBJECTS · 2026"),
						createVNode("h1", null, [
							createTextVNode("Твоя форма."),
							createVNode("br"),
							createVNode("i", null, "Твоя історія.")
						]),
						createVNode("p", null, "Скульптурні прикраси для моментів, які хочеться запам’ятати."),
						createVNode(unref(Link), {
							href: "/categories/rings",
							class: "button"
						}, {
							default: withCtx(() => [createTextVNode("Дивитися колекцію")]),
							_: 1
						})
					]), (openBlock(true), createBlock(Fragment, null, renderList(__props.categories, (category) => {
						return openBlock(), createBlock("section", {
							key: category.id,
							class: "section"
						}, [createVNode("div", { class: "section-title" }, [createVNode("h2", null, toDisplayString(category.name), 1), createVNode(unref(Link), { href: `/categories/${category.slug}` }, {
							default: withCtx(() => [createTextVNode("Усі моделі →")]),
							_: 1
						}, 8, ["href"])]), createVNode("div", { class: "grid" }, [(openBlock(true), createBlock(Fragment, null, renderList(category.products, (product) => {
							return openBlock(), createBlock(unref(Link), {
								key: product.id,
								href: `/products/${product.slug}`,
								class: "card"
							}, {
								default: withCtx(() => [
									createVNode("img", {
										src: product.image_url,
										alt: product.name
									}, null, 8, ["src", "alt"]),
									createVNode("h3", null, toDisplayString(product.name), 1),
									createVNode("p", null, toDisplayString((product.variants[0].price_amount / 100).toLocaleString("uk-UA")) + " ₴", 1)
								]),
								_: 2
							}, 1032, ["href"]);
						}), 128))])]);
					}), 128))];
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
