import { t as StoreLayout_default } from "./StoreLayout-DpbhNzPq.js";
import { Fragment, computed, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, nextTick, onMounted, onUnmounted, openBlock, ref, renderList, resolveDynamicComponent, toDisplayString, unref, useSSRContext, withCtx, withModifiers } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrRenderAttr, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderStyle, ssrRenderVNode } from "vue/server-renderer";
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
		const carouselMedia = computed(() => media.value.length > 1 ? [
			{
				...media.value[media.value.length - 1],
				cloneKey: "last-clone"
			},
			...media.value.map((item, index) => ({
				...item,
				cloneKey: `media-${item.id || item.url}-${index}`
			})),
			{
				...media.value[0],
				cloneKey: "first-clone"
			}
		] : media.value.map((item) => ({
			...item,
			cloneKey: `media-${item.id || item.url}`
		})));
		const gallery = ref(null);
		const buyButton = ref(null);
		const showStickyBuy = ref(false);
		const isFavorite = ref(false);
		const toggleFavorite = () => {
			const favorites = JSON.parse(localStorage.getItem("lamari-favorites") || "[]");
			const next = favorites.includes(p.product.id) ? favorites.filter((id) => id !== p.product.id) : [...favorites, p.product.id];
			localStorage.setItem("lamari-favorites", JSON.stringify(next));
			isFavorite.value = next.includes(p.product.id);
			window.dispatchEvent(new Event("lamari-favorites"));
		};
		const activeMedia = ref(0);
		const zoomKey = ref(null);
		const zoomScale = ref(1);
		const zoomX = ref(0);
		const zoomY = ref(0);
		let pinchStartDistance = 0;
		let pinchStartScale = 1;
		let panStartX = 0;
		let panStartY = 0;
		let panOriginX = 0;
		let panOriginY = 0;
		const touchDistance = (touches) => Math.hypot(touches[0].clientX - touches[1].clientX, touches[0].clientY - touches[1].clientY);
		const resetZoom = () => {
			zoomKey.value = null;
			zoomScale.value = 1;
			zoomX.value = 0;
			zoomY.value = 0;
			pinchStartDistance = 0;
		};
		const imageTransform = (key) => key === zoomKey.value ? { transform: `translate3d(${zoomX.value}px, ${zoomY.value}px, 0) scale(${zoomScale.value})` } : void 0;
		const startImageGesture = (event, key) => {
			if (event.touches.length === 2) {
				zoomKey.value = key;
				pinchStartDistance = touchDistance(event.touches);
				pinchStartScale = zoomScale.value;
			} else if (event.touches.length === 1 && zoomKey.value === key && zoomScale.value > 1) {
				panStartX = event.touches[0].clientX;
				panStartY = event.touches[0].clientY;
				panOriginX = zoomX.value;
				panOriginY = zoomY.value;
			}
		};
		const moveImageGesture = (event, key) => {
			if (zoomKey.value !== key) return;
			if (event.touches.length === 2) {
				event.preventDefault();
				zoomScale.value = Math.min(4, Math.max(1, pinchStartScale * touchDistance(event.touches) / pinchStartDistance));
				if (zoomScale.value === 1) {
					zoomX.value = 0;
					zoomY.value = 0;
				}
			} else if (event.touches.length === 1 && zoomScale.value > 1) {
				event.preventDefault();
				const limitX = gallery.value?.clientWidth ? gallery.value.clientWidth * (zoomScale.value - 1) / 2 : 0;
				const limitY = gallery.value?.clientHeight ? gallery.value.clientHeight * (zoomScale.value - 1) / 2 : 0;
				zoomX.value = Math.min(limitX, Math.max(-limitX, panOriginX + event.touches[0].clientX - panStartX));
				zoomY.value = Math.min(limitY, Math.max(-limitY, panOriginY + event.touches[0].clientY - panStartY));
			}
		};
		const endImageGesture = (event) => {
			if (event.touches.length === 0 && zoomScale.value <= 1.02) resetZoom();
			if (event.touches.length === 1 && zoomScale.value > 1) {
				panStartX = event.touches[0].clientX;
				panStartY = event.touches[0].clientY;
				panOriginX = zoomX.value;
				panOriginY = zoomY.value;
			}
		};
		let scrollTimer;
		const scrollToPhysical = (index, behavior = "auto") => {
			const element = gallery.value;
			if (!element) return;
			element.scrollTo({
				left: index * element.clientWidth,
				behavior
			});
		};
		const goToMedia = (index) => {
			resetZoom();
			const count = media.value.length;
			if (count < 2) return;
			if (index < 0) return scrollToPhysical(0, "smooth");
			if (index >= count) return scrollToPhysical(count + 1, "smooth");
			scrollToPhysical(index + 1, "smooth");
		};
		const updateActiveMedia = () => {
			const element = gallery.value;
			if (!element || media.value.length < 2) return;
			const physicalIndex = Math.round(element.scrollLeft / element.clientWidth);
			if (physicalIndex !== activeMedia.value + 1 && zoomScale.value === 1) resetZoom();
			activeMedia.value = physicalIndex === 0 ? media.value.length - 1 : physicalIndex === media.value.length + 1 ? 0 : physicalIndex - 1;
			clearTimeout(scrollTimer);
			scrollTimer = setTimeout(() => {
				const settledIndex = Math.round(element.scrollLeft / element.clientWidth);
				if (settledIndex === 0) scrollToPhysical(media.value.length);
				if (settledIndex === media.value.length + 1) scrollToPhysical(1);
			}, 80);
		};
		const updateStickyBuy = () => {
			showStickyBuy.value = Boolean(buyButton.value && buyButton.value.getBoundingClientRect().bottom < 0);
		};
		onMounted(() => {
			try {
				isFavorite.value = JSON.parse(localStorage.getItem("lamari-favorites") || "[]").includes(p.product.id);
			} catch {
				isFavorite.value = false;
			}
			nextTick(() => {
				if (media.value.length > 1) scrollToPhysical(1);
				updateStickyBuy();
			});
			window.addEventListener("scroll", updateStickyBuy, { passive: true });
			window.addEventListener("resize", updateStickyBuy);
		});
		onUnmounted(() => {
			window.removeEventListener("scroll", updateStickyBuy);
			window.removeEventListener("resize", updateStickyBuy);
		});
		const selectedVariant = computed(() => p.product.variants.find((v) => v.id === selected.value));
		const compareAtPrice = computed(() => Number(p.product.compare_at_price_amount || 0));
		const currentPrice = computed(() => Number(selectedVariant.value?.price_amount || 0));
		const discountLabel = computed(() => {
			if (!compareAtPrice.value || !currentPrice.value || compareAtPrice.value <= currentPrice.value) return "";
			return p.product.catalog_badges?.find((badge) => badge.type === "sale")?.label || `-${Math.round((1 - currentPrice.value / compareAtPrice.value) * 100)}%`;
		});
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
						_push(` / ${ssrInterpolate(__props.product.name)}</div><section class="product-lace"${_scopeId}><div class="product-gallery"${_scopeId}>`);
						if (discountLabel.value) _push(`<span class="product-sale-badge"${_scopeId}>${ssrInterpolate(discountLabel.value)}</span>`);
						else _push(`<!---->`);
						_push(`<div class="${ssrRenderClass([{ "is-image-zoomed": zoomScale.value > 1 }, "media-carousel"])}"${_scopeId}><!--[-->`);
						ssrRenderList(carouselMedia.value, (item) => {
							_push(`<figure${_scopeId}>`);
							if (item.type === "image") _push(`<img${ssrRenderAttr("src", asset(item.url))}${ssrRenderAttr("alt", item.alt || __props.product.name)} style="${ssrRenderStyle(imageTransform(item.cloneKey))}" loading="lazy"${_scopeId}>`);
							else _push(`<video${ssrRenderAttr("src", asset(item.url))}${ssrRenderAttr("poster", asset(item.poster_url))} muted autoplay loop playsinline disablepictureinpicture disableremoteplayback tabindex="-1" preload="auto"${_scopeId}>Ваш браузер не підтримує відео.</video>`);
							_push(`</figure>`);
						});
						_push(`<!--]--></div>`);
						if (media.value.length > 1) {
							_push(`<!--[--><button class="gallery-arrow gallery-prev" aria-label="Попереднє медіа"${_scopeId}>←</button><button class="gallery-arrow gallery-next" aria-label="Наступне медіа"${_scopeId}>→</button><div class="gallery-dots"${_scopeId}><!--[-->`);
							ssrRenderList(media.value, (_, index) => {
								_push(`<button class="${ssrRenderClass({ active: activeMedia.value === index })}"${ssrRenderAttr("aria-label", `Медіа ${index + 1}`)}${_scopeId}></button>`);
							});
							_push(`<!--]--></div><!--]-->`);
						} else _push(`<!---->`);
						_push(`</div><aside class="buy-panel"${_scopeId}><button class="${ssrRenderClass([{ active: isFavorite.value }, "product-favorite"])}"${ssrRenderAttr("aria-label", isFavorite.value ? "Видалити з обраного" : "Додати в обране")}${_scopeId}><svg viewBox="0 0 24 24"${_scopeId}><path d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"${_scopeId}></path></svg></button><h1${_scopeId}>${ssrInterpolate(__props.product.name)}</h1><p class="sku"${_scopeId}>Артикул ${ssrInterpolate(selectedVariant.value?.sku)} · <span class="in-stock"${_scopeId}>В наявності</span></p><p class="${ssrRenderClass([{ "product-sale-price": compareAtPrice.value }, "price"])}"${_scopeId}>`);
						if (compareAtPrice.value) _push(`<del${_scopeId}>${ssrInterpolate((compareAtPrice.value / 100).toLocaleString("uk-UA"))} ₴</del>`);
						else _push(`<!---->`);
						_push(`<span${_scopeId}>${ssrInterpolate((currentPrice.value / 100).toLocaleString("uk-UA"))} ₴</span></p><label${_scopeId}>Оберіть розмір <div class="variant-pills"${_scopeId}><!--[-->`);
						ssrRenderList(__props.product.variants, (v) => {
							_push(`<button class="${ssrRenderClass({ active: selected.value === v.id })}"${_scopeId}>${ssrInterpolate(v.name)}</button>`);
						});
						_push(`<!--]--></div></label><button class="button buy"${ssrIncludeBooleanAttr(unref(form).processing || !selected.value) ? " disabled" : ""}${_scopeId}>Додати в кошик</button><div class="product-benefits"${_scopeId}><span${_scopeId}>Безкоштовне брендоване пакування</span><span${_scopeId}>Відправлення 1–2 робочі дні</span></div><details open${_scopeId}><summary${_scopeId}>Характеристики</summary><dl${_scopeId}><!--[-->`);
						ssrRenderList(__props.product.characteristics, (value, key) => {
							_push(`<!--[--><dt${_scopeId}>${ssrInterpolate(key)}</dt><dd${_scopeId}>${ssrInterpolate(value)}</dd><!--]-->`);
						});
						_push(`<!--]--><dt${_scopeId}>Матеріал</dt><dd${_scopeId}>${ssrInterpolate(__props.product.material)}</dd></dl></details><details${_scopeId}><summary${_scopeId}>Опис товару</summary><p${_scopeId}>${ssrInterpolate(__props.product.description)}</p></details><details${_scopeId}><summary${_scopeId}>Упаковка</summary><p${_scopeId}>${ssrInterpolate(__props.product.packaging_text)}</p></details><details${_scopeId}><summary${_scopeId}>Догляд</summary><p${_scopeId}>${ssrInterpolate(__props.product.care_text)}</p></details><details${_scopeId}><summary${_scopeId}>Доставка та оплата</summary><p${_scopeId}>${ssrInterpolate(__props.product.delivery_payment_text || "Доставка Україною та за кордон. Точний спосіб і вартість будуть доступні під час оформлення.")}</p></details></aside></section>`);
						if (showStickyBuy.value) {
							_push(`<div class="sticky-buy-bar"${_scopeId}><strong class="sticky-product-price"${_scopeId}>`);
							if (compareAtPrice.value) _push(`<del${_scopeId}>${ssrInterpolate((compareAtPrice.value / 100).toLocaleString("uk-UA"))} ₴</del>`);
							else _push(`<!---->`);
							_push(`<span${_scopeId}>${ssrInterpolate((currentPrice.value / 100).toLocaleString("uk-UA"))} ₴</span></strong><button class="button"${ssrIncludeBooleanAttr(unref(form).processing || !selected.value) ? " disabled" : ""}${_scopeId}>Додати в кошик</button></div>`);
						} else _push(`<!---->`);
					} else return [
						createVNode("div", { class: "breadcrumbs" }, [
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
						]),
						createVNode("section", { class: "product-lace" }, [createVNode("div", { class: "product-gallery" }, [
							discountLabel.value ? (openBlock(), createBlock("span", {
								key: 0,
								class: "product-sale-badge"
							}, toDisplayString(discountLabel.value), 1)) : createCommentVNode("", true),
							createVNode("div", {
								ref_key: "gallery",
								ref: gallery,
								class: ["media-carousel", { "is-image-zoomed": zoomScale.value > 1 }],
								onScrollPassive: updateActiveMedia
							}, [(openBlock(true), createBlock(Fragment, null, renderList(carouselMedia.value, (item) => {
								return openBlock(), createBlock("figure", {
									key: item.cloneKey,
									onTouchstart: ($event) => item.type === "image" && startImageGesture($event, item.cloneKey),
									onTouchmove: ($event) => item.type === "image" && moveImageGesture($event, item.cloneKey),
									onTouchend: ($event) => item.type === "image" && endImageGesture($event),
									onTouchcancel: resetZoom
								}, [item.type === "image" ? (openBlock(), createBlock("img", {
									key: 0,
									src: asset(item.url),
									alt: item.alt || __props.product.name,
									style: imageTransform(item.cloneKey),
									loading: "lazy"
								}, null, 12, ["src", "alt"])) : (openBlock(), createBlock("video", {
									key: 1,
									src: asset(item.url),
									poster: asset(item.poster_url),
									muted: "",
									autoplay: "",
									loop: "",
									playsinline: "",
									disablepictureinpicture: "",
									disableremoteplayback: "",
									tabindex: "-1",
									preload: "auto",
									onContextmenu: withModifiers(() => {}, ["prevent"])
								}, "Ваш браузер не підтримує відео.", 40, [
									"src",
									"poster",
									"onContextmenu"
								]))], 40, [
									"onTouchstart",
									"onTouchmove",
									"onTouchend"
								]);
							}), 128))], 34),
							media.value.length > 1 ? (openBlock(), createBlock(Fragment, { key: 1 }, [
								createVNode("button", {
									class: "gallery-arrow gallery-prev",
									"aria-label": "Попереднє медіа",
									onClick: ($event) => goToMedia(activeMedia.value - 1)
								}, "←", 8, ["onClick"]),
								createVNode("button", {
									class: "gallery-arrow gallery-next",
									"aria-label": "Наступне медіа",
									onClick: ($event) => goToMedia(activeMedia.value + 1)
								}, "→", 8, ["onClick"]),
								createVNode("div", { class: "gallery-dots" }, [(openBlock(true), createBlock(Fragment, null, renderList(media.value, (_, index) => {
									return openBlock(), createBlock("button", {
										key: index,
										class: { active: activeMedia.value === index },
										"aria-label": `Медіа ${index + 1}`,
										onClick: ($event) => goToMedia(index)
									}, null, 10, ["aria-label", "onClick"]);
								}), 128))])
							], 64)) : createCommentVNode("", true)
						]), createVNode("aside", { class: "buy-panel" }, [
							createVNode("button", {
								class: ["product-favorite", { active: isFavorite.value }],
								"aria-label": isFavorite.value ? "Видалити з обраного" : "Додати в обране",
								onClick: toggleFavorite
							}, [(openBlock(), createBlock("svg", { viewBox: "0 0 24 24" }, [createVNode("path", { d: "M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" })]))], 10, ["aria-label"]),
							createVNode("h1", null, toDisplayString(__props.product.name), 1),
							createVNode("p", { class: "sku" }, [createTextVNode("Артикул " + toDisplayString(selectedVariant.value?.sku) + " · ", 1), createVNode("span", { class: "in-stock" }, "В наявності")]),
							createVNode("p", { class: ["price", { "product-sale-price": compareAtPrice.value }] }, [compareAtPrice.value ? (openBlock(), createBlock("del", { key: 0 }, toDisplayString((compareAtPrice.value / 100).toLocaleString("uk-UA")) + " ₴", 1)) : createCommentVNode("", true), createVNode("span", null, toDisplayString((currentPrice.value / 100).toLocaleString("uk-UA")) + " ₴", 1)], 2),
							createVNode("label", null, [createTextVNode("Оберіть розмір "), createVNode("div", { class: "variant-pills" }, [(openBlock(true), createBlock(Fragment, null, renderList(__props.product.variants, (v) => {
								return openBlock(), createBlock("button", {
									key: v.id,
									class: { active: selected.value === v.id },
									onClick: ($event) => selected.value = v.id
								}, toDisplayString(v.name), 11, ["onClick"]);
							}), 128))])]),
							createVNode("button", {
								ref_key: "buyButton",
								ref: buyButton,
								class: "button buy",
								onClick: add,
								disabled: unref(form).processing || !selected.value
							}, "Додати в кошик", 8, ["disabled"]),
							createVNode("div", { class: "product-benefits" }, [createVNode("span", null, "Безкоштовне брендоване пакування"), createVNode("span", null, "Відправлення 1–2 робочі дні")]),
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
							createVNode("details", null, [createVNode("summary", null, "Доставка та оплата"), createVNode("p", null, toDisplayString(__props.product.delivery_payment_text || "Доставка Україною та за кордон. Точний спосіб і вартість будуть доступні під час оформлення."), 1)])
						])]),
						showStickyBuy.value ? (openBlock(), createBlock("div", {
							key: 0,
							class: "sticky-buy-bar"
						}, [createVNode("strong", { class: "sticky-product-price" }, [compareAtPrice.value ? (openBlock(), createBlock("del", { key: 0 }, toDisplayString((compareAtPrice.value / 100).toLocaleString("uk-UA")) + " ₴", 1)) : createCommentVNode("", true), createVNode("span", null, toDisplayString((currentPrice.value / 100).toLocaleString("uk-UA")) + " ₴", 1)]), createVNode("button", {
							class: "button",
							onClick: add,
							disabled: unref(form).processing || !selected.value
						}, "Додати в кошик", 8, ["disabled"])])) : createCommentVNode("", true)
					];
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
