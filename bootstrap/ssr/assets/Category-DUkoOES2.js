import { t as StoreLayout_default } from "./StoreLayout-3_rn2Txl.js";
import { Fragment, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, openBlock, renderList, toDisplayString, unref, useSSRContext, withCtx } from "vue";
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
		const image = (product) => product.media?.find((m) => m.type === "image")?.url || product.image_url;
		const asset = (url) => url?.startsWith("http") ? url : `/storage/${url}`;
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
						_push(`<section class="page-head catalog-head"${_scopeId}><p class="eyebrow"${_scopeId}>КАТАЛОГ LAMARI</p><h1${_scopeId}>${ssrInterpolate(__props.category.name)}</h1><p${_scopeId}>${ssrInterpolate(__props.category.description)}</p>`);
						if (__props.category.children?.length) {
							_push(`<div class="subcategories"${_scopeId}><!--[-->`);
							ssrRenderList(__props.category.children, (child) => {
								_push(ssrRenderComponent(unref(Link), { href: `/categories/${child.slug}` }, {
									default: withCtx((_, _push, _parent, _scopeId) => {
										if (_push) _push(`${ssrInterpolate(child.name)}`);
										else return [createTextVNode(toDisplayString(child.name), 1)];
									}),
									_: 2
								}, _parent, _scopeId));
							});
							_push(`<!--]--></div>`);
						} else _push(`<!---->`);
						_push(`</section><div class="catalog-tools"${_scopeId}><span${_scopeId}>${ssrInterpolate(__props.products.length)} товарів</span><button${_scopeId}>Фільтри +</button><span${_scopeId}>За новизною ↓</span></div>`);
						if (__props.products.length) {
							_push(`<section class="product-catalog"${_scopeId}><!--[-->`);
							ssrRenderList(__props.products, (product) => {
								_push(ssrRenderComponent(unref(Link), {
									key: product.id,
									href: `/products/${product.slug}`,
									class: "catalog-card"
								}, {
									default: withCtx((_, _push, _parent, _scopeId) => {
										if (_push) {
											_push(`<div class="catalog-image"${_scopeId}><img${ssrRenderAttr("src", asset(image(product)))}${ssrRenderAttr("alt", product.name)}${_scopeId}>`);
											if (product.media?.some((m) => m.type === "video")) _push(`<span class="video-badge"${_scopeId}>▶ Відео</span>`);
											else _push(`<!---->`);
											_push(`</div><h3${_scopeId}>${ssrInterpolate(product.name)}</h3><p${_scopeId}>${ssrInterpolate((product.variants[0].price_amount / 100).toLocaleString("uk-UA"))} ₴</p><small${_scopeId}>Розміри: ${ssrInterpolate(product.variants.map((v) => v.name).join(" · "))}</small>`);
										} else return [
											createVNode("div", { class: "catalog-image" }, [createVNode("img", {
												src: asset(image(product)),
												alt: product.name
											}, null, 8, ["src", "alt"]), product.media?.some((m) => m.type === "video") ? (openBlock(), createBlock("span", {
												key: 0,
												class: "video-badge"
											}, "▶ Відео")) : createCommentVNode("", true)]),
											createVNode("h3", null, toDisplayString(product.name), 1),
											createVNode("p", null, toDisplayString((product.variants[0].price_amount / 100).toLocaleString("uk-UA")) + " ₴", 1),
											createVNode("small", null, "Розміри: " + toDisplayString(product.variants.map((v) => v.name).join(" · ")), 1)
										];
									}),
									_: 2
								}, _parent, _scopeId));
							});
							_push(`<!--]--></section>`);
						} else _push(`<section class="empty-category"${_scopeId}>Товари цього розділу готуються до імпорту з чинного каталогу.</section>`);
					} else return [
						createVNode("section", { class: "page-head catalog-head" }, [
							createVNode("p", { class: "eyebrow" }, "КАТАЛОГ LAMARI"),
							createVNode("h1", null, toDisplayString(__props.category.name), 1),
							createVNode("p", null, toDisplayString(__props.category.description), 1),
							__props.category.children?.length ? (openBlock(), createBlock("div", {
								key: 0,
								class: "subcategories"
							}, [(openBlock(true), createBlock(Fragment, null, renderList(__props.category.children, (child) => {
								return openBlock(), createBlock(unref(Link), { href: `/categories/${child.slug}` }, {
									default: withCtx(() => [createTextVNode(toDisplayString(child.name), 1)]),
									_: 2
								}, 1032, ["href"]);
							}), 256))])) : createCommentVNode("", true)
						]),
						createVNode("div", { class: "catalog-tools" }, [
							createVNode("span", null, toDisplayString(__props.products.length) + " товарів", 1),
							createVNode("button", null, "Фільтри +"),
							createVNode("span", null, "За новизною ↓")
						]),
						__props.products.length ? (openBlock(), createBlock("section", {
							key: 0,
							class: "product-catalog"
						}, [(openBlock(true), createBlock(Fragment, null, renderList(__props.products, (product) => {
							return openBlock(), createBlock(unref(Link), {
								key: product.id,
								href: `/products/${product.slug}`,
								class: "catalog-card"
							}, {
								default: withCtx(() => [
									createVNode("div", { class: "catalog-image" }, [createVNode("img", {
										src: asset(image(product)),
										alt: product.name
									}, null, 8, ["src", "alt"]), product.media?.some((m) => m.type === "video") ? (openBlock(), createBlock("span", {
										key: 0,
										class: "video-badge"
									}, "▶ Відео")) : createCommentVNode("", true)]),
									createVNode("h3", null, toDisplayString(product.name), 1),
									createVNode("p", null, toDisplayString((product.variants[0].price_amount / 100).toLocaleString("uk-UA")) + " ₴", 1),
									createVNode("small", null, "Розміри: " + toDisplayString(product.variants.map((v) => v.name).join(" · ")), 1)
								]),
								_: 2
							}, 1032, ["href"]);
						}), 128))])) : (openBlock(), createBlock("section", {
							key: 1,
							class: "empty-category"
						}, "Товари цього розділу готуються до імпорту з чинного каталогу."))
					];
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
