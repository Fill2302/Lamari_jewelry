import { createTextVNode, defineComponent, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderComponent, ssrRenderSlot } from "vue/server-renderer";
//#region resources/js/Layouts/StoreLayout.vue?vue&type=script&setup=true&lang.ts
var StoreLayout_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "StoreLayout",
	__ssrInlineRender: true,
	setup(__props) {
		const page = usePage();
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[--><header>`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/",
				class: "brand"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`LAMARI`);
					else return [createTextVNode("LAMARI")];
				}),
				_: 1
			}, _parent));
			_push(`<nav>`);
			_push(ssrRenderComponent(unref(Link), { href: "/categories/rings" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Каблучки`);
					else return [createTextVNode("Каблучки")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/categories/earrings" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Сережки`);
					else return [createTextVNode("Сережки")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/cart" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Кошик (${ssrInterpolate(unref(page).props.cartCount)})`);
					else return [createTextVNode("Кошик (" + toDisplayString(unref(page).props.cartCount) + ")", 1)];
				}),
				_: 1
			}, _parent));
			_push(`</nav></header>`);
			if (unref(page).props.flash?.success) _push(`<div class="notice">${ssrInterpolate(unref(page).props.flash.success)}</div>`);
			else _push(`<!---->`);
			_push(`<main>`);
			ssrRenderSlot(_ctx.$slots, "default", {}, null, _push, _parent);
			_push(`</main><footer><b>LAMARI</b><span>Прикраси, що залишаються з тобою.</span></footer><!--]-->`);
		};
	}
});
//#endregion
//#region resources/js/Layouts/StoreLayout.vue
var _sfc_setup = StoreLayout_vue_vue_type_script_setup_true_lang_default.setup;
StoreLayout_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Layouts/StoreLayout.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var StoreLayout_default = StoreLayout_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { StoreLayout_default as t };
