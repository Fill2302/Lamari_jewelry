import { t as StoreLayout_default } from "./StoreLayout-CI3WdeRz.js";
import { Fragment, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, openBlock, ref, renderList, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderAttr, ssrRenderClass, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
//#region resources/js/Pages/Home.vue?vue&type=script&setup=true&lang.ts
var Home_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Home",
	__ssrInlineRender: true,
	props: {
		categories: {},
		newProducts: {},
		hitProducts: {}
	},
	setup(__props) {
		const openFaq = ref(null);
		const asset = (url) => url?.startsWith("http") ? url : `/storage/${url}`;
		const productImage = (product) => asset(product.media?.find((item) => item.type === "image")?.url || product.image_url);
		const price = (product) => product.variants?.[0]?.effective_price_amount ?? product.variants?.[0]?.price_amount ?? 0;
		const originalPrice = (product) => product.variants?.[0]?.discount_percentage ? product.variants[0].original_price_amount : product.compare_at_price_amount;
		const categoryCards = [
			{
				name: "Кольє",
				slug: "necklaces",
				image: "/images/home/categories/necklaces.jpg"
			},
			{
				name: "Чокери",
				slug: "chokers",
				image: "/images/home/categories/chokers.jpg"
			},
			{
				name: "Сережки",
				slug: "earrings",
				image: "/images/home/categories/earrings.jpg"
			},
			{
				name: "Ланцюжки",
				slug: "chains",
				image: "/images/home/categories/chains.jpg"
			},
			{
				name: "Браслети",
				slug: "bracelets",
				image: "/images/home/categories/bracelets.jpg"
			},
			{
				name: "Анклети",
				slug: "anklets",
				image: "/images/home/categories/anklets.jpeg"
			},
			{
				name: "Каблучки",
				slug: "rings",
				image: "/images/home/categories/rings.jpg"
			},
			{
				name: "Комплекти",
				slug: "sets",
				image: "/images/home/categories/sets.jpg"
			},
			{
				name: "Літня колекція",
				slug: "summer",
				image: "/images/home/categories/summer.jpg"
			},
			{
				name: "Булавки",
				slug: "pins",
				image: "/images/home/categories/pins.jpg"
			}
		];
		const faqs = [
			{
				q: "Чому варто обирати прикраси LAMARI?",
				a: "Усі прикраси створені за авторською ідеєю та виконані з матеріалів найвищої якості. Виготовляємо прикраси на замовлення протягом 1–2 днів і відправляємо у брендованих коробочках, які зручно використовувати для зберігання або подарунка. Маємо широкий асортимент прикрас із натуральних перлин, каміння та ланцюжків, здійснюємо відправлення Україною та за кордон."
			},
			{
				q: "Який матеріал фурнітури?",
				a: "Ми обираємо матеріали класу люкс із якісним, стійким та гіпоалергенним покриттям. У золотому кольорі це позолота 18 карат по латуні. У срібному — латунь із покриттям родій або ювелірна сталь. Усі матеріали спершу тестуємо особисто й лише потім виготовляємо з ними прикраси."
			},
			{
				q: "Які перли та каміння використовуєте?",
				a: "Ми використовуємо тільки натуральні прісноводні перли та натуральне каміння."
			},
			{
				q: "У мене чутливі вуха. Який матеріал сережок?",
				a: "Наші сережки мають гіпоалергенний сплав, не викликають алергії та дискомфорту."
			},
			{
				q: "Чи темніє прикраса?",
				a: "Наші прикраси не темніють. Щоб прикраса якомога довше зберігала початковий вигляд, не рекомендуємо мочити її, особливо у солоній воді та басейні."
			},
			{
				q: "Можна мочити прикраси?",
				a: "Прикраси з ювелірної сталі можна мочити та носити не знімаючи. Прикраси з покриттям золотом і родієм рекомендуємо знімати перед душем, морем або басейном. Якщо прикраса намокла, нічого страшного — просто просушіть її серветкою."
			},
			{
				q: "Який догляд за прикрасами?",
				a: "Рекомендуємо знімати прикраси перед сном і душем, щоб уникнути зайвого тертя. Не наносити на них парфуми та креми безпосередньо, а також зберігати прикраси окремо одну від одної."
			},
			{
				q: "Як довго мені прослужать прикраси?",
				a: "Ми обираємо найкращі матеріали та тестуємо їх особисто. Довговічність первинного вигляду залежить від багатьох факторів: pH шкіри, частоти носіння, контакту з парфумами та косметикою."
			},
			{
				q: "Чи можна обрати довжину прикраси?",
				a: "Так, бажану довжину можна обрати у картці товару. Якщо потрібної довжини немає, зв’яжіться з нами через чат — ми допоможемо."
			}
		];
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
			_push(ssrRenderComponent(StoreLayout_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<section class="home-campaign" aria-label="Summer Collection Lamari"${_scopeId}><video${ssrRenderAttr("src", "/images/home/hero-video.mp4")}${ssrRenderAttr("poster", "/images/home/summer-collection-mobile-clean-v3.webp")} autoplay muted loop playsinline preload="metadata" aria-hidden="true"${_scopeId}></video>`);
						_push(ssrRenderComponent(unref(Link), {
							href: "/catalog",
							class: "home-campaign-button"
						}, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) _push(`Каталог`);
								else return [createTextVNode("Каталог")];
							}),
							_: 1
						}, _parent, _scopeId));
						_push(`</section>`);
						if (__props.newProducts.length) {
							_push(`<section class="home-showcase"${_scopeId}><div class="home-section-heading"${_scopeId}><h2${_scopeId}>Новинки</h2>`);
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
						if (__props.hitProducts.length) {
							_push(`<section class="home-showcase"${_scopeId}><div class="home-section-heading"${_scopeId}><h2${_scopeId}>Хіти продажів</h2>`);
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
						ssrRenderList(categoryCards, (item) => {
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
						_push(`<!--]--></section><section class="home-faq"${_scopeId}><h2${_scopeId}>Поширені питання</h2><div class="faq-list"${_scopeId}><!--[-->`);
						ssrRenderList(faqs, (faq, index) => {
							_push(`<article class="${ssrRenderClass({ open: openFaq.value === index })}"${_scopeId}><button type="button"${ssrRenderAttr("aria-expanded", openFaq.value === index)}${_scopeId}><span${_scopeId}>${ssrInterpolate(faq.q)}</span><b${_scopeId}>${ssrInterpolate(openFaq.value === index ? "−" : "+")}</b></button>`);
							if (openFaq.value === index) _push(`<p${_scopeId}>${ssrInterpolate(faq.a)}</p>`);
							else _push(`<!---->`);
							_push(`</article>`);
						});
						_push(`<!--]--></div></section><section class="home-instagram"${_scopeId}><div class="instagram-mark"${_scopeId}>◎</div><h2${_scopeId}>Ви і Lamari Jewelry</h2><p${_scopeId}>Діліться своїми образами, відзначайте нас у Instagram, і ми із задоволенням додамо ваші фото</p><div class="instagram-gallery"${_scopeId}><!--[-->`);
						ssrRenderList(6, (n) => {
							_push(`<img${ssrRenderAttr("src", `/images/home/instagram/insta${n}.png`)}${ssrRenderAttr("alt", `Відгук клієнтки Lamari ${n}`)} loading="lazy"${_scopeId}>`);
						});
						_push(`<!--]--></div><a class="instagram-button" href="https://www.instagram.com/lamari.jewelry/" target="_blank" rel="noopener"${_scopeId}>Наш Instagram</a></section>`);
					} else return [
						createVNode("section", {
							class: "home-campaign",
							"aria-label": "Summer Collection Lamari"
						}, [createVNode("video", {
							src: "/images/home/hero-video.mp4",
							poster: "/images/home/summer-collection-mobile-clean-v3.webp",
							autoplay: "",
							muted: "",
							loop: "",
							playsinline: "",
							preload: "metadata",
							"aria-hidden": "true"
						}), createVNode(unref(Link), {
							href: "/catalog",
							class: "home-campaign-button"
						}, {
							default: withCtx(() => [createTextVNode("Каталог")]),
							_: 1
						})]),
						__props.newProducts.length ? (openBlock(), createBlock("section", {
							key: 0,
							class: "home-showcase"
						}, [createVNode("div", { class: "home-section-heading" }, [createVNode("h2", null, "Новинки"), createVNode(unref(Link), { href: "/catalog?sort=newest" }, {
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
						__props.hitProducts.length ? (openBlock(), createBlock("section", {
							key: 1,
							class: "home-showcase"
						}, [createVNode("div", { class: "home-section-heading" }, [createVNode("h2", null, "Хіти продажів"), createVNode(unref(Link), { href: "/catalog" }, {
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
						createVNode("section", { class: "home-categories" }, [(openBlock(), createBlock(Fragment, null, renderList(categoryCards, (item) => {
							return createVNode(unref(Link), {
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
						}), 64))]),
						createVNode("section", { class: "home-faq" }, [createVNode("h2", null, "Поширені питання"), createVNode("div", { class: "faq-list" }, [(openBlock(), createBlock(Fragment, null, renderList(faqs, (faq, index) => {
							return createVNode("article", {
								key: faq.q,
								class: { open: openFaq.value === index }
							}, [createVNode("button", {
								type: "button",
								"aria-expanded": openFaq.value === index,
								onClick: ($event) => openFaq.value = openFaq.value === index ? null : index
							}, [createVNode("span", null, toDisplayString(faq.q), 1), createVNode("b", null, toDisplayString(openFaq.value === index ? "−" : "+"), 1)], 8, ["aria-expanded", "onClick"]), openFaq.value === index ? (openBlock(), createBlock("p", { key: 0 }, toDisplayString(faq.a), 1)) : createCommentVNode("", true)], 2);
						}), 64))])]),
						createVNode("section", { class: "home-instagram" }, [
							createVNode("div", { class: "instagram-mark" }, "◎"),
							createVNode("h2", null, "Ви і Lamari Jewelry"),
							createVNode("p", null, "Діліться своїми образами, відзначайте нас у Instagram, і ми із задоволенням додамо ваші фото"),
							createVNode("div", { class: "instagram-gallery" }, [(openBlock(), createBlock(Fragment, null, renderList(6, (n) => {
								return createVNode("img", {
									key: n,
									src: `/images/home/instagram/insta${n}.png`,
									alt: `Відгук клієнтки Lamari ${n}`,
									loading: "lazy"
								}, null, 8, ["src", "alt"]);
							}), 64))]),
							createVNode("a", {
								class: "instagram-button",
								href: "https://www.instagram.com/lamari.jewelry/",
								target: "_blank",
								rel: "noopener"
							}, "Наш Instagram")
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
