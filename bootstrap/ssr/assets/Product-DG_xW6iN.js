import { t as StoreLayout_default } from "./StoreLayout-3_rn2Txl.js";
import { Fragment, computed, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, openBlock, ref, renderList, resolveDynamicComponent, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrRenderAttr, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderVNode } from "vue/server-renderer";
//#region resources/js/Pages/Product.vue?vue&type=script&setup=true&lang.ts
var Product_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Product",
	__ssrInlineRender: true,
	props: { product: {} },
	setup(__props) {
		const p = __props;
		const selected = ref(p.product.variants[0]?.id);
		const form = useForm({ quantity: 1 });
		const add = () => form.post(`/cart/${selected.value}`, { preserveScroll: true });
		const asset = (url) => !url ? "" : url.startsWith("http") ? url : `/storage/${url}`;
		const media = computed(() => p.product.media?.length ? p.product.media : [{
			type: "image",
			url: p.product.image_url,
			alt: p.product.name
		}]);
		const selectedVariant = computed(() => p.product.variants.find((v) => v.id === selected.value));
		const schema = {
			"@context": "https://schema.org",
			"@type": "Product",
			name: p.product.name,
			image: media.value.filter((m) => m.type === "image").map((m) => asset(m.url)),
			description: p.product.description,
			sku: p.product.variants[0]?.sku,
			offers: {
				"@type": "Offer",
				priceCurrency: "UAH",
				price: p.product.variants[0]?.price_amount / 100,
				availability: "https://schema.org/InStock"
			}
		};
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<title${_scopeId}>${ssrInterpolate(__props.product.seo_title || __props.product.name)}</title><meta name="description"${ssrRenderAttr("content", __props.product.seo_description || __props.product.description)}${_scopeId}><link rel="canonical"${ssrRenderAttr("href", `http://localhost/products/${__props.product.slug}`)}${_scopeId}><meta property="og:type" content="product"${_scopeId}><meta property="og:title"${ssrRenderAttr("content", __props.product.name)}${_scopeId}>`);
						ssrRenderVNode(_push, createVNode(resolveDynamicComponent("script"), { type: "application/ld+json" }, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) _push(`${ssrInterpolate(JSON.stringify(schema))}`);
								else return [createTextVNode(toDisplayString(JSON.stringify(schema)), 1)];
							}),
							_: 1
						}), _parent, _scopeId);
					} else return [
						createVNode("title", null, toDisplayString(__props.product.seo_title || __props.product.name), 1),
						createVNode("meta", {
							name: "description",
							content: __props.product.seo_description || __props.product.description
						}, null, 8, ["content"]),
						createVNode("link", {
							rel: "canonical",
							href: `http://localhost/products/${__props.product.slug}`
						}, null, 8, ["href"]),
						createVNode("meta", {
							property: "og:type",
							content: "product"
						}),
						createVNode("meta", {
							property: "og:title",
							content: __props.product.name
						}, null, 8, ["content"]),
						(openBlock(), createBlock(resolveDynamicComponent("script"), { type: "application/ld+json" }, {
							default: withCtx(() => [createTextVNode(toDisplayString(JSON.stringify(schema)), 1)]),
							_: 1
						}))
					];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(StoreLayout_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<div class="breadcrumbs"${_scopeId}>`);
						_push(ssrRenderComponent(unref(Link), { href: "/" }, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) _push(`Головна`);
								else return [createTextVNode("Головна")];
							}),
							_: 1
						}, _parent, _scopeId));
						_push(` / `);
						_push(ssrRenderComponent(unref(Link), { href: `/categories/${__props.product.category.parent?.slug || __props.product.category.slug}` }, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) _push(`${ssrInterpolate(__props.product.category.parent?.name || __props.product.category.name)}`);
								else return [createTextVNode(toDisplayString(__props.product.category.parent?.name || __props.product.category.name), 1)];
							}),
							_: 1
						}, _parent, _scopeId));
						_push(` / ${ssrInterpolate(__props.product.name)}</div><section class="product-lace"${_scopeId}><div class="media-grid"${_scopeId}><!--[-->`);
						ssrRenderList(media.value, (item) => {
							_push(`<figure class="${ssrRenderClass({ video: item.type === "video" })}"${_scopeId}>`);
							if (item.type === "image") _push(`<img${ssrRenderAttr("src", asset(item.url))}${ssrRenderAttr("alt", item.alt || __props.product.name)} loading="lazy"${_scopeId}>`);
							else _push(`<video${ssrRenderAttr("src", asset(item.url))}${ssrRenderAttr("poster", asset(item.poster_url))} controls muted playsinline preload="metadata"${_scopeId}>Ваш браузер не підтримує відео.</video>`);
							if (item.type === "video") _push(`<span class="media-label"${_scopeId}>Відео</span>`);
							else _push(`<!---->`);
							_push(`</figure>`);
						});
						_push(`<!--]--></div><aside class="buy-panel"${_scopeId}><p class="eyebrow"${_scopeId}>${ssrInterpolate(__props.product.category.name)}</p><h1${_scopeId}>${ssrInterpolate(__props.product.name)}</h1><p class="sku"${_scopeId}>Артикул ${ssrInterpolate(selectedVariant.value?.sku)} · <span class="in-stock"${_scopeId}>В наявності</span></p><p class="price"${_scopeId}>${ssrInterpolate((selectedVariant.value?.price_amount / 100).toLocaleString("uk-UA"))} ₴</p><label${_scopeId}>Оберіть розмір <div class="variant-pills"${_scopeId}><!--[-->`);
						ssrRenderList(__props.product.variants, (v) => {
							_push(`<button class="${ssrRenderClass({ active: selected.value === v.id })}"${_scopeId}>${ssrInterpolate(v.name)}</button>`);
						});
						_push(`<!--]--></div></label><button class="button buy"${ssrIncludeBooleanAttr(unref(form).processing || !selected.value) ? " disabled" : ""}${_scopeId}>Додати в кошик</button><div class="product-benefits"${_scopeId}><span${_scopeId}>Безкоштовне брендоване пакування</span><span${_scopeId}>Відправлення 1–3 робочі дні</span></div><details open${_scopeId}><summary${_scopeId}>Характеристики</summary><dl${_scopeId}><!--[-->`);
						ssrRenderList(__props.product.characteristics, (value, key) => {
							_push(`<!--[--><dt${_scopeId}>${ssrInterpolate(key)}</dt><dd${_scopeId}>${ssrInterpolate(value)}</dd><!--]-->`);
						});
						_push(`<!--]--><dt${_scopeId}>Матеріал</dt><dd${_scopeId}>${ssrInterpolate(__props.product.material)}</dd></dl></details><details${_scopeId}><summary${_scopeId}>Опис товару</summary><p${_scopeId}>${ssrInterpolate(__props.product.description)}</p></details><details${_scopeId}><summary${_scopeId}>Упаковка</summary><p${_scopeId}>${ssrInterpolate(__props.product.packaging_text)}</p></details><details${_scopeId}><summary${_scopeId}>Догляд</summary><p${_scopeId}>${ssrInterpolate(__props.product.care_text)}</p></details><details${_scopeId}><summary${_scopeId}>Доставка та оплата</summary><p${_scopeId}>Доставка Україною та за кордон. Точний спосіб і вартість будуть доступні під час оформлення.</p></details></aside></section>`);
					} else return [createVNode("div", { class: "breadcrumbs" }, [
						createVNode(unref(Link), { href: "/" }, {
							default: withCtx(() => [createTextVNode("Головна")]),
							_: 1
						}),
						createTextVNode(" / "),
						createVNode(unref(Link), { href: `/categories/${__props.product.category.parent?.slug || __props.product.category.slug}` }, {
							default: withCtx(() => [createTextVNode(toDisplayString(__props.product.category.parent?.name || __props.product.category.name), 1)]),
							_: 1
						}, 8, ["href"]),
						createTextVNode(" / " + toDisplayString(__props.product.name), 1)
					]), createVNode("section", { class: "product-lace" }, [createVNode("div", { class: "media-grid" }, [(openBlock(true), createBlock(Fragment, null, renderList(media.value, (item) => {
						return openBlock(), createBlock("figure", {
							key: item.id || item.url,
							class: { video: item.type === "video" }
						}, [item.type === "image" ? (openBlock(), createBlock("img", {
							key: 0,
							src: asset(item.url),
							alt: item.alt || __props.product.name,
							loading: "lazy"
						}, null, 8, ["src", "alt"])) : (openBlock(), createBlock("video", {
							key: 1,
							src: asset(item.url),
							poster: asset(item.poster_url),
							controls: "",
							muted: "",
							playsinline: "",
							preload: "metadata"
						}, "Ваш браузер не підтримує відео.", 8, ["src", "poster"])), item.type === "video" ? (openBlock(), createBlock("span", {
							key: 2,
							class: "media-label"
						}, "Відео")) : createCommentVNode("", true)], 2);
					}), 128))]), createVNode("aside", { class: "buy-panel" }, [
						createVNode("p", { class: "eyebrow" }, toDisplayString(__props.product.category.name), 1),
						createVNode("h1", null, toDisplayString(__props.product.name), 1),
						createVNode("p", { class: "sku" }, [createTextVNode("Артикул " + toDisplayString(selectedVariant.value?.sku) + " · ", 1), createVNode("span", { class: "in-stock" }, "В наявності")]),
						createVNode("p", { class: "price" }, toDisplayString((selectedVariant.value?.price_amount / 100).toLocaleString("uk-UA")) + " ₴", 1),
						createVNode("label", null, [createTextVNode("Оберіть розмір "), createVNode("div", { class: "variant-pills" }, [(openBlock(true), createBlock(Fragment, null, renderList(__props.product.variants, (v) => {
							return openBlock(), createBlock("button", {
								key: v.id,
								class: { active: selected.value === v.id },
								onClick: ($event) => selected.value = v.id
							}, toDisplayString(v.name), 11, ["onClick"]);
						}), 128))])]),
						createVNode("button", {
							class: "button buy",
							onClick: add,
							disabled: unref(form).processing || !selected.value
						}, "Додати в кошик", 8, ["disabled"]),
						createVNode("div", { class: "product-benefits" }, [createVNode("span", null, "Безкоштовне брендоване пакування"), createVNode("span", null, "Відправлення 1–3 робочі дні")]),
						createVNode("details", { open: "" }, [createVNode("summary", null, "Характеристики"), createVNode("dl", null, [
							(openBlock(true), createBlock(Fragment, null, renderList(__props.product.characteristics, (value, key) => {
								return openBlock(), createBlock(Fragment, null, [createVNode("dt", null, toDisplayString(key), 1), createVNode("dd", null, toDisplayString(value), 1)], 64);
							}), 256)),
							createVNode("dt", null, "Матеріал"),
							createVNode("dd", null, toDisplayString(__props.product.material), 1)
						])]),
						createVNode("details", null, [createVNode("summary", null, "Опис товару"), createVNode("p", null, toDisplayString(__props.product.description), 1)]),
						createVNode("details", null, [createVNode("summary", null, "Упаковка"), createVNode("p", null, toDisplayString(__props.product.packaging_text), 1)]),
						createVNode("details", null, [createVNode("summary", null, "Догляд"), createVNode("p", null, toDisplayString(__props.product.care_text), 1)]),
						createVNode("details", null, [createVNode("summary", null, "Доставка та оплата"), createVNode("p", null, "Доставка Україною та за кордон. Точний спосіб і вартість будуть доступні під час оформлення.")])
					])])];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/js/Pages/Product.vue
var _sfc_setup = Product_vue_vue_type_script_setup_true_lang_default.setup;
Product_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Product.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Product_default = Product_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { Product_default as default };
