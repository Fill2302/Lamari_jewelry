import { createTextVNode, defineComponent, ref, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderSlot } from "vue/server-renderer";
//#region resources/js/Layouts/StoreLayout.vue?vue&type=script&setup=true&lang.ts
var StoreLayout_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "StoreLayout",
	__ssrInlineRender: true,
	setup(__props) {
		const page = usePage();
		const menuOpen = ref(false);
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[--><div class="ticker">БЕЗКОШТОВНЕ ПАКУВАННЯ КОЖНОГО ЗАМОВЛЕННЯ · MADE IN UKRAINE</div><header><button class="menu-trigger">Каталог</button>`);
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
			_push(`<nav><a href="https://www.instagram.com/lamari.jewelry/" target="_blank">Instagram</a>`);
			_push(ssrRenderComponent(unref(Link), { href: "/cart" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Кошик (${ssrInterpolate(unref(page).props.cartCount)})`);
					else return [createTextVNode("Кошик (" + toDisplayString(unref(page).props.cartCount) + ")", 1)];
				}),
				_: 1
			}, _parent));
			_push(`</nav></header><div class="${ssrRenderClass([{ open: menuOpen.value }, "catalog-drawer"])}"><button class="drawer-close">Закрити ×</button><div class="drawer-grid"><!--[-->`);
			ssrRenderList(unref(page).props.catalogMenu, (category) => {
				_push(`<section>`);
				_push(ssrRenderComponent(unref(Link), {
					href: `/categories/${category.slug}`,
					class: "drawer-title"
				}, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) _push(`${ssrInterpolate(category.name)}`);
						else return [createTextVNode(toDisplayString(category.name), 1)];
					}),
					_: 2
				}, _parent));
				_push(`<!--[-->`);
				ssrRenderList(category.children, (child) => {
					_push(ssrRenderComponent(unref(Link), {
						key: child.id,
						href: `/categories/${child.slug}`
					}, {
						default: withCtx((_, _push, _parent, _scopeId) => {
							if (_push) _push(`${ssrInterpolate(child.name)}`);
							else return [createTextVNode(toDisplayString(child.name), 1)];
						}),
						_: 2
					}, _parent));
				});
				_push(`<!--]--></section>`);
			});
			_push(`<!--]--></div></div>`);
			if (unref(page).props.flash?.success) _push(`<div class="notice">${ssrInterpolate(unref(page).props.flash.success)}</div>`);
			else _push(`<!---->`);
			_push(`<main>`);
			ssrRenderSlot(_ctx.$slots, "default", {}, null, _push, _parent);
			_push(`</main><footer><b>LAMARI</b><span>Авторські прикраси ручної роботи.</span></footer><!--]-->`);
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
