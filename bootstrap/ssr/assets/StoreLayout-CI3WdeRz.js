import { createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, onMounted, onUnmounted, openBlock, ref, toDisplayString, unref, useSSRContext, watch, withCtx } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrRenderAttr, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderSlot, ssrRenderStyle } from "vue/server-renderer";
//#region resources/js/Layouts/StoreLayout.vue?vue&type=script&setup=true&lang.ts
var StoreLayout_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "StoreLayout",
	__ssrInlineRender: true,
	setup(__props) {
		const page = usePage();
		const menuOpen = ref(false);
		const searchOpen = ref(false);
		const searchQuery = ref("");
		ref(null);
		const expandedCategories = ref([]);
		const cartOpen = ref(Boolean(page.props.flash?.cartOpen));
		const favoriteCount = ref(0);
		const ticker = ref(null);
		const headerPinned = ref(false);
		const headerViewportOffset = ref(0);
		const updateHeaderPosition = () => {
			headerPinned.value = window.innerWidth <= 800 && window.scrollY >= (ticker.value?.offsetHeight || 0);
			headerViewportOffset.value = headerPinned.value ? Math.max(0, window.visualViewport?.offsetTop || 0) : 0;
		};
		const updateFavoriteCount = () => {
			try {
				favoriteCount.value = JSON.parse(localStorage.getItem("lamari-favorites") || "[]").length;
			} catch {
				favoriteCount.value = 0;
			}
		};
		onMounted(() => {
			updateFavoriteCount();
			updateHeaderPosition();
			window.addEventListener("storage", updateFavoriteCount);
			window.addEventListener("lamari-favorites", updateFavoriteCount);
			window.addEventListener("scroll", updateHeaderPosition, { passive: true });
			window.addEventListener("resize", updateHeaderPosition);
			window.visualViewport?.addEventListener("scroll", updateHeaderPosition);
			window.visualViewport?.addEventListener("resize", updateHeaderPosition);
		});
		onUnmounted(() => {
			window.removeEventListener("storage", updateFavoriteCount);
			window.removeEventListener("lamari-favorites", updateFavoriteCount);
			window.removeEventListener("scroll", updateHeaderPosition);
			window.removeEventListener("resize", updateHeaderPosition);
			window.visualViewport?.removeEventListener("scroll", updateHeaderPosition);
			window.visualViewport?.removeEventListener("resize", updateHeaderPosition);
		});
		watch(() => page.props.flash?.cartOpen, (value) => {
			if (value) cartOpen.value = true;
		});
		const money = (amount) => (amount / 100).toLocaleString("uk-UA");
		const cartDiscount = () => page.props.cartPreview.items.reduce((sum, item) => sum + (item.discount_total || 0), 0);
		const asset = (url) => !url ? "" : url.startsWith("http") ? url : `/storage/${url}`;
		const itemImage = (item) => asset(item.variant.product.media?.find((m) => m.type === "image")?.url || item.variant.product.image_url);
		const closeSearch = () => {
			searchOpen.value = false;
		};
		const handleEscape = (event) => {
			if (event.key !== "Escape") return;
			closeSearch();
			cartOpen.value = false;
			menuOpen.value = false;
		};
		onMounted(() => window.addEventListener("keydown", handleEscape));
		onUnmounted(() => window.removeEventListener("keydown", handleEscape));
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[--><div class="ticker" aria-label="Безкоштовне брендоване пакування"><div class="ticker-track"><!--[-->`);
			ssrRenderList(4, (index) => {
				_push(`<span>БЕЗКОШТОВНЕ БРЕНДОВАНЕ ПАКУВАННЯ</span>`);
			});
			_push(`<!--]--></div></div>`);
			if (headerPinned.value) _push(`<div class="site-header-placeholder" aria-hidden="true"></div>`);
			else _push(`<!---->`);
			_push(`<header class="${ssrRenderClass({ "is-pinned": headerPinned.value })}" style="${ssrRenderStyle(headerPinned.value ? { top: `${headerViewportOffset.value}px` } : void 0)}"><button class="menu-trigger desktop-menu-trigger">Каталог</button>`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/",
				class: "brand",
				"aria-label": "Lamari Jewelry"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<img${ssrRenderAttr("src", "/images/brand/lamari-logo-hq.png?v=1")} alt="Lamari Jewelry"${_scopeId}>`);
					else return [createVNode("img", {
						src: "/images/brand/lamari-logo-hq.png?v=1",
						alt: "Lamari Jewelry"
					})];
				}),
				_: 1
			}, _parent));
			_push(`<nav class="header-actions"><a href="https://www.instagram.com/lamari.jewelry/" target="_blank" aria-label="Instagram" class="header-icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><circle cx="17.5" cy="6.5" r="1"></circle></svg></a><button type="button" aria-label="Пошук у каталозі" class="header-icon"><svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="7"></circle><path d="m16 16 5 5"></path></svg></button>`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/catalog",
				"aria-label": `Обране: ${favoriteCount.value}`,
				class: "header-icon icon-with-count"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<svg viewBox="0 0 24 24"${_scopeId}><path d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"${_scopeId}></path></svg>`);
						if (favoriteCount.value) _push(`<small${_scopeId}>${ssrInterpolate(favoriteCount.value)}</small>`);
						else _push(`<!---->`);
					} else return [(openBlock(), createBlock("svg", { viewBox: "0 0 24 24" }, [createVNode("path", { d: "M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" })])), favoriteCount.value ? (openBlock(), createBlock("small", { key: 0 }, toDisplayString(favoriteCount.value), 1)) : createCommentVNode("", true)];
				}),
				_: 1
			}, _parent));
			_push(`<button class="cart-trigger header-icon icon-with-count" aria-label="Кошик"><svg viewBox="0 0 24 24"><path d="M5 8h14l1 13H4L5 8Z"></path><path d="M9 8V6a3 3 0 0 1 6 0v2"></path></svg>`);
			if (unref(page).props.cartCount) _push(`<small>${ssrInterpolate(unref(page).props.cartCount)}</small>`);
			else _push(`<!---->`);
			_push(`</button><button class="menu-trigger mobile-menu-trigger header-icon" aria-label="Відкрити меню"><svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"></path></svg></button></nav></header><div class="${ssrRenderClass([{ open: searchOpen.value }, "search-overlay"])}"><form class="site-search" role="search"><div class="site-search-head"><strong>Пошук</strong><button type="button" aria-label="Закрити пошук">×</button></div><label for="site-search-input">Назва товару або артикул</label><div class="site-search-field"><input id="site-search-input"${ssrRenderAttr("value", searchQuery.value)} type="search" placeholder="Наприклад, кольє або K402-43" autocomplete="off"><button type="submit"${ssrIncludeBooleanAttr(!searchQuery.value.trim()) ? " disabled" : ""} aria-label="Знайти"><svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="7"></circle><path d="m16 16 5 5"></path></svg></button></div></form></div><div class="${ssrRenderClass([{ open: menuOpen.value }, "catalog-backdrop"])}"></div><aside class="${ssrRenderClass([{ open: menuOpen.value }, "catalog-drawer"])}" aria-label="Каталог"><div class="catalog-drawer-head">`);
			_push(ssrRenderComponent(unref(Link), {
				href: "/",
				class: "catalog-drawer-brand",
				"aria-label": "Lamari Jewelry",
				onClick: ($event) => menuOpen.value = false
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<img${ssrRenderAttr("src", "/images/brand/lamari-logo-hq.png?v=1")} alt="Lamari Jewelry"${_scopeId}>`);
					else return [createVNode("img", {
						src: "/images/brand/lamari-logo-hq.png?v=1",
						alt: "Lamari Jewelry"
					})];
				}),
				_: 1
			}, _parent));
			_push(`<button class="drawer-close" aria-label="Закрити каталог">×</button></div><nav class="drawer-grid"><!--[-->`);
			ssrRenderList(unref(page).props.catalogMenu, (category) => {
				_push(`<section class="${ssrRenderClass({ expanded: expandedCategories.value.includes(category.id) })}"><div class="drawer-category-row">`);
				_push(ssrRenderComponent(unref(Link), {
					href: `/categories/${category.slug}`,
					class: ["drawer-title", { sale: category.slug.toLowerCase() === "sale" }],
					onClick: ($event) => menuOpen.value = false
				}, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) _push(`${ssrInterpolate(category.name)}`);
						else return [createTextVNode(toDisplayString(category.name), 1)];
					}),
					_: 2
				}, _parent));
				if (category.children?.length) _push(`<button class="drawer-expand"${ssrRenderAttr("aria-expanded", expandedCategories.value.includes(category.id))}${ssrRenderAttr("aria-label", `${expandedCategories.value.includes(category.id) ? "Закрити" : "Відкрити"} підкатегорії ${category.name}`)}>⌄</button>`);
				else _push(`<!---->`);
				_push(`</div>`);
				if (category.children?.length) {
					_push(`<div class="drawer-children"><!--[-->`);
					ssrRenderList(category.children, (child) => {
						_push(ssrRenderComponent(unref(Link), {
							key: child.id,
							href: `/categories/${child.slug}`,
							onClick: ($event) => menuOpen.value = false
						}, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) _push(`${ssrInterpolate(child.name)}`);
								else return [createTextVNode(toDisplayString(child.name), 1)];
							}),
							_: 2
						}, _parent));
					});
					_push(`<!--]--></div>`);
				} else _push(`<!---->`);
				_push(`</section>`);
			});
			_push(`<!--]--></nav></aside><div class="${ssrRenderClass([{ open: cartOpen.value }, "drawer-backdrop"])}"></div><aside class="${ssrRenderClass([{ open: cartOpen.value }, "cart-drawer"])}" aria-label="Кошик"${ssrRenderAttr("aria-hidden", !cartOpen.value)}><div class="cart-drawer-head"><h2>Кошик <small>${ssrInterpolate(unref(page).props.cartCount)}</small></h2><button type="button" class="cart-drawer-close" aria-label="Закрити кошик і продовжити покупки"><span>Закрити</span><b aria-hidden="true">×</b></button></div>`);
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
					_push(`<label class="cart-size">Довжина<select${ssrRenderAttr("value", item.variant.id)}><!--[-->`);
					ssrRenderList(item.variant.product.variants, (variant) => {
						_push(`<option${ssrRenderAttr("value", variant.id)}${ssrIncludeBooleanAttr(!variant.is_active || variant.stock_on_hand <= variant.stock_reserved) ? " disabled" : ""}>${ssrInterpolate(variant.name)}</option>`);
					});
					_push(`<!--]--></select></label><small>Артикул ${ssrInterpolate(item.variant.sku)}</small><div class="qty"><button>−</button><span>${ssrInterpolate(item.quantity)}</span><button${ssrIncludeBooleanAttr(item.quantity >= item.variant.stock_on_hand - item.variant.stock_reserved) ? " disabled" : ""}>+</button></div></div><div class="drawer-item-price"><div class="discounted-price">`);
					if (item.discount_total) _push(`<span class="discount-label">-${ssrInterpolate(item.variant.discount_percentage)}%</span>`);
					else _push(`<!---->`);
					if (item.discount_total) _push(`<del>${ssrInterpolate(money(item.original_total))} ₴</del>`);
					else _push(`<!---->`);
					_push(`<b>${ssrInterpolate(money(item.total))} ₴</b></div><button>Видалити</button></div></article>`);
				});
				_push(`<!--]--></div>`);
			}
			if (unref(page).props.cartPreview.items.length) {
				_push(`<div class="cart-drawer-footer"><p class="delivery-note">Вартість доставки буде розрахована під час оформлення.</p>`);
				if (cartDiscount()) _push(`<div class="drawer-discount"><span>Ваша знижка</span><b>− ${ssrInterpolate(money(cartDiscount()))} ₴</b></div>`);
				else _push(`<!---->`);
				_push(`<div class="drawer-subtotal"><span>Разом</span><b>${ssrInterpolate(money(unref(page).props.cartPreview.subtotal))} ₴</b></div>`);
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
			_push(`</main><footer class="site-footer"><div class="footer-inner"><section class="footer-contacts" aria-label="Контакти Lamari"><a class="footer-phone" href="tel:+380635463954">+38 063 546 39 54</a><a class="footer-email" href="mailto:lamari.jewelry.site@gmail.com">jewelrylamari@gmail.com</a><div class="footer-contact-row"><div class="footer-payments" aria-label="Способи оплати"><span class="payment-badge visa">VISA</span><span class="payment-badge mastercard" aria-label="Mastercard"><i></i><i></i></span><span class="payment-badge paw" aria-label="Оплата при отриманні">●</span></div><div class="footer-socials"><a href="https://www.instagram.com/lamari_jewelry/" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><circle cx="17.5" cy="6.5" r="1"></circle></svg></a><a href="https://t.me/lamari_jewelry" target="_blank" rel="noopener" aria-label="Telegram"><svg viewBox="0 0 24 24"><path d="M21 4 3.8 10.6c-1.2.5-1.2 1.2-.2 1.5l4.4 1.4 1.7 5.2c.2.7.1 1 .8 1 .5 0 .8-.2 1-.4l2.4-2.3 5 3.7c.9.5 1.6.2 1.8-.9L23.8 5c.3-1.2-.5-1.8-1.5-1.4Z"></path><path d="m8 13.5 10.2-6.4"></path></svg></a></div></div></section><div class="footer-columns"><nav class="footer-column" aria-label="Каталог у футері"><h2>Каталог</h2>`);
			_push(ssrRenderComponent(unref(Link), { href: "/categories/necklaces" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Кольє`);
					else return [createTextVNode("Кольє")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/categories/chokers" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Чокери`);
					else return [createTextVNode("Чокери")];
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
			_push(ssrRenderComponent(unref(Link), { href: "/categories/chains" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Ланцюжки`);
					else return [createTextVNode("Ланцюжки")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/categories/bracelets" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Браслети`);
					else return [createTextVNode("Браслети")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/categories/anklets" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Анклети`);
					else return [createTextVNode("Анклети")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/categories/rings" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Каблучки`);
					else return [createTextVNode("Каблучки")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/categories/sets" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Комплекти`);
					else return [createTextVNode("Комплекти")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/categories/summer" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Літня колекція`);
					else return [createTextVNode("Літня колекція")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/catalog?q=сертифікат" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Сертифікати`);
					else return [createTextVNode("Сертифікати")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/categories/pins" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Булавки`);
					else return [createTextVNode("Булавки")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/catalog?q=пакування" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Пакування`);
					else return [createTextVNode("Пакування")];
				}),
				_: 1
			}, _parent));
			_push(`</nav><nav class="footer-column" aria-label="Інформація у футері"><h2>Інформація</h2>`);
			_push(ssrRenderComponent(unref(Link), { href: "/information/about" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Про бренд`);
					else return [createTextVNode("Про бренд")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/information/care" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Догляд за виробами`);
					else return [createTextVNode("Догляд за виробами")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/information/delivery" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Доставка і оплата`);
					else return [createTextVNode("Доставка і оплата")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/information/returns" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Повернення та обмін`);
					else return [createTextVNode("Повернення та обмін")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/information/contacts" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Контакти`);
					else return [createTextVNode("Контакти")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/information/cooperation" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Співпраця`);
					else return [createTextVNode("Співпраця")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), {
				class: "footer-spaced-link",
				href: "/information/offer"
			}, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Публічна оферта`);
					else return [createTextVNode("Публічна оферта")];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(unref(Link), { href: "/information/privacy" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`Політика обробки даних`);
					else return [createTextVNode("Політика обробки даних")];
				}),
				_: 1
			}, _parent));
			_push(`</nav></div></div></footer><!--]-->`);
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
