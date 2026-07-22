import { t as StoreLayout_default } from "./StoreLayout-CSgOPgrw.js";
import { Fragment, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, openBlock, renderList, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderAttr, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
//#region resources/js/Pages/Cart.vue?vue&type=script&setup=true&lang.ts
var Cart_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Cart",
	__ssrInlineRender: true,
	props: {
		items: {},
		total: {}
	},
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Кошик" }, null, _parent));
			_push(ssrRenderComponent(StoreLayout_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<section class="narrow"${_scopeId}><h1${_scopeId}>Кошик</h1>`);
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
							_push(`<article class="cart-row"${_scopeId}><img${ssrRenderAttr("src", item.variant.product.image_url)}${_scopeId}><div${_scopeId}><b${_scopeId}>${ssrInterpolate(item.variant.product.name)}</b><p${_scopeId}>${ssrInterpolate(item.variant.name)} · ${ssrInterpolate(item.quantity)} шт.</p></div><b${_scopeId}>${ssrInterpolate((item.total / 100).toLocaleString("uk-UA"))} ₴</b><button class="link"${_scopeId}>Видалити</button></article>`);
						});
						_push(`<!--]-->`);
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
					} else return [createVNode("section", { class: "narrow" }, [
						createVNode("h1", null, "Кошик"),
						!__props.items.length ? (openBlock(), createBlock("div", {
							key: 0,
							class: "empty"
						}, [createTextVNode("Кошик поки порожній. "), createVNode(unref(Link), { href: "/" }, {
							default: withCtx(() => [createTextVNode("До каталогу")]),
							_: 1
						})])) : createCommentVNode("", true),
						(openBlock(true), createBlock(Fragment, null, renderList(__props.items, (item) => {
							return openBlock(), createBlock("article", { class: "cart-row" }, [
								createVNode("img", { src: item.variant.product.image_url }, null, 8, ["src"]),
								createVNode("div", null, [createVNode("b", null, toDisplayString(item.variant.product.name), 1), createVNode("p", null, toDisplayString(item.variant.name) + " · " + toDisplayString(item.quantity) + " шт.", 1)]),
								createVNode("b", null, toDisplayString((item.total / 100).toLocaleString("uk-UA")) + " ₴", 1),
								createVNode("button", {
									class: "link",
									onClick: ($event) => unref(router).delete(`/cart/${item.variant.id}`)
								}, "Видалити", 8, ["onClick"])
							]);
						}), 256)),
						__props.items.length ? (openBlock(), createBlock("div", {
							key: 1,
							class: "total"
						}, [createVNode("span", null, "Разом"), createVNode("b", null, toDisplayString((__props.total / 100).toLocaleString("uk-UA")) + " ₴", 1)])) : createCommentVNode("", true),
						__props.items.length ? (openBlock(), createBlock(unref(Link), {
							key: 2,
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
