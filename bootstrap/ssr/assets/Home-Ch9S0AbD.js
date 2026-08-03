import { t as StoreLayout_default } from "./StoreLayout-CqSO763L.js";
import { Fragment, computed, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, openBlock, ref, renderList, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderAttr, ssrRenderClass, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
//#region resources/js/Pages/Home.vue?vue&type=script&setup=true&lang.ts
var Home_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Home",
	__ssrInlineRender: true,
	props: {
		categories: {},
		newProducts: {},
		hitProducts: {},
		homepage: {}
	},
	setup(__props) {
		const props = __props;
		const openFaq = ref(null);
		const asset = (url) => url?.startsWith("http") ? url : `/storage/${url}`;
		const pageAsset = (url) => !url ? "" : url.startsWith("http") || url.startsWith("/") ? url : `/storage/${url}`;
		const productImage = (product) => asset(product.media?.find((item) => item.type === "image")?.url || product.image_url);
		const price = (product) => product.variants?.[0]?.effective_price_amount ?? product.variants?.[0]?.price_amount ?? 0;
		const originalPrice = (product) => product.variants?.[0]?.discount_percentage ? product.variants[0].original_price_amount : product.compare_at_price_amount;
		const fallbackCategoryImages = {
			necklaces: "/images/home/categories/necklaces.jpg",
			chokers: "/images/home/categories/chokers.jpg",
			earrings: "/images/home/categories/earrings.jpg",
			chains: "/images/home/categories/chains.jpg",
			bracelets: "/images/home/categories/bracelets.jpg",
			anklets: "/images/home/categories/anklets.jpeg",
			rings: "/images/home/categories/rings.jpg",
			sets: "/images/home/categories/sets.jpg",
			summer: "/images/home/categories/summer.jpg",
			pins: "/images/home/categories/pins.jpg"
		};
		const categoryCards = computed(() => props.categories.map((item) => ({
			...item,
			image: item.image_url ? pageAsset(item.image_url) : fallbackCategoryImages[item.slug]
		})).filter((item) => item.image));
		const faqs = computed(() => (props.homepage?.faq_items || []).map((item) => ({
			q: item.question,
			a: item.answer
		})));
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<title${_scopeId}>Авторські прикраси ручної роботи</title><meta name="description" content="Lamari Jewelry — авторські прикраси ручної роботи: кольє, чокери, сережки, браслети та каблучки."${_scopeId}><link rel="canonical" href="http://localhost"${_scopeId}><meta property="og:title" content="Lamari Jewelry"${_scopeId}>`);
					else return [
						createVNode("title", null, "Авторські прикраси ручної роботи"),
						createVNode("meta", {
							name: "description",
							content: "Lamari Jewelry — авторські прикраси ручної роботи: кольє, чокери, сережки, браслети та каблучки."
						}),
						createVNode("link", {
							rel: "canonical",
							href: "http://localhost"
						}),
						createVNode("meta", {
							property: "og:title",
							content: "Lamari Jewelry"
						})
					];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(StoreLayout_default, { "home-overlay": "" }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(ssrRenderComponent(unref(Link), {
							href: __props.homepage?.hero_link || "/catalog",
							class: "home-campaign",
							"aria-label": "Перейти до каталогу всіх товарів"
						}, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) _push(`<img class="home-campaign-desktop"${ssrRenderAttr("src", pageAsset(__props.homepage?.desktop_hero_image || "/images/home/summer-collection-desktop.jpg?v=2"))} alt="Літня колекція Lamari Jewelry" loading="eager" fetchpriority="high"${_scopeId}><video${ssrRenderAttr("src", pageAsset(__props.homepage?.mobile_hero_video || "/images/home/hero-video.mp4"))}${ssrRenderAttr("poster", pageAsset(__props.homepage?.mobile_hero_poster || "/images/home/hero-video-first-frame.webp"))} autoplay muted loop playsinline preload="metadata" aria-hidden="true"${_scopeId}></video>`);
								else return [createVNode("img", {
									class: "home-campaign-desktop",
									src: pageAsset(__props.homepage?.desktop_hero_image || "/images/home/summer-collection-desktop.jpg?v=2"),
									alt: "Літня колекція Lamari Jewelry",
									loading: "eager",
									fetchpriority: "high"
								}, null, 8, ["src"]), createVNode("video", {
									src: pageAsset(__props.homepage?.mobile_hero_video || "/images/home/hero-video.mp4"),
									poster: pageAsset(__props.homepage?.mobile_hero_poster || "/images/home/hero-video-first-frame.webp"),
									autoplay: "",
									muted: "",
									loop: "",
									playsinline: "",
									preload: "metadata",
									"aria-hidden": "true"
								}, null, 8, ["src", "poster"])];
							}),
							_: 1
						}, _parent, _scopeId));
						_push(`<div class="ticker desktop-home-ticker" aria-label="Безкоштовне брендоване пакування"${_scopeId}><div class="ticker-track"${_scopeId}><!--[-->`);
						ssrRenderList(4, (index) => {
							_push(`<span${_scopeId}>${ssrInterpolate(__props.homepage?.ticker_text || "БЕЗКОШТОВНЕ БРЕНДОВАНЕ ПАКУВАННЯ")}</span>`);
						});
						_push(`<!--]--></div></div>`);
						if (__props.homepage?.show_new_products !== false && __props.newProducts.length) {
							_push(`<section class="home-showcase"${_scopeId}><div class="home-section-heading"${_scopeId}><h2${_scopeId}>${ssrInterpolate(__props.homepage?.new_products_title || "Новинки")}</h2>`);
							_push(ssrRenderComponent(unref(Link), { href: "/catalog?sort=newest" }, {
								default: withCtx((_, _push, _parent, _scopeId) => {
									if (_push) _push(`Переглянути всі`);
									else return [createTextVNode("Переглянути всі")];
								}),
								_: 1
							}, _parent, _scopeId));
							_push(`</div><div class="home-products"${_scopeId}><!--[-->`);
							ssrRenderList(__props.newProducts, (product) => {
								_push(ssrRenderComponent(unref(Link), {
									key: product.id,
									href: `/products/${product.slug}`,
									class: "home-product-card"
								}, {
									default: withCtx((_, _push, _parent, _scopeId) => {
										if (_push) {
											_push(`<div class="home-product-image"${_scopeId}><img${ssrRenderAttr("src", productImage(product))}${ssrRenderAttr("alt", product.name)} loading="lazy"${_scopeId}><span${_scopeId}>NEW</span></div><h3${_scopeId}>${ssrInterpolate(product.name)}</h3><p${_scopeId}>`);
											if (originalPrice(product)) _push(`<del${_scopeId}>${ssrInterpolate((originalPrice(product) / 100).toLocaleString("uk-UA"))} ₴</del>`);
											else _push(`<!---->`);
											_push(`${ssrInterpolate((price(product) / 100).toLocaleString("uk-UA"))} ₴</p>`);
										} else return [
											createVNode("div", { class: "home-product-image" }, [createVNode("img", {
												src: productImage(product),
												alt: product.name,
												loading: "lazy"
											}, null, 8, ["src", "alt"]), createVNode("span", null, "NEW")]),
											createVNode("h3", null, toDisplayString(product.name), 1),
											createVNode("p", null, [originalPrice(product) ? (openBlock(), createBlock("del", { key: 0 }, toDisplayString((originalPrice(product) / 100).toLocaleString("uk-UA")) + " ₴", 1)) : createCommentVNode("", true), createTextVNode(toDisplayString((price(product) / 100).toLocaleString("uk-UA")) + " ₴", 1)])
										];
									}),
									_: 2
								}, _parent, _scopeId));
							});
							_push(`<!--]--></div></section>`);
						} else _push(`<!---->`);
						if (__props.homepage?.show_hit_products !== false && __props.hitProducts.length) {
							_push(`<section class="home-showcase"${_scopeId}><div class="home-section-heading"${_scopeId}><h2${_scopeId}>${ssrInterpolate(__props.homepage?.hit_products_title || "Хіти продажів")}</h2>`);
							_push(ssrRenderComponent(unref(Link), { href: "/catalog" }, {
								default: withCtx((_, _push, _parent, _scopeId) => {
									if (_push) _push(`Переглянути всі`);
									else return [createTextVNode("Переглянути всі")];
								}),
								_: 1
							}, _parent, _scopeId));
							_push(`</div><div class="home-products"${_scopeId}><!--[-->`);
							ssrRenderList(__props.hitProducts, (product) => {
								_push(ssrRenderComponent(unref(Link), {
									key: product.id,
									href: `/products/${product.slug}`,
									class: "home-product-card"
								}, {
									default: withCtx((_, _push, _parent, _scopeId) => {
										if (_push) {
											_push(`<div class="home-product-image"${_scopeId}><img${ssrRenderAttr("src", productImage(product))}${ssrRenderAttr("alt", product.name)} loading="lazy"${_scopeId}><span class="hit"${_scopeId}>ХІТ</span></div><h3${_scopeId}>${ssrInterpolate(product.name)}</h3><p${_scopeId}>`);
											if (originalPrice(product)) _push(`<del${_scopeId}>${ssrInterpolate((originalPrice(product) / 100).toLocaleString("uk-UA"))} ₴</del>`);
											else _push(`<!---->`);
											_push(`${ssrInterpolate((price(product) / 100).toLocaleString("uk-UA"))} ₴</p>`);
										} else return [
											createVNode("div", { class: "home-product-image" }, [createVNode("img", {
												src: productImage(product),
												alt: product.name,
												loading: "lazy"
											}, null, 8, ["src", "alt"]), createVNode("span", { class: "hit" }, "ХІТ")]),
											createVNode("h3", null, toDisplayString(product.name), 1),
											createVNode("p", null, [originalPrice(product) ? (openBlock(), createBlock("del", { key: 0 }, toDisplayString((originalPrice(product) / 100).toLocaleString("uk-UA")) + " ₴", 1)) : createCommentVNode("", true), createTextVNode(toDisplayString((price(product) / 100).toLocaleString("uk-UA")) + " ₴", 1)])
										];
									}),
									_: 2
								}, _parent, _scopeId));
							});
							_push(`<!--]--></div></section>`);
						} else _push(`<!---->`);
						_push(`<section class="home-categories"${_scopeId}><!--[-->`);
						ssrRenderList(categoryCards.value, (item) => {
							_push(ssrRenderComponent(unref(Link), {
								key: item.slug,
								href: `/categories/${item.slug}`,
								class: "home-category-card"
							}, {
								default: withCtx((_, _push, _parent, _scopeId) => {
									if (_push) _push(`<img${ssrRenderAttr("src", item.image)}${ssrRenderAttr("alt", item.name)} loading="lazy"${_scopeId}><strong${_scopeId}>${ssrInterpolate(item.name)}</strong><span aria-hidden="true"${_scopeId}>⟶</span>`);
									else return [
										createVNode("img", {
											src: item.image,
											alt: item.name,
											loading: "lazy"
										}, null, 8, ["src", "alt"]),
										createVNode("strong", null, toDisplayString(item.name), 1),
										createVNode("span", { "aria-hidden": "true" }, "⟶")
									];
								}),
								_: 2
							}, _parent, _scopeId));
						});
						_push(`<!--]--></section><section id="faq" class="home-faq"${_scopeId}><h2${_scopeId}>Поширені питання</h2><div class="faq-list"${_scopeId}><!--[-->`);
						ssrRenderList(faqs.value, (faq, index) => {
							_push(`<article class="${ssrRenderClass({ open: openFaq.value === index })}"${_scopeId}><button type="button"${ssrRenderAttr("aria-expanded", openFaq.value === index)}${_scopeId}><span${_scopeId}>${ssrInterpolate(faq.q)}</span><b${_scopeId}>${ssrInterpolate(openFaq.value === index ? "−" : "+")}</b></button>`);
							if (openFaq.value === index) _push(`<p${_scopeId}>${ssrInterpolate(faq.a)}</p>`);
							else _push(`<!---->`);
							_push(`</article>`);
						});
						_push(`<!--]--></div></section><section class="home-instagram"${_scopeId}><div class="instagram-mark"${_scopeId}>◎</div><h2${_scopeId}>${ssrInterpolate(__props.homepage?.instagram_title || "Ви і Lamari Jewelry")}</h2><p${_scopeId}>${ssrInterpolate(__props.homepage?.instagram_text)}</p><div class="instagram-gallery"${_scopeId}><!--[-->`);
						ssrRenderList(__props.homepage?.instagram_images || [], (image, index) => {
							_push(`<img${ssrRenderAttr("src", pageAsset(image))}${ssrRenderAttr("alt", `Відгук клієнтки Lamari ${index + 1}`)} loading="lazy"${_scopeId}>`);
						});
						_push(`<!--]--></div><a class="instagram-button"${ssrRenderAttr("href", __props.homepage?.instagram_url || "https://www.instagram.com/lamari.jewelry/")} target="_blank" rel="noopener"${_scopeId}>Наш Instagram</a></section>`);
					} else return [
						createVNode(unref(Link), {
							href: __props.homepage?.hero_link || "/catalog",
							class: "home-campaign",
							"aria-label": "Перейти до каталогу всіх товарів"
						}, {
							default: withCtx(() => [createVNode("img", {
								class: "home-campaign-desktop",
								src: pageAsset(__props.homepage?.desktop_hero_image || "/images/home/summer-collection-desktop.jpg?v=2"),
								alt: "Літня колекція Lamari Jewelry",
								loading: "eager",
								fetchpriority: "high"
							}, null, 8, ["src"]), createVNode("video", {
								src: pageAsset(__props.homepage?.mobile_hero_video || "/images/home/hero-video.mp4"),
								poster: pageAsset(__props.homepage?.mobile_hero_poster || "/images/home/hero-video-first-frame.webp"),
								autoplay: "",
								muted: "",
								loop: "",
								playsinline: "",
								preload: "metadata",
								"aria-hidden": "true"
							}, null, 8, ["src", "poster"])]),
							_: 1
						}, 8, ["href"]),
						createVNode("div", {
							class: "ticker desktop-home-ticker",
							"aria-label": "Безкоштовне брендоване пакування"
						}, [createVNode("div", { class: "ticker-track" }, [(openBlock(), createBlock(Fragment, null, renderList(4, (index) => {
							return createVNode("span", { key: index }, toDisplayString(__props.homepage?.ticker_text || "БЕЗКОШТОВНЕ БРЕНДОВАНЕ ПАКУВАННЯ"), 1);
						}), 64))])]),
						__props.homepage?.show_new_products !== false && __props.newProducts.length ? (openBlock(), createBlock("section", {
							key: 0,
							class: "home-showcase"
						}, [createVNode("div", { class: "home-section-heading" }, [createVNode("h2", null, toDisplayString(__props.homepage?.new_products_title || "Новинки"), 1), createVNode(unref(Link), { href: "/catalog?sort=newest" }, {
							default: withCtx(() => [createTextVNode("Переглянути всі")]),
							_: 1
						})]), createVNode("div", { class: "home-products" }, [(openBlock(true), createBlock(Fragment, null, renderList(__props.newProducts, (product) => {
							return openBlock(), createBlock(unref(Link), {
								key: product.id,
								href: `/products/${product.slug}`,
								class: "home-product-card"
							}, {
								default: withCtx(() => [
									createVNode("div", { class: "home-product-image" }, [createVNode("img", {
										src: productImage(product),
										alt: product.name,
										loading: "lazy"
									}, null, 8, ["src", "alt"]), createVNode("span", null, "NEW")]),
									createVNode("h3", null, toDisplayString(product.name), 1),
									createVNode("p", null, [originalPrice(product) ? (openBlock(), createBlock("del", { key: 0 }, toDisplayString((originalPrice(product) / 100).toLocaleString("uk-UA")) + " ₴", 1)) : createCommentVNode("", true), createTextVNode(toDisplayString((price(product) / 100).toLocaleString("uk-UA")) + " ₴", 1)])
								]),
								_: 2
							}, 1032, ["href"]);
						}), 128))])])) : createCommentVNode("", true),
						__props.homepage?.show_hit_products !== false && __props.hitProducts.length ? (openBlock(), createBlock("section", {
							key: 1,
							class: "home-showcase"
						}, [createVNode("div", { class: "home-section-heading" }, [createVNode("h2", null, toDisplayString(__props.homepage?.hit_products_title || "Хіти продажів"), 1), createVNode(unref(Link), { href: "/catalog" }, {
							default: withCtx(() => [createTextVNode("Переглянути всі")]),
							_: 1
						})]), createVNode("div", { class: "home-products" }, [(openBlock(true), createBlock(Fragment, null, renderList(__props.hitProducts, (product) => {
							return openBlock(), createBlock(unref(Link), {
								key: product.id,
								href: `/products/${product.slug}`,
								class: "home-product-card"
							}, {
								default: withCtx(() => [
									createVNode("div", { class: "home-product-image" }, [createVNode("img", {
										src: productImage(product),
										alt: product.name,
										loading: "lazy"
									}, null, 8, ["src", "alt"]), createVNode("span", { class: "hit" }, "ХІТ")]),
									createVNode("h3", null, toDisplayString(product.name), 1),
									createVNode("p", null, [originalPrice(product) ? (openBlock(), createBlock("del", { key: 0 }, toDisplayString((originalPrice(product) / 100).toLocaleString("uk-UA")) + " ₴", 1)) : createCommentVNode("", true), createTextVNode(toDisplayString((price(product) / 100).toLocaleString("uk-UA")) + " ₴", 1)])
								]),
								_: 2
							}, 1032, ["href"]);
						}), 128))])])) : createCommentVNode("", true),
						createVNode("section", { class: "home-categories" }, [(openBlock(true), createBlock(Fragment, null, renderList(categoryCards.value, (item) => {
							return openBlock(), createBlock(unref(Link), {
								key: item.slug,
								href: `/categories/${item.slug}`,
								class: "home-category-card"
							}, {
								default: withCtx(() => [
									createVNode("img", {
										src: item.image,
										alt: item.name,
										loading: "lazy"
									}, null, 8, ["src", "alt"]),
									createVNode("strong", null, toDisplayString(item.name), 1),
									createVNode("span", { "aria-hidden": "true" }, "⟶")
								]),
								_: 2
							}, 1032, ["href"]);
						}), 128))]),
						createVNode("section", {
							id: "faq",
							class: "home-faq"
						}, [createVNode("h2", null, "Поширені питання"), createVNode("div", { class: "faq-list" }, [(openBlock(true), createBlock(Fragment, null, renderList(faqs.value, (faq, index) => {
							return openBlock(), createBlock("article", {
								key: faq.q,
								class: { open: openFaq.value === index }
							}, [createVNode("button", {
								type: "button",
								"aria-expanded": openFaq.value === index,
								onClick: ($event) => openFaq.value = openFaq.value === index ? null : index
							}, [createVNode("span", null, toDisplayString(faq.q), 1), createVNode("b", null, toDisplayString(openFaq.value === index ? "−" : "+"), 1)], 8, ["aria-expanded", "onClick"]), openFaq.value === index ? (openBlock(), createBlock("p", { key: 0 }, toDisplayString(faq.a), 1)) : createCommentVNode("", true)], 2);
						}), 128))])]),
						createVNode("section", { class: "home-instagram" }, [
							createVNode("div", { class: "instagram-mark" }, "◎"),
							createVNode("h2", null, toDisplayString(__props.homepage?.instagram_title || "Ви і Lamari Jewelry"), 1),
							createVNode("p", null, toDisplayString(__props.homepage?.instagram_text), 1),
							createVNode("div", { class: "instagram-gallery" }, [(openBlock(true), createBlock(Fragment, null, renderList(__props.homepage?.instagram_images || [], (image, index) => {
								return openBlock(), createBlock("img", {
									key: image,
									src: pageAsset(image),
									alt: `Відгук клієнтки Lamari ${index + 1}`,
									loading: "lazy"
								}, null, 8, ["src", "alt"]);
							}), 128))]),
							createVNode("a", {
								class: "instagram-button",
								href: __props.homepage?.instagram_url || "https://www.instagram.com/lamari.jewelry/",
								target: "_blank",
								rel: "noopener"
							}, "Наш Instagram", 8, ["href"])
						])
					];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/js/Pages/Home.vue
var _sfc_setup = Home_vue_vue_type_script_setup_true_lang_default.setup;
Home_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Home.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Home_default = Home_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { Home_default as default };
