import { t as StoreLayout_default } from "./StoreLayout-DpbhNzPq.js";
import { Fragment, Teleport, computed, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, onMounted, openBlock, ref, renderList, toDisplayString, unref, useSSRContext, vModelCheckbox, vModelRadio, vModelText, watch, withCtx, withDirectives, withModifiers } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrLooseContain, ssrLooseEqual, ssrRenderAttr, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderStyle, ssrRenderTeleport } from "vue/server-renderer";
//#region resources/js/Pages/Category.vue?vue&type=script&setup=true&lang.ts
var Category_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Category",
	__ssrInlineRender: true,
	props: {
		category: {},
		categoryNavigation: {},
		products: {},
		productTotal: {},
		pagination: {},
		filters: {},
		selectedFilters: {},
		catalogControls: {},
		catalogUrl: {},
		searchQuery: {}
	},
	setup(__props) {
		const props = __props;
		const filtersOpen = ref(false);
		const catalogColumns = ref(2);
		const selected = ref(Object.fromEntries((props.filters || []).map((filter) => [filter.slug, [...props.selectedFilters?.[filter.slug] || []]])));
		const selectedFilterCount = computed(() => Object.values(selected.value).reduce((total, values) => total + values.length, 0));
		const priceFrom = ref(props.catalogControls?.priceFrom || props.catalogControls?.priceMin || 0);
		const priceTo = ref(props.catalogControls?.priceTo || props.catalogControls?.priceMax || 0);
		const availability = ref(props.catalogControls?.availability || "");
		const sort = ref(props.catalogControls?.sort || "manual");
		const activeControlCount = computed(() => selectedFilterCount.value + (availability.value ? 1 : 0) + (priceFrom.value > (props.catalogControls?.priceMin || 0) ? 1 : 0) + (priceTo.value < (props.catalogControls?.priceMax || 0) ? 1 : 0));
		const visiblePages = computed(() => {
			if (!props.pagination) return [];
			const total = props.pagination.lastPage;
			const windowSize = Math.min(5, total);
			let start = Math.max(1, props.pagination.currentPage - 2);
			start = Math.min(start, total - windowSize + 1);
			return Array.from({ length: windowSize }, (_, index) => start + index);
		});
		const mediaItems = (product) => {
			const media = product.media?.filter((item) => item.url && (item.type === "image" || item.type === "video")) || [];
			return media.length ? media : [product.image_url ? {
				type: "image",
				url: product.image_url
			} : null].filter(Boolean);
		};
		const asset = (url) => url?.startsWith("http") ? url : `/storage/${url}`;
		const slideIndexes = ref({});
		const slideOffsets = ref({});
		const draggingSlides = ref({});
		const touchStarts = /* @__PURE__ */ new Map();
		const swipeBlockedLinks = /* @__PURE__ */ new Set();
		const activeSlide = (product) => slideIndexes.value[product.id] || 0;
		const slideStyle = (product) => ({ transform: `translate3d(calc(${-activeSlide(product) * 100}% + ${slideOffsets.value[product.id] || 0}px), 0, 0)` });
		const startProductSwipe = (event, product) => {
			const touch = event.touches[0];
			if (touch) {
				touchStarts.set(product.id, {
					x: touch.clientX,
					y: touch.clientY
				});
				draggingSlides.value[product.id] = false;
				slideOffsets.value[product.id] = 0;
			}
		};
		const moveProductSwipe = (event, product) => {
			const start = touchStarts.get(product.id);
			const touch = event.touches[0];
			if (!start || !touch || mediaItems(product).length < 2) return;
			const deltaX = touch.clientX - start.x;
			const deltaY = touch.clientY - start.y;
			if (!draggingSlides.value[product.id]) {
				if (Math.abs(deltaX) < 6 || Math.abs(deltaX) <= Math.abs(deltaY)) return;
				draggingSlides.value[product.id] = true;
			}
			event.preventDefault();
			const index = activeSlide(product);
			const lastIndex = mediaItems(product).length - 1;
			const atEdge = index === 0 && deltaX > 0 || index === lastIndex && deltaX < 0;
			slideOffsets.value[product.id] = atEdge ? deltaX * .28 : deltaX;
		};
		const endProductSwipe = (event, product) => {
			const start = touchStarts.get(product.id);
			const touch = event.changedTouches[0];
			touchStarts.delete(product.id);
			const wasDragging = draggingSlides.value[product.id];
			draggingSlides.value[product.id] = false;
			slideOffsets.value[product.id] = 0;
			if (!start || !touch || mediaItems(product).length < 2 || !wasDragging) return;
			const deltaX = touch.clientX - start.x;
			const deltaY = touch.clientY - start.y;
			event.preventDefault();
			const count = mediaItems(product).length;
			if (Math.abs(deltaX) >= 35 && Math.abs(deltaX) > Math.abs(deltaY)) {
				const nextIndex = activeSlide(product) + (deltaX < 0 ? 1 : -1);
				slideIndexes.value[product.id] = Math.max(0, Math.min(count - 1, nextIndex));
			}
			swipeBlockedLinks.add(product.id);
			window.setTimeout(() => swipeBlockedLinks.delete(product.id), 350);
		};
		const cancelProductSwipe = (product) => {
			touchStarts.delete(product.id);
			draggingSlides.value[product.id] = false;
			slideOffsets.value[product.id] = 0;
		};
		const openProduct = (event, product) => {
			if (!swipeBlockedLinks.has(product.id)) return;
			event.preventDefault();
			swipeBlockedLinks.delete(product.id);
		};
		const price = (product) => product.variants[0]?.effective_price_amount ?? product.variants[0]?.price_amount ?? 0;
		const originalPrice = (product) => product.variants[0]?.discount_percentage ? product.variants[0]?.original_price_amount : product.compare_at_price_amount;
		const catalogBadges = (product) => {
			const badges = Array.isArray(product.catalog_badges) ? [...product.catalog_badges] : [];
			const percentage = Number(product.variants?.[0]?.discount_percentage || 0);
			if (percentage > 0) {
				const label = `-${Math.round(percentage)}%`;
				if (!badges.some((badge) => badge.type === "sale" && badge.label === label)) badges.push({
					type: "sale",
					label
				});
			}
			return badges;
		};
		const availableVariant = (product) => product.variants?.find((variant) => variant.is_active && variant.stock_on_hand > variant.stock_reserved);
		const addingProduct = ref(null);
		const addToCart = (product) => {
			const variant = availableVariant(product);
			if (!variant || addingProduct.value) return;
			addingProduct.value = product.id;
			router.post(`/cart/${variant.id}`, { quantity: 1 }, {
				preserveScroll: true,
				onFinish: () => addingProduct.value = null
			});
		};
		const applyFilters = () => {
			router.get(props.catalogUrl || `/categories/${props.category.slug}`, {
				filters: selected.value,
				price_from: priceFrom.value,
				price_to: priceTo.value,
				availability: availability.value || void 0,
				sort: sort.value,
				q: props.searchQuery || void 0
			}, {
				preserveState: true,
				preserveScroll: true,
				onSuccess: () => filtersOpen.value = false
			});
		};
		const clearFilters = () => {
			selected.value = {};
			priceFrom.value = props.catalogControls?.priceMin || 0;
			priceTo.value = props.catalogControls?.priceMax || 0;
			availability.value = "";
			sort.value = "manual";
			applyFilters();
		};
		const setCatalogColumns = (columns) => {
			catalogColumns.value = columns;
		};
		onMounted(() => {
			catalogColumns.value = localStorage.getItem("lamari-catalog-columns") === "1" ? 1 : 2;
		});
		watch(catalogColumns, (columns) => {
			localStorage.setItem("lamari-catalog-columns", String(columns));
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<title${_scopeId}>${ssrInterpolate(__props.category.seo_title || __props.category.name)}</title><meta name="description"${ssrRenderAttr("content", __props.category.seo_description || __props.category.description)}${_scopeId}>`);
					else return [createVNode("title", null, toDisplayString(__props.category.seo_title || __props.category.name), 1), createVNode("meta", {
						name: "description",
						content: __props.category.seo_description || __props.category.description
					}, null, 8, ["content"])];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(StoreLayout_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						if (__props.categoryNavigation) {
							_push(`<nav class="category-scroll" aria-label="Категорії товарів"${_scopeId}>`);
							_push(ssrRenderComponent(unref(Link), {
								href: __props.categoryNavigation.allHref,
								class: { active: __props.categoryNavigation.allHref === (__props.catalogUrl || `/categories/${__props.category.slug}`) }
							}, {
								default: withCtx((_, _push, _parent, _scopeId) => {
									if (_push) _push(`Усі товари`);
									else return [createTextVNode("Усі товари")];
								}),
								_: 1
							}, _parent, _scopeId));
							if (__props.categoryNavigation.root) _push(ssrRenderComponent(unref(Link), {
								href: `/categories/${__props.categoryNavigation.root.slug}`,
								class: { active: __props.category.slug === __props.categoryNavigation.root.slug || __props.category.parent_id === __props.categoryNavigation.root.id }
							}, {
								default: withCtx((_, _push, _parent, _scopeId) => {
									if (_push) _push(`${ssrInterpolate(__props.categoryNavigation.root.name)}`);
									else return [createTextVNode(toDisplayString(__props.categoryNavigation.root.name), 1)];
								}),
								_: 1
							}, _parent, _scopeId));
							else _push(`<!---->`);
							_push(`<!--[-->`);
							ssrRenderList(__props.categoryNavigation.items, (item) => {
								_push(ssrRenderComponent(unref(Link), {
									key: item.id,
									href: `/categories/${item.slug}`,
									class: { active: item.slug === __props.category.slug }
								}, {
									default: withCtx((_, _push, _parent, _scopeId) => {
										if (_push) _push(`${ssrInterpolate(item.name)}`);
										else return [createTextVNode(toDisplayString(item.name), 1)];
									}),
									_: 2
								}, _parent, _scopeId));
							});
							_push(`<!--]--></nav>`);
						} else _push(`<!---->`);
						_push(`<div class="catalog-tools"${_scopeId}><div class="catalog-view-switcher" role="group" aria-label="Вигляд товарів"${_scopeId}><button type="button" class="${ssrRenderClass([{ active: catalogColumns.value === 1 }, "catalog-view-button"])}"${ssrRenderAttr("aria-pressed", catalogColumns.value === 1)} aria-label="Один товар у ряд"${_scopeId}><span class="view-icon view-icon-one" aria-hidden="true"${_scopeId}></span></button><button type="button" class="${ssrRenderClass([{ active: catalogColumns.value === 2 }, "catalog-view-button"])}"${ssrRenderAttr("aria-pressed", catalogColumns.value === 2)} aria-label="Два товари в ряд"${_scopeId}><span class="view-icon view-icon-two" aria-hidden="true"${_scopeId}><i${_scopeId}></i><i${_scopeId}></i></span></button></div><span${_scopeId}>${ssrInterpolate(__props.productTotal ?? __props.products.length)} товарів</span><button${_scopeId}><span class="filter-icon" aria-hidden="true"${_scopeId}>☷</span> Фільтр (${ssrInterpolate(activeControlCount.value)})</button></div><section class="page-head catalog-head"${_scopeId}><h1${_scopeId}>${ssrInterpolate(__props.searchQuery ? "Результати пошуку" : __props.category.name)}</h1>`);
						if (__props.searchQuery) _push(`<p${_scopeId}>За запитом «${ssrInterpolate(__props.searchQuery)}» знайдено: ${ssrInterpolate(__props.productTotal ?? __props.products.length)}</p>`);
						else _push(`<!---->`);
						_push(`</section>`);
						ssrRenderTeleport(_push, (_push) => {
							if (filtersOpen.value) {
								_push(`<div class="filter-overlay"${_scopeId}><aside class="catalog-filters" aria-label="Фільтри каталогу"${_scopeId}><header${_scopeId}><strong${_scopeId}>Фільтри</strong><button type="button" aria-label="Закрити"${_scopeId}>×</button></header><!--[-->`);
								ssrRenderList(__props.filters, (filter) => {
									_push(`<fieldset${_scopeId}><legend${_scopeId}>${ssrInterpolate(filter.name)}</legend><!--[-->`);
									ssrRenderList(filter.values, (value) => {
										_push(`<label${_scopeId}><input${ssrIncludeBooleanAttr(Array.isArray(selected.value[filter.slug]) ? ssrLooseContain(selected.value[filter.slug], value.slug) : selected.value[filter.slug]) ? " checked" : ""} type="checkbox"${ssrRenderAttr("value", value.slug)}${_scopeId}>`);
										if (value.color_hex) _push(`<i style="${ssrRenderStyle({ background: value.color_hex })}"${_scopeId}></i>`);
										else _push(`<!---->`);
										_push(`${ssrInterpolate(value.value)}</label>`);
									});
									_push(`<!--]--></fieldset>`);
								});
								_push(`<!--]--><fieldset class="price-filter"${_scopeId}><legend${_scopeId}>Ціна, грн</legend><div${_scopeId}><label${_scopeId}>від <input${ssrRenderAttr("value", priceFrom.value)} type="number"${ssrRenderAttr("min", __props.catalogControls?.priceMin)}${ssrRenderAttr("max", priceTo.value)}${_scopeId}></label><span${_scopeId}>—</span><label${_scopeId}>до <input${ssrRenderAttr("value", priceTo.value)} type="number"${ssrRenderAttr("min", priceFrom.value)}${ssrRenderAttr("max", __props.catalogControls?.priceMax)}${_scopeId}></label></div></fieldset><fieldset${_scopeId}><legend${_scopeId}>Наявність</legend><label${_scopeId}><input${ssrIncludeBooleanAttr(ssrLooseEqual(availability.value, "in_stock")) ? " checked" : ""} type="radio" value="in_stock"${_scopeId}>В наявності</label><label${_scopeId}><input${ssrIncludeBooleanAttr(ssrLooseEqual(availability.value, "preorder")) ? " checked" : ""} type="radio" value="preorder"${_scopeId}>Під замовлення</label></fieldset><fieldset${_scopeId}><legend${_scopeId}>Сортувати</legend><label${_scopeId}><input${ssrIncludeBooleanAttr(ssrLooseEqual(sort.value, "manual")) ? " checked" : ""} type="radio" value="manual"${_scopeId}>Порядок з адмінки</label><label${_scopeId}><input${ssrIncludeBooleanAttr(ssrLooseEqual(sort.value, "newest")) ? " checked" : ""} type="radio" value="newest"${_scopeId}>За новизною</label><label${_scopeId}><input${ssrIncludeBooleanAttr(ssrLooseEqual(sort.value, "price_asc")) ? " checked" : ""} type="radio" value="price_asc"${_scopeId}>Ціна: від нижчої</label><label${_scopeId}><input${ssrIncludeBooleanAttr(ssrLooseEqual(sort.value, "price_desc")) ? " checked" : ""} type="radio" value="price_desc"${_scopeId}>Ціна: від вищої</label></fieldset><div class="filter-actions"${_scopeId}><button class="button"${_scopeId}>Показати товари</button><button class="link"${_scopeId}>Очистити все</button></div></aside></div>`);
							} else _push(`<!---->`);
						}, "body", false, _parent);
						if (__props.products.length) {
							_push(`<section class="${ssrRenderClass([`catalog-columns-${catalogColumns.value}`, "product-catalog"])}"${_scopeId}><!--[-->`);
							ssrRenderList(__props.products, (product) => {
								_push(`<article class="catalog-card"${_scopeId}>`);
								_push(ssrRenderComponent(unref(Link), {
									href: `/products/${product.slug}`,
									class: "catalog-card-link",
									onClick: ($event) => openProduct($event, product)
								}, {
									default: withCtx((_, _push, _parent, _scopeId) => {
										if (_push) {
											_push(`<div class="catalog-image"${_scopeId}><div class="${ssrRenderClass([{ "is-dragging": draggingSlides.value[product.id] }, "catalog-image-track"])}" style="${ssrRenderStyle(slideStyle(product))}"${_scopeId}><!--[-->`);
											ssrRenderList(mediaItems(product), (item, index) => {
												_push(`<!--[-->`);
												if (item.type === "image") _push(`<img${ssrRenderAttr("src", asset(item.url))}${ssrRenderAttr("alt", index === 0 ? product.name : `${product.name}, фото ${index + 1}`)} loading="lazy" draggable="false"${_scopeId}>`);
												else _push(`<video${ssrRenderAttr("src", asset(item.url))}${ssrRenderAttr("poster", item.poster_url ? asset(item.poster_url) : void 0)} muted autoplay loop playsinline disablepictureinpicture disableremoteplayback preload="metadata" tabindex="-1"${_scopeId}></video>`);
												_push(`<!--]-->`);
											});
											_push(`<!--]--></div>`);
											if (catalogBadges(product).length) {
												_push(`<div class="catalog-badges"${_scopeId}><!--[-->`);
												ssrRenderList(catalogBadges(product), (badge) => {
													_push(`<span class="${ssrRenderClass([`catalog-badge-${badge.type}`, "catalog-badge"])}"${_scopeId}>${ssrInterpolate(badge.label)}</span>`);
												});
												_push(`<!--]--></div>`);
											} else _push(`<!---->`);
											if (mediaItems(product).length > 1) {
												_push(`<div class="catalog-image-dots"${ssrRenderAttr("aria-label", `${mediaItems(product).length} медіафайлів`)}${_scopeId}><!--[-->`);
												ssrRenderList(mediaItems(product), (_, index) => {
													_push(`<span class="${ssrRenderClass({ active: index === activeSlide(product) })}"${_scopeId}></span>`);
												});
												_push(`<!--]--></div>`);
											} else _push(`<!---->`);
											_push(`</div><h3${_scopeId}>${ssrInterpolate(product.name)}</h3><p class="catalog-price"${_scopeId}>`);
											if (originalPrice(product)) _push(`<del${_scopeId}>${ssrInterpolate((originalPrice(product) / 100).toLocaleString("uk-UA"))} ₴</del>`);
											else _push(`<!---->`);
											_push(`<span${_scopeId}>${ssrInterpolate((price(product) / 100).toLocaleString("uk-UA"))} ₴</span></p>`);
										} else return [
											createVNode("div", {
												class: "catalog-image",
												onTouchstartPassive: ($event) => startProductSwipe($event, product),
												onTouchmove: ($event) => moveProductSwipe($event, product),
												onTouchend: ($event) => endProductSwipe($event, product),
												onTouchcancel: ($event) => cancelProductSwipe(product)
											}, [
												createVNode("div", {
													class: ["catalog-image-track", { "is-dragging": draggingSlides.value[product.id] }],
													style: slideStyle(product)
												}, [(openBlock(true), createBlock(Fragment, null, renderList(mediaItems(product), (item, index) => {
													return openBlock(), createBlock(Fragment, { key: `${item.type}-${item.url}` }, [item.type === "image" ? (openBlock(), createBlock("img", {
														key: 0,
														src: asset(item.url),
														alt: index === 0 ? product.name : `${product.name}, фото ${index + 1}`,
														loading: "lazy",
														draggable: "false"
													}, null, 8, ["src", "alt"])) : (openBlock(), createBlock("video", {
														key: 1,
														src: asset(item.url),
														poster: item.poster_url ? asset(item.poster_url) : void 0,
														muted: "",
														autoplay: "",
														loop: "",
														playsinline: "",
														disablepictureinpicture: "",
														disableremoteplayback: "",
														preload: "metadata",
														tabindex: "-1"
													}, null, 8, ["src", "poster"]))], 64);
												}), 128))], 6),
												catalogBadges(product).length ? (openBlock(), createBlock("div", {
													key: 0,
													class: "catalog-badges"
												}, [(openBlock(true), createBlock(Fragment, null, renderList(catalogBadges(product), (badge) => {
													return openBlock(), createBlock("span", {
														key: `${badge.type}-${badge.label}`,
														class: ["catalog-badge", `catalog-badge-${badge.type}`]
													}, toDisplayString(badge.label), 3);
												}), 128))])) : createCommentVNode("", true),
												mediaItems(product).length > 1 ? (openBlock(), createBlock("div", {
													key: 1,
													class: "catalog-image-dots",
													"aria-label": `${mediaItems(product).length} медіафайлів`
												}, [(openBlock(true), createBlock(Fragment, null, renderList(mediaItems(product), (_, index) => {
													return openBlock(), createBlock("span", {
														key: index,
														class: { active: index === activeSlide(product) }
													}, null, 2);
												}), 128))], 8, ["aria-label"])) : createCommentVNode("", true)
											], 40, [
												"onTouchstartPassive",
												"onTouchmove",
												"onTouchend",
												"onTouchcancel"
											]),
											createVNode("h3", null, toDisplayString(product.name), 1),
											createVNode("p", { class: "catalog-price" }, [originalPrice(product) ? (openBlock(), createBlock("del", { key: 0 }, toDisplayString((originalPrice(product) / 100).toLocaleString("uk-UA")) + " ₴", 1)) : createCommentVNode("", true), createVNode("span", null, toDisplayString((price(product) / 100).toLocaleString("uk-UA")) + " ₴", 1)])
										];
									}),
									_: 2
								}, _parent, _scopeId));
								_push(`<button type="button" class="catalog-add-button"${ssrIncludeBooleanAttr(!availableVariant(product) || addingProduct.value === product.id) ? " disabled" : ""}${_scopeId}>${ssrInterpolate(availableVariant(product) ? addingProduct.value === product.id ? "Додаємо…" : "Додати в кошик" : "Немає в наявності")}</button></article>`);
							});
							_push(`<!--]--></section>`);
						} else _push(`<!---->`);
						if (__props.pagination && __props.pagination.lastPage > 1) {
							_push(`<nav class="catalog-pagination" aria-label="Сторінки каталогу"${_scopeId}>`);
							if (visiblePages.value[0] > 1) {
								_push(`<!--[-->`);
								_push(ssrRenderComponent(unref(Link), {
									href: __props.pagination.pageUrls?.[1] || "?page=1",
									class: "pagination-number"
								}, {
									default: withCtx((_, _push, _parent, _scopeId) => {
										if (_push) _push(`1`);
										else return [createTextVNode("1")];
									}),
									_: 1
								}, _parent, _scopeId));
								if (visiblePages.value[0] > 2) _push(`<span class="pagination-ellipsis"${_scopeId}>…</span>`);
								else _push(`<!---->`);
								_push(`<!--]-->`);
							} else _push(`<!---->`);
							_push(`<!--[-->`);
							ssrRenderList(visiblePages.value, (page) => {
								_push(ssrRenderComponent(unref(Link), {
									key: page,
									href: __props.pagination.pageUrls?.[page] || `?page=${page}`,
									class: ["pagination-number", { active: page === __props.pagination.currentPage }],
									"aria-current": page === __props.pagination.currentPage ? "page" : void 0
								}, {
									default: withCtx((_, _push, _parent, _scopeId) => {
										if (_push) _push(`${ssrInterpolate(page)}`);
										else return [createTextVNode(toDisplayString(page), 1)];
									}),
									_: 2
								}, _parent, _scopeId));
							});
							_push(`<!--]-->`);
							if (visiblePages.value[visiblePages.value.length - 1] < __props.pagination.lastPage) {
								_push(`<!--[-->`);
								if (visiblePages.value[visiblePages.value.length - 1] < __props.pagination.lastPage - 1) _push(`<span class="pagination-ellipsis"${_scopeId}>…</span>`);
								else _push(`<!---->`);
								_push(ssrRenderComponent(unref(Link), {
									href: __props.pagination.pageUrls?.[__props.pagination.lastPage] || `?page=${__props.pagination.lastPage}`,
									class: "pagination-number"
								}, {
									default: withCtx((_, _push, _parent, _scopeId) => {
										if (_push) _push(`${ssrInterpolate(__props.pagination.lastPage)}`);
										else return [createTextVNode(toDisplayString(__props.pagination.lastPage), 1)];
									}),
									_: 1
								}, _parent, _scopeId));
								_push(`<!--]-->`);
							} else _push(`<!---->`);
							if (__props.pagination.nextUrl) _push(ssrRenderComponent(unref(Link), {
								href: __props.pagination.nextUrl,
								class: "pagination-arrow",
								"aria-label": "Наступна сторінка"
							}, {
								default: withCtx((_, _push, _parent, _scopeId) => {
									if (_push) _push(`→`);
									else return [createTextVNode("→")];
								}),
								_: 1
							}, _parent, _scopeId));
							else _push(`<!---->`);
							_push(`</nav>`);
						} else _push(`<section class="empty-category"${_scopeId}>За вибраними фільтрами товарів не знайдено.</section>`);
					} else return [
						__props.categoryNavigation ? (openBlock(), createBlock("nav", {
							key: 0,
							class: "category-scroll",
							"aria-label": "Категорії товарів"
						}, [
							createVNode(unref(Link), {
								href: __props.categoryNavigation.allHref,
								class: { active: __props.categoryNavigation.allHref === (__props.catalogUrl || `/categories/${__props.category.slug}`) }
							}, {
								default: withCtx(() => [createTextVNode("Усі товари")]),
								_: 1
							}, 8, ["href", "class"]),
							__props.categoryNavigation.root ? (openBlock(), createBlock(unref(Link), {
								key: 0,
								href: `/categories/${__props.categoryNavigation.root.slug}`,
								class: { active: __props.category.slug === __props.categoryNavigation.root.slug || __props.category.parent_id === __props.categoryNavigation.root.id }
							}, {
								default: withCtx(() => [createTextVNode(toDisplayString(__props.categoryNavigation.root.name), 1)]),
								_: 1
							}, 8, ["href", "class"])) : createCommentVNode("", true),
							(openBlock(true), createBlock(Fragment, null, renderList(__props.categoryNavigation.items, (item) => {
								return openBlock(), createBlock(unref(Link), {
									key: item.id,
									href: `/categories/${item.slug}`,
									class: { active: item.slug === __props.category.slug }
								}, {
									default: withCtx(() => [createTextVNode(toDisplayString(item.name), 1)]),
									_: 2
								}, 1032, ["href", "class"]);
							}), 128))
						])) : createCommentVNode("", true),
						createVNode("div", { class: "catalog-tools" }, [
							createVNode("div", {
								class: "catalog-view-switcher",
								role: "group",
								"aria-label": "Вигляд товарів"
							}, [createVNode("button", {
								type: "button",
								class: ["catalog-view-button", { active: catalogColumns.value === 1 }],
								"aria-pressed": catalogColumns.value === 1,
								"aria-label": "Один товар у ряд",
								onClick: ($event) => setCatalogColumns(1)
							}, [createVNode("span", {
								class: "view-icon view-icon-one",
								"aria-hidden": "true"
							})], 10, ["aria-pressed", "onClick"]), createVNode("button", {
								type: "button",
								class: ["catalog-view-button", { active: catalogColumns.value === 2 }],
								"aria-pressed": catalogColumns.value === 2,
								"aria-label": "Два товари в ряд",
								onClick: ($event) => setCatalogColumns(2)
							}, [createVNode("span", {
								class: "view-icon view-icon-two",
								"aria-hidden": "true"
							}, [createVNode("i"), createVNode("i")])], 10, ["aria-pressed", "onClick"])]),
							createVNode("span", null, toDisplayString(__props.productTotal ?? __props.products.length) + " товарів", 1),
							createVNode("button", { onClick: ($event) => filtersOpen.value = true }, [createVNode("span", {
								class: "filter-icon",
								"aria-hidden": "true"
							}, "☷"), createTextVNode(" Фільтр (" + toDisplayString(activeControlCount.value) + ")", 1)], 8, ["onClick"])
						]),
						createVNode("section", { class: "page-head catalog-head" }, [createVNode("h1", null, toDisplayString(__props.searchQuery ? "Результати пошуку" : __props.category.name), 1), __props.searchQuery ? (openBlock(), createBlock("p", { key: 0 }, "За запитом «" + toDisplayString(__props.searchQuery) + "» знайдено: " + toDisplayString(__props.productTotal ?? __props.products.length), 1)) : createCommentVNode("", true)]),
						(openBlock(), createBlock(Teleport, { to: "body" }, [filtersOpen.value ? (openBlock(), createBlock("div", {
							key: 0,
							class: "filter-overlay",
							onClick: withModifiers(($event) => filtersOpen.value = false, ["self"])
						}, [createVNode("aside", {
							class: "catalog-filters",
							"aria-label": "Фільтри каталогу"
						}, [
							createVNode("header", null, [createVNode("strong", null, "Фільтри"), createVNode("button", {
								type: "button",
								"aria-label": "Закрити",
								onClick: ($event) => filtersOpen.value = false
							}, "×", 8, ["onClick"])]),
							(openBlock(true), createBlock(Fragment, null, renderList(__props.filters, (filter) => {
								return openBlock(), createBlock("fieldset", { key: filter.id }, [createVNode("legend", null, toDisplayString(filter.name), 1), (openBlock(true), createBlock(Fragment, null, renderList(filter.values, (value) => {
									return openBlock(), createBlock("label", { key: value.id }, [
										withDirectives(createVNode("input", {
											"onUpdate:modelValue": ($event) => selected.value[filter.slug] = $event,
											type: "checkbox",
											value: value.slug
										}, null, 8, ["onUpdate:modelValue", "value"]), [[vModelCheckbox, selected.value[filter.slug]]]),
										value.color_hex ? (openBlock(), createBlock("i", {
											key: 0,
											style: { background: value.color_hex }
										}, null, 4)) : createCommentVNode("", true),
										createTextVNode(toDisplayString(value.value), 1)
									]);
								}), 128))]);
							}), 128)),
							createVNode("fieldset", { class: "price-filter" }, [createVNode("legend", null, "Ціна, грн"), createVNode("div", null, [
								createVNode("label", null, [createTextVNode("від "), withDirectives(createVNode("input", {
									"onUpdate:modelValue": ($event) => priceFrom.value = $event,
									type: "number",
									min: __props.catalogControls?.priceMin,
									max: priceTo.value
								}, null, 8, [
									"onUpdate:modelValue",
									"min",
									"max"
								]), [[
									vModelText,
									priceFrom.value,
									void 0,
									{ number: true }
								]])]),
								createVNode("span", null, "—"),
								createVNode("label", null, [createTextVNode("до "), withDirectives(createVNode("input", {
									"onUpdate:modelValue": ($event) => priceTo.value = $event,
									type: "number",
									min: priceFrom.value,
									max: __props.catalogControls?.priceMax
								}, null, 8, [
									"onUpdate:modelValue",
									"min",
									"max"
								]), [[
									vModelText,
									priceTo.value,
									void 0,
									{ number: true }
								]])])
							])]),
							createVNode("fieldset", null, [
								createVNode("legend", null, "Наявність"),
								createVNode("label", null, [withDirectives(createVNode("input", {
									"onUpdate:modelValue": ($event) => availability.value = $event,
									type: "radio",
									value: "in_stock"
								}, null, 8, ["onUpdate:modelValue"]), [[vModelRadio, availability.value]]), createTextVNode("В наявності")]),
								createVNode("label", null, [withDirectives(createVNode("input", {
									"onUpdate:modelValue": ($event) => availability.value = $event,
									type: "radio",
									value: "preorder"
								}, null, 8, ["onUpdate:modelValue"]), [[vModelRadio, availability.value]]), createTextVNode("Під замовлення")])
							]),
							createVNode("fieldset", null, [
								createVNode("legend", null, "Сортувати"),
								createVNode("label", null, [withDirectives(createVNode("input", {
									"onUpdate:modelValue": ($event) => sort.value = $event,
									type: "radio",
									value: "manual"
								}, null, 8, ["onUpdate:modelValue"]), [[vModelRadio, sort.value]]), createTextVNode("Порядок з адмінки")]),
								createVNode("label", null, [withDirectives(createVNode("input", {
									"onUpdate:modelValue": ($event) => sort.value = $event,
									type: "radio",
									value: "newest"
								}, null, 8, ["onUpdate:modelValue"]), [[vModelRadio, sort.value]]), createTextVNode("За новизною")]),
								createVNode("label", null, [withDirectives(createVNode("input", {
									"onUpdate:modelValue": ($event) => sort.value = $event,
									type: "radio",
									value: "price_asc"
								}, null, 8, ["onUpdate:modelValue"]), [[vModelRadio, sort.value]]), createTextVNode("Ціна: від нижчої")]),
								createVNode("label", null, [withDirectives(createVNode("input", {
									"onUpdate:modelValue": ($event) => sort.value = $event,
									type: "radio",
									value: "price_desc"
								}, null, 8, ["onUpdate:modelValue"]), [[vModelRadio, sort.value]]), createTextVNode("Ціна: від вищої")])
							]),
							createVNode("div", { class: "filter-actions" }, [createVNode("button", {
								class: "button",
								onClick: applyFilters
							}, "Показати товари"), createVNode("button", {
								class: "link",
								onClick: clearFilters
							}, "Очистити все")])
						])], 8, ["onClick"])) : createCommentVNode("", true)])),
						__props.products.length ? (openBlock(), createBlock("section", {
							key: 1,
							class: ["product-catalog", `catalog-columns-${catalogColumns.value}`]
						}, [(openBlock(true), createBlock(Fragment, null, renderList(__props.products, (product) => {
							return openBlock(), createBlock("article", {
								key: product.id,
								class: "catalog-card"
							}, [createVNode(unref(Link), {
								href: `/products/${product.slug}`,
								class: "catalog-card-link",
								onClick: ($event) => openProduct($event, product)
							}, {
								default: withCtx(() => [
									createVNode("div", {
										class: "catalog-image",
										onTouchstartPassive: ($event) => startProductSwipe($event, product),
										onTouchmove: ($event) => moveProductSwipe($event, product),
										onTouchend: ($event) => endProductSwipe($event, product),
										onTouchcancel: ($event) => cancelProductSwipe(product)
									}, [
										createVNode("div", {
											class: ["catalog-image-track", { "is-dragging": draggingSlides.value[product.id] }],
											style: slideStyle(product)
										}, [(openBlock(true), createBlock(Fragment, null, renderList(mediaItems(product), (item, index) => {
											return openBlock(), createBlock(Fragment, { key: `${item.type}-${item.url}` }, [item.type === "image" ? (openBlock(), createBlock("img", {
												key: 0,
												src: asset(item.url),
												alt: index === 0 ? product.name : `${product.name}, фото ${index + 1}`,
												loading: "lazy",
												draggable: "false"
											}, null, 8, ["src", "alt"])) : (openBlock(), createBlock("video", {
												key: 1,
												src: asset(item.url),
												poster: item.poster_url ? asset(item.poster_url) : void 0,
												muted: "",
												autoplay: "",
												loop: "",
												playsinline: "",
												disablepictureinpicture: "",
												disableremoteplayback: "",
												preload: "metadata",
												tabindex: "-1"
											}, null, 8, ["src", "poster"]))], 64);
										}), 128))], 6),
										catalogBadges(product).length ? (openBlock(), createBlock("div", {
											key: 0,
											class: "catalog-badges"
										}, [(openBlock(true), createBlock(Fragment, null, renderList(catalogBadges(product), (badge) => {
											return openBlock(), createBlock("span", {
												key: `${badge.type}-${badge.label}`,
												class: ["catalog-badge", `catalog-badge-${badge.type}`]
											}, toDisplayString(badge.label), 3);
										}), 128))])) : createCommentVNode("", true),
										mediaItems(product).length > 1 ? (openBlock(), createBlock("div", {
											key: 1,
											class: "catalog-image-dots",
											"aria-label": `${mediaItems(product).length} медіафайлів`
										}, [(openBlock(true), createBlock(Fragment, null, renderList(mediaItems(product), (_, index) => {
											return openBlock(), createBlock("span", {
												key: index,
												class: { active: index === activeSlide(product) }
											}, null, 2);
										}), 128))], 8, ["aria-label"])) : createCommentVNode("", true)
									], 40, [
										"onTouchstartPassive",
										"onTouchmove",
										"onTouchend",
										"onTouchcancel"
									]),
									createVNode("h3", null, toDisplayString(product.name), 1),
									createVNode("p", { class: "catalog-price" }, [originalPrice(product) ? (openBlock(), createBlock("del", { key: 0 }, toDisplayString((originalPrice(product) / 100).toLocaleString("uk-UA")) + " ₴", 1)) : createCommentVNode("", true), createVNode("span", null, toDisplayString((price(product) / 100).toLocaleString("uk-UA")) + " ₴", 1)])
								]),
								_: 2
							}, 1032, ["href", "onClick"]), createVNode("button", {
								type: "button",
								class: "catalog-add-button",
								disabled: !availableVariant(product) || addingProduct.value === product.id,
								onClick: ($event) => addToCart(product)
							}, toDisplayString(availableVariant(product) ? addingProduct.value === product.id ? "Додаємо…" : "Додати в кошик" : "Немає в наявності"), 9, ["disabled", "onClick"])]);
						}), 128))], 2)) : createCommentVNode("", true),
						__props.pagination && __props.pagination.lastPage > 1 ? (openBlock(), createBlock("nav", {
							key: 2,
							class: "catalog-pagination",
							"aria-label": "Сторінки каталогу"
						}, [
							visiblePages.value[0] > 1 ? (openBlock(), createBlock(Fragment, { key: 0 }, [createVNode(unref(Link), {
								href: __props.pagination.pageUrls?.[1] || "?page=1",
								class: "pagination-number"
							}, {
								default: withCtx(() => [createTextVNode("1")]),
								_: 1
							}, 8, ["href"]), visiblePages.value[0] > 2 ? (openBlock(), createBlock("span", {
								key: 0,
								class: "pagination-ellipsis"
							}, "…")) : createCommentVNode("", true)], 64)) : createCommentVNode("", true),
							(openBlock(true), createBlock(Fragment, null, renderList(visiblePages.value, (page) => {
								return openBlock(), createBlock(unref(Link), {
									key: page,
									href: __props.pagination.pageUrls?.[page] || `?page=${page}`,
									class: ["pagination-number", { active: page === __props.pagination.currentPage }],
									"aria-current": page === __props.pagination.currentPage ? "page" : void 0
								}, {
									default: withCtx(() => [createTextVNode(toDisplayString(page), 1)]),
									_: 2
								}, 1032, [
									"href",
									"class",
									"aria-current"
								]);
							}), 128)),
							visiblePages.value[visiblePages.value.length - 1] < __props.pagination.lastPage ? (openBlock(), createBlock(Fragment, { key: 1 }, [visiblePages.value[visiblePages.value.length - 1] < __props.pagination.lastPage - 1 ? (openBlock(), createBlock("span", {
								key: 0,
								class: "pagination-ellipsis"
							}, "…")) : createCommentVNode("", true), createVNode(unref(Link), {
								href: __props.pagination.pageUrls?.[__props.pagination.lastPage] || `?page=${__props.pagination.lastPage}`,
								class: "pagination-number"
							}, {
								default: withCtx(() => [createTextVNode(toDisplayString(__props.pagination.lastPage), 1)]),
								_: 1
							}, 8, ["href"])], 64)) : createCommentVNode("", true),
							__props.pagination.nextUrl ? (openBlock(), createBlock(unref(Link), {
								key: 2,
								href: __props.pagination.nextUrl,
								class: "pagination-arrow",
								"aria-label": "Наступна сторінка"
							}, {
								default: withCtx(() => [createTextVNode("→")]),
								_: 1
							}, 8, ["href"])) : createCommentVNode("", true)
						])) : (openBlock(), createBlock("section", {
							key: 3,
							class: "empty-category"
						}, "За вибраними фільтрами товарів не знайдено."))
					];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/js/Pages/Category.vue
var _sfc_setup = Category_vue_vue_type_script_setup_true_lang_default.setup;
Category_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Category.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Category_default = Category_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { Category_default as default };
