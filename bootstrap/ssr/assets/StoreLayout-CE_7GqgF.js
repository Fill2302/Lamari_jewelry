import { createTextVNode, defineComponent, ref, toDisplayString, unref, useSSRContext, watch, withCtx } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrRenderAttr, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderSlot } from "vue/server-renderer";
//#region resources/js/Layouts/StoreLayout.vue?vue&type=script&setup=true&lang.ts
var StoreLayout_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "StoreLayout",
	__ssrInlineRender: true,
	setup(__props) {
		const page = usePage();
		const menuOpen = ref(false);
		const cartOpen = ref(Boolean(page.props.flash?.cartOpen));
		watch(() => page.props.flash?.cartOpen, (value) => {
			if (value) cartOpen.value = true;
		});
		const money = (amount) => (amount / 100).toLocaleString("uk-UA");
		const asset = (url) => !url ? "" : url.startsWith("http") ? url : `/storage/${url}`;
		const itemImage = (item) => asset(item.variant.product.media?.find((m) => m.type === "image")?.url || item.variant.product.image_url);
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
			_push(`<nav><a href="https://www.instagram.com/lamari.jewelry/" target="_blank">Instagram</a><button class="cart-trigger">Кошик (${ssrInterpolate(unref(page).props.cartCount)})</button></nav></header><div class="${ssrRenderClass([{ open: menuOpen.value }, "catalog-drawer"])}"><button class="drawer-close">Закрити ×</button><div class="drawer-grid"><!--[-->`);
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
			_push(`<!--]--></div></div><div class="${ssrRenderClass([{ open: cartOpen.value }, "drawer-backdrop"])}"></div><aside class="${ssrRenderClass([{ open: cartOpen.value }, "cart-drawer"])}" aria-label="Кошик"><div class="cart-drawer-head"><h2>Кошик <small>${ssrInterpolate(unref(page).props.cartCount)}</small></h2><button>Закрити ×</button></div>`);
			if (!unref(page).props.cartPreview.items.length) _push(`<div class="cart-drawer-empty"><p>У кошику ще немає прикрас.</p><button>Перейти до каталогу</button></div>`);
			else {
				_push(`<div class="cart-drawer-body"><!--[-->`);
				ssrRenderList(unref(page).props.cartPreview.items, (item) => {
					_push(`<article class="drawer-item"><img${ssrRenderAttr("src", itemImage(item))}${ssrRenderAttr("alt", item.variant.product.name)}><div class="drawer-item-info">`);
					_push(ssrRenderComponent(unref(Link), {
						href: `/products/${item.variant.product.slug}`,
						onClick: ($event) => cartOpen.value = false
					}, {
						default: withCtx((_, _push, _parent, _scopeId) => {
							if (_push) _push(`${ssrInterpolate(item.variant.product.name)}`);
							else return [createTextVNode(toDisplayString(item.variant.product.name), 1)];
						}),
						_: 2
					}, _parent));
					_push(`<small>${ssrInterpolate(item.variant.name)} · ${ssrInterpolate(item.variant.sku)}</small><div class="qty"><button>−</button><span>${ssrInterpolate(item.quantity)}</span><button${ssrIncludeBooleanAttr(item.quantity >= item.variant.stock_on_hand - item.variant.stock_reserved) ? " disabled" : ""}>+</button></div></div><div class="drawer-item-price"><b>${ssrInterpolate(money(item.total))} ₴</b><button>Видалити</button></div></article>`);
				});
				_push(`<!--]--></div>`);
			}
			if (unref(page).props.cartPreview.items.length) {
				_push(`<div class="cart-drawer-footer"><p class="delivery-note">Вартість доставки буде розрахована під час оформлення.</p><div class="drawer-subtotal"><span>Разом</span><b>${ssrInterpolate(money(unref(page).props.cartPreview.subtotal))} ₴</b></div>`);
				_push(ssrRenderComponent(unref(Link), {
					href: "/checkout",
					class: "button drawer-checkout",
					onClick: ($event) => cartOpen.value = false
				}, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) _push(`Оформити замовлення`);
						else return [createTextVNode("Оформити замовлення")];
					}),
					_: 1
				}, _parent));
				_push(ssrRenderComponent(unref(Link), {
					href: "/cart",
					class: "view-cart",
					onClick: ($event) => cartOpen.value = false
				}, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) _push(`Переглянути кошик`);
						else return [createTextVNode("Переглянути кошик")];
					}),
					_: 1
				}, _parent));
				_push(`</div>`);
			} else _push(`<!---->`);
			_push(`</aside>`);
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
