import { t as StoreLayout_default } from "./StoreLayout-CSgOPgrw.js";
import { Fragment, createBlock, createTextVNode, createVNode, defineComponent, openBlock, ref, renderList, resolveDynamicComponent, toDisplayString, unref, useSSRContext, vModelSelect, withCtx, withDirectives } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrLooseContain, ssrLooseEqual, ssrRenderAttr, ssrRenderComponent, ssrRenderList, ssrRenderVNode } from "vue/server-renderer";
//#region resources/js/Pages/Product.vue?vue&type=script&setup=true&lang.ts
var Product_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Product",
	__ssrInlineRender: true,
	props: { product: {} },
	setup(__props) {
		const p = __props;
		const selected = ref(p.product.variants[0].id);
		const form = useForm({ quantity: 1 });
		const add = () => form.post(`/cart/${selected.value}`, { preserveScroll: true });
		const schema = {
			"@context": "https://schema.org",
			"@type": "Product",
			name: p.product.name,
			image: p.product.image_url,
			description: p.product.description,
			offers: {
				"@type": "Offer",
				priceCurrency: "UAH",
				price: p.product.variants[0].price_amount / 100,
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
						_push(`<section class="product"${_scopeId}><img${ssrRenderAttr("src", __props.product.image_url)}${ssrRenderAttr("alt", __props.product.name)}${_scopeId}><div${_scopeId}><p class="eyebrow"${_scopeId}>${ssrInterpolate(__props.product.category.name)}</p><h1${_scopeId}>${ssrInterpolate(__props.product.name)}</h1><p class="price"${_scopeId}>${ssrInterpolate((__props.product.variants.find((v) => v.id === selected.value)?.price_amount / 100).toLocaleString("uk-UA"))} ₴</p><p${_scopeId}>${ssrInterpolate(__props.product.description)}</p><p class="muted"${_scopeId}>Матеріал: ${ssrInterpolate(__props.product.material)}</p><label${_scopeId}>Варіант<select${_scopeId}><!--[-->`);
						ssrRenderList(__props.product.variants, (v) => {
							_push(`<option${ssrRenderAttr("value", v.id)}${ssrIncludeBooleanAttr(Array.isArray(selected.value) ? ssrLooseContain(selected.value, v.id) : ssrLooseEqual(selected.value, v.id)) ? " selected" : ""}${_scopeId}>${ssrInterpolate(v.name)} · ${ssrInterpolate(v.stock_on_hand - v.stock_reserved)} шт.</option>`);
						});
						_push(`<!--]--></select></label><button class="button"${ssrIncludeBooleanAttr(unref(form).processing) ? " disabled" : ""}${_scopeId}>Додати до кошика</button></div></section>`);
					} else return [createVNode("section", { class: "product" }, [createVNode("img", {
						src: __props.product.image_url,
						alt: __props.product.name
					}, null, 8, ["src", "alt"]), createVNode("div", null, [
						createVNode("p", { class: "eyebrow" }, toDisplayString(__props.product.category.name), 1),
						createVNode("h1", null, toDisplayString(__props.product.name), 1),
						createVNode("p", { class: "price" }, toDisplayString((__props.product.variants.find((v) => v.id === selected.value)?.price_amount / 100).toLocaleString("uk-UA")) + " ₴", 1),
						createVNode("p", null, toDisplayString(__props.product.description), 1),
						createVNode("p", { class: "muted" }, "Матеріал: " + toDisplayString(__props.product.material), 1),
						createVNode("label", null, [createTextVNode("Варіант"), withDirectives(createVNode("select", { "onUpdate:modelValue": ($event) => selected.value = $event }, [(openBlock(true), createBlock(Fragment, null, renderList(__props.product.variants, (v) => {
							return openBlock(), createBlock("option", { value: v.id }, toDisplayString(v.name) + " · " + toDisplayString(v.stock_on_hand - v.stock_reserved) + " шт.", 9, ["value"]);
						}), 256))], 8, ["onUpdate:modelValue"]), [[vModelSelect, selected.value]])]),
						createVNode("button", {
							class: "button",
							onClick: add,
							disabled: unref(form).processing
						}, "Додати до кошика", 8, ["disabled"])
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
