import { t as StoreLayout_default } from "./StoreLayout-CSgOPgrw.js";
import { Fragment, createBlock, createVNode, defineComponent, openBlock, renderList, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderAttr, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
//#region resources/js/Pages/Category.vue?vue&type=script&setup=true&lang.ts
var Category_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Category",
	__ssrInlineRender: true,
	props: {
		category: {},
		products: {}
	},
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<title${_scopeId}>${ssrInterpolate(__props.category.seo_title || __props.category.name)}</title><meta name="description"${ssrRenderAttr("content", __props.category.seo_description || __props.category.description)}${_scopeId}><link rel="canonical"${ssrRenderAttr("href", `http://localhost/categories/${__props.category.slug}`)}${_scopeId}>`);
					else return [
						createVNode("title", null, toDisplayString(__props.category.seo_title || __props.category.name), 1),
						createVNode("meta", {
							name: "description",
							content: __props.category.seo_description || __props.category.description
						}, null, 8, ["content"]),
						createVNode("link", {
							rel: "canonical",
							href: `http://localhost/categories/${__props.category.slug}`
						}, null, 8, ["href"])
					];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(StoreLayout_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<section class="page-head"${_scopeId}><p class="eyebrow"${_scopeId}>КОЛЕКЦІЯ</p><h1${_scopeId}>${ssrInterpolate(__props.category.name)}</h1><p${_scopeId}>${ssrInterpolate(__props.category.description)}</p></section><section class="grid section"${_scopeId}><!--[-->`);
						ssrRenderList(__props.products, (product) => {
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
						_push(`<!--]--></section>`);
					} else return [createVNode("section", { class: "page-head" }, [
						createVNode("p", { class: "eyebrow" }, "КОЛЕКЦІЯ"),
						createVNode("h1", null, toDisplayString(__props.category.name), 1),
						createVNode("p", null, toDisplayString(__props.category.description), 1)
					]), createVNode("section", { class: "grid section" }, [(openBlock(true), createBlock(Fragment, null, renderList(__props.products, (product) => {
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
					}), 128))])];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/js/Pages/Category.vue
var _sfc_setup = Category_vue_vue_type_script_setup_true_lang_default.setup;
Category_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Category.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Category_default = Category_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { Category_default as default };
