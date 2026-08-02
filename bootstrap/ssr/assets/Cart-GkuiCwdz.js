import { t as StoreLayout_default } from "./StoreLayout-CI3WdeRz.js";
import { Fragment, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, openBlock, renderList, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrRenderAttr, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
//#region resources/js/Pages/Cart.vue?vue&type=script&setup=true&lang.ts
var Cart_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Cart",
	__ssrInlineRender: true,
	props: {
		items: {},
		total: {}
	},
	setup(__props) {
		const props = __props;
		const discountTotal = () => props.items.reduce((sum, item) => sum + (item.discount_total || 0), 0);
		const asset = (url) => !url ? "" : url.startsWith("http") ? url : `/storage/${url}`;
		const itemImage = (item) => asset(item.variant.product.media?.find((media) => media.type === "image")?.url || item.variant.product.image_url);
		const setVariant = (item, variantId) => router.put(`/cart/${item.variant.id}/variant`, { variant_id: variantId }, { preserveScroll: true });
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Кошик" }, null, _parent));
			_push(ssrRenderComponent(StoreLayout_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<section class="narrow cart-page"${_scopeId}><h1${_scopeId}>Кошик</h1>`);
						if (!__props.items.length) {
							_push(`<div class="empty"${_scopeId}>Кошик поки порожній. `);
							_push(ssrRenderComponent(unref(Link), { href: "/" }, {
								default: withCtx((_, _push, _parent, _scopeId) => {
									if (_push) _push(`До каталогу`);
									else return [createTextVNode("До каталогу")];
								}),
								_: 1
							}, _parent, _scopeId));
							_push(`</div>`);
						} else _push(`<!---->`);
						_push(`<!--[-->`);
						ssrRenderList(__props.items, (item) => {
							_push(`<article class="cart-row"${_scopeId}><img${ssrRenderAttr("src", itemImage(item))}${ssrRenderAttr("alt", item.variant.product.name)}${_scopeId}><div${_scopeId}><b${_scopeId}>${ssrInterpolate(item.variant.product.name)}</b><label class="cart-size"${_scopeId}>Довжина<select${ssrRenderAttr("value", item.variant.id)}${_scopeId}><!--[-->`);
							ssrRenderList(item.variant.product.variants, (variant) => {
								_push(`<option${ssrRenderAttr("value", variant.id)}${ssrIncludeBooleanAttr(!variant.is_active || variant.stock_on_hand <= variant.stock_reserved) ? " disabled" : ""}${_scopeId}>${ssrInterpolate(variant.name)}</option>`);
							});
							_push(`<!--]--></select></label><p${_scopeId}>${ssrInterpolate(item.quantity)} шт. · ${ssrInterpolate(item.variant.sku)}</p></div><div class="discounted-price"${_scopeId}>`);
							if (item.discount_total) _push(`<span class="discount-label"${_scopeId}>${ssrInterpolate(item.discount_percentage ? `-${item.discount_percentage}%` : "Знижка за кількість")}</span>`);
							else _push(`<!---->`);
							if (item.discount_total) _push(`<del${_scopeId}>${ssrInterpolate((item.original_total / 100).toLocaleString("uk-UA"))} ₴</del>`);
							else _push(`<!---->`);
							_push(`<b${_scopeId}>${ssrInterpolate((item.total / 100).toLocaleString("uk-UA"))} ₴</b></div><button class="link"${_scopeId}>Видалити</button></article>`);
						});
						_push(`<!--]-->`);
						if (__props.items.length && discountTotal()) _push(`<div class="cart-discount-total"${_scopeId}><span${_scopeId}>Ваша знижка</span><b${_scopeId}>− ${ssrInterpolate((discountTotal() / 100).toLocaleString("uk-UA"))} ₴</b></div>`);
						else _push(`<!---->`);
						if (__props.items.length) _push(`<div class="total"${_scopeId}><span${_scopeId}>Разом</span><b${_scopeId}>${ssrInterpolate((__props.total / 100).toLocaleString("uk-UA"))} ₴</b></div>`);
						else _push(`<!---->`);
						if (__props.items.length) _push(ssrRenderComponent(unref(Link), {
							href: "/checkout",
							class: "button"
						}, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) _push(`Оформити замовлення`);
								else return [createTextVNode("Оформити замовлення")];
							}),
							_: 1
						}, _parent, _scopeId));
						else _push(`<!---->`);
						_push(`</section>`);
					} else return [createVNode("section", { class: "narrow cart-page" }, [
						createVNode("h1", null, "Кошик"),
						!__props.items.length ? (openBlock(), createBlock("div", {
							key: 0,
							class: "empty"
						}, [createTextVNode("Кошик поки порожній. "), createVNode(unref(Link), { href: "/" }, {
							default: withCtx(() => [createTextVNode("До каталогу")]),
							_: 1
						})])) : createCommentVNode("", true),
						(openBlock(true), createBlock(Fragment, null, renderList(__props.items, (item) => {
							return openBlock(), createBlock("article", {
								key: item.variant.id,
								class: "cart-row"
							}, [
								createVNode("img", {
									src: itemImage(item),
									alt: item.variant.product.name
								}, null, 8, ["src", "alt"]),
								createVNode("div", null, [
									createVNode("b", null, toDisplayString(item.variant.product.name), 1),
									createVNode("label", { class: "cart-size" }, [createTextVNode("Довжина"), createVNode("select", {
										value: item.variant.id,
										onChange: ($event) => setVariant(item, Number($event.target.value))
									}, [(openBlock(true), createBlock(Fragment, null, renderList(item.variant.product.variants, (variant) => {
										return openBlock(), createBlock("option", {
											key: variant.id,
											value: variant.id,
											disabled: !variant.is_active || variant.stock_on_hand <= variant.stock_reserved
										}, toDisplayString(variant.name), 9, ["value", "disabled"]);
									}), 128))], 40, ["value", "onChange"])]),
									createVNode("p", null, toDisplayString(item.quantity) + " шт. · " + toDisplayString(item.variant.sku), 1)
								]),
								createVNode("div", { class: "discounted-price" }, [
									item.discount_total ? (openBlock(), createBlock("span", {
										key: 0,
										class: "discount-label"
									}, toDisplayString(item.discount_percentage ? `-${item.discount_percentage}%` : "Знижка за кількість"), 1)) : createCommentVNode("", true),
									item.discount_total ? (openBlock(), createBlock("del", { key: 1 }, toDisplayString((item.original_total / 100).toLocaleString("uk-UA")) + " ₴", 1)) : createCommentVNode("", true),
									createVNode("b", null, toDisplayString((item.total / 100).toLocaleString("uk-UA")) + " ₴", 1)
								]),
								createVNode("button", {
									class: "link",
									onClick: ($event) => unref(router).delete(`/cart/${item.variant.id}`)
								}, "Видалити", 8, ["onClick"])
							]);
						}), 128)),
						__props.items.length && discountTotal() ? (openBlock(), createBlock("div", {
							key: 1,
							class: "cart-discount-total"
						}, [createVNode("span", null, "Ваша знижка"), createVNode("b", null, "− " + toDisplayString((discountTotal() / 100).toLocaleString("uk-UA")) + " ₴", 1)])) : createCommentVNode("", true),
						__props.items.length ? (openBlock(), createBlock("div", {
							key: 2,
							class: "total"
						}, [createVNode("span", null, "Разом"), createVNode("b", null, toDisplayString((__props.total / 100).toLocaleString("uk-UA")) + " ₴", 1)])) : createCommentVNode("", true),
						__props.items.length ? (openBlock(), createBlock(unref(Link), {
							key: 3,
							href: "/checkout",
							class: "button"
						}, {
							default: withCtx(() => [createTextVNode("Оформити замовлення")]),
							_: 1
						})) : createCommentVNode("", true)
					])];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/js/Pages/Cart.vue
var _sfc_setup = Cart_vue_vue_type_script_setup_true_lang_default.setup;
Cart_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Cart.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Cart_default = Cart_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { Cart_default as default };
