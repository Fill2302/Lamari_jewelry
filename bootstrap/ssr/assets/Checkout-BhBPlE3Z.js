import { t as StoreLayout_default } from "./StoreLayout-DpbhNzPq.js";
import { Fragment, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, openBlock, ref, renderList, toDisplayString, unref, useSSRContext, vModelText, watch, withCtx, withDirectives, withModifiers } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrRenderAttr, ssrRenderClass, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
//#region resources/js/Pages/Checkout.vue?vue&type=script&setup=true&lang.ts
var phoneError = "Введіть повний номер у форматі +38 0XX XXX XX XX";
var Checkout_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Checkout",
	__ssrInlineRender: true,
	props: {
		items: {},
		total: {}
	},
	setup(__props) {
		const form = useForm({
			customer_name: "",
			email: "",
			phone: "+38",
			city: "",
			city_ref: "",
			warehouse: "",
			warehouse_ref: ""
		});
		const quickCities = [
			"Київ",
			"Харків",
			"Одеса",
			"Львів"
		];
		const citySuggestions = ref([]);
		const warehouseSuggestions = ref([]);
		const cityLoading = ref(false);
		const warehouseLoading = ref(false);
		const cityOpen = ref(false);
		const warehouseOpen = ref(false);
		let cityTimer;
		let warehouseTimer;
		async function loadCities(query) {
			if (query.trim().length < 2) {
				citySuggestions.value = [];
				return;
			}
			cityLoading.value = true;
			try {
				const response = await fetch(`/api/delivery/nova-poshta/cities?q=${encodeURIComponent(query.trim())}`);
				const payload = await response.json();
				citySuggestions.value = response.ok ? payload.data : [];
				cityOpen.value = true;
			} finally {
				cityLoading.value = false;
			}
		}
		async function chooseQuickCity(name) {
			await loadCities(name);
			const city = citySuggestions.value.find((item) => item.name === name) || citySuggestions.value[0];
			if (city) chooseCity(city);
		}
		function chooseCity(city) {
			form.city = city.name;
			form.city_ref = city.ref;
			form.warehouse = "";
			form.warehouse_ref = "";
			cityOpen.value = false;
			citySuggestions.value = [];
			form.clearErrors("city", "city_ref");
		}
		function chooseWarehouse(warehouse) {
			form.warehouse = warehouse.name;
			form.warehouse_ref = warehouse.ref;
			warehouseOpen.value = false;
			warehouseSuggestions.value = [];
			form.clearErrors("warehouse", "warehouse_ref");
		}
		watch(() => form.city, (value) => {
			if (form.city_ref) return;
			clearTimeout(cityTimer);
			cityTimer = setTimeout(() => loadCities(value), 300);
		});
		watch(() => form.warehouse, (value) => {
			if (!form.city_ref || form.warehouse_ref) return;
			clearTimeout(warehouseTimer);
			warehouseTimer = setTimeout(async () => {
				warehouseLoading.value = true;
				try {
					const params = new URLSearchParams({
						city_ref: form.city_ref,
						q: value.trim()
					});
					const response = await fetch(`/api/delivery/nova-poshta/warehouses?${params}`);
					const payload = await response.json();
					warehouseSuggestions.value = response.ok ? payload.data : [];
					warehouseOpen.value = true;
				} finally {
					warehouseLoading.value = false;
				}
			}, 300);
		});
		function onCityInput() {
			form.city_ref = "";
			form.warehouse = "";
			form.warehouse_ref = "";
			form.clearErrors("city", "city_ref");
		}
		function onWarehouseInput() {
			form.warehouse_ref = "";
			form.clearErrors("warehouse", "warehouse_ref");
		}
		function formatPhone(value) {
			let digits = value.replace(/\D/g, "");
			if (digits.startsWith("38")) digits = digits.slice(2);
			const local = digits.slice(0, 10);
			const groups = [
				local.slice(0, 3),
				local.slice(3, 6),
				local.slice(6, 8),
				local.slice(8, 10)
			].filter(Boolean);
			return `+38${groups.length ? ` ${groups.join(" ")}` : ""}`;
		}
		function onPhoneInput(event) {
			form.phone = formatPhone(event.target.value);
			form.clearErrors("phone");
		}
		function submit() {
			form.phone = formatPhone(form.phone);
			if (!/^\+38 0\d{2} \d{3} \d{2} \d{2}$/.test(form.phone)) {
				form.setError("phone", phoneError);
				return;
			}
			if (!form.city_ref) {
				form.setError("city", "Оберіть місто зі списку Нової пошти.");
				return;
			}
			if (!form.warehouse_ref) {
				form.setError("warehouse", "Оберіть відділення або поштомат зі списку.");
				return;
			}
			form.post("/checkout");
		}
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Оформлення замовлення" }, null, _parent));
			_push(ssrRenderComponent(StoreLayout_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<section class="checkout-page"${_scopeId}><header class="checkout-heading"${_scopeId}><p class="eyebrow"${_scopeId}>ВАШЕ ЗАМОВЛЕННЯ</p><h1${_scopeId}>Оформлення замовлення</h1><p${_scopeId}>Заповніть контактні дані — ми зв’яжемося з вами для підтвердження.</p></header><form class="checkout"${_scopeId}><div class="checkout-fields"${_scopeId}><label${_scopeId}> Ім’я та прізвище <input${ssrRenderAttr("value", unref(form).customer_name)} autocomplete="name" required${_scopeId}>`);
						if (unref(form).errors.customer_name) _push(`<small${_scopeId}>${ssrInterpolate(unref(form).errors.customer_name)}</small>`);
						else _push(`<!---->`);
						_push(`</label><label${_scopeId}> Номер телефону <input${ssrRenderAttr("value", unref(form).phone)} class="${ssrRenderClass({ "is-invalid": unref(form).errors.phone })}" type="tel" inputmode="numeric" autocomplete="tel" maxlength="17" aria-describedby="phone-error"${ssrRenderAttr("aria-invalid", Boolean(unref(form).errors.phone))} required${_scopeId}>`);
						if (unref(form).errors.phone) _push(`<small id="phone-error"${_scopeId}>${ssrInterpolate(unref(form).errors.phone)}</small>`);
						else _push(`<!---->`);
						_push(`</label><label${_scopeId}> Email <input${ssrRenderAttr("value", unref(form).email)} type="email" inputmode="email" autocomplete="email" required${_scopeId}>`);
						if (unref(form).errors.email) _push(`<small${_scopeId}>${ssrInterpolate(unref(form).errors.email)}</small>`);
						else _push(`<!---->`);
						_push(`</label><label class="checkout-autocomplete"${_scopeId}> Місто <div class="quick-cities" aria-label="Популярні міста"${_scopeId}><!--[-->`);
						ssrRenderList(quickCities, (city) => {
							_push(`<button type="button"${_scopeId}>${ssrInterpolate(city)}</button>`);
						});
						_push(`<!--]--></div><input${ssrRenderAttr("value", unref(form).city)} autocomplete="off" placeholder="Почніть вводити назву міста" required${_scopeId}>`);
						if (cityLoading.value) _push(`<span class="field-status"${_scopeId}>Шукаємо місто…</span>`);
						else _push(`<!---->`);
						if (cityOpen.value && citySuggestions.value.length) {
							_push(`<ul class="autocomplete-list"${_scopeId}><!--[-->`);
							ssrRenderList(citySuggestions.value, (city) => {
								_push(`<li${_scopeId}><button type="button"${_scopeId}><strong${_scopeId}>${ssrInterpolate(city.name)}</strong><small${_scopeId}>${ssrInterpolate([city.type, city.area && `${city.area} обл.`].filter(Boolean).join(", "))}</small></button></li>`);
							});
							_push(`<!--]--></ul>`);
						} else _push(`<!---->`);
						if (unref(form).errors.city) _push(`<small${_scopeId}>${ssrInterpolate(unref(form).errors.city)}</small>`);
						else _push(`<!---->`);
						_push(`</label><label class="checkout-address checkout-autocomplete"${_scopeId}> Відділення або поштомат Нової пошти <input${ssrRenderAttr("value", unref(form).warehouse)} autocomplete="off"${ssrIncludeBooleanAttr(!unref(form).city_ref) ? " disabled" : ""}${ssrRenderAttr("placeholder", unref(form).city_ref ? "Введіть номер або адресу" : "Спочатку оберіть місто")} required${_scopeId}>`);
						if (warehouseLoading.value) _push(`<span class="field-status"${_scopeId}>Шукаємо відділення…</span>`);
						else _push(`<!---->`);
						if (warehouseOpen.value && warehouseSuggestions.value.length) {
							_push(`<ul class="autocomplete-list"${_scopeId}><!--[-->`);
							ssrRenderList(warehouseSuggestions.value, (warehouse) => {
								_push(`<li${_scopeId}><button type="button"${_scopeId}><strong${_scopeId}>${ssrInterpolate(warehouse.name)}</strong>`);
								if (warehouse.address) _push(`<small${_scopeId}>${ssrInterpolate(warehouse.address)}</small>`);
								else _push(`<!---->`);
								_push(`</button></li>`);
							});
							_push(`<!--]--></ul>`);
						} else _push(`<!---->`);
						if (unref(form).errors.warehouse) _push(`<small${_scopeId}>${ssrInterpolate(unref(form).errors.warehouse)}</small>`);
						else _push(`<!---->`);
						_push(`</label></div><aside class="checkout-summary"${_scopeId}><h2${_scopeId}>Разом</h2><div class="checkout-summary-row"${_scopeId}><span${_scopeId}>Товарів: ${ssrInterpolate(__props.items.reduce((sum, item) => sum + item.quantity, 0))}</span><b${_scopeId}>${ssrInterpolate((__props.total / 100).toLocaleString("uk-UA"))} ₴</b></div><p${_scopeId}>Вартість доставки буде розрахована під час підтвердження замовлення.</p><button class="button"${ssrIncludeBooleanAttr(unref(form).processing) ? " disabled" : ""}${_scopeId}>${ssrInterpolate(unref(form).processing ? "Оформлюємо…" : "Підтвердити замовлення")}</button></aside></form></section>`);
					} else return [createVNode("section", { class: "checkout-page" }, [createVNode("header", { class: "checkout-heading" }, [
						createVNode("p", { class: "eyebrow" }, "ВАШЕ ЗАМОВЛЕННЯ"),
						createVNode("h1", null, "Оформлення замовлення"),
						createVNode("p", null, "Заповніть контактні дані — ми зв’яжемося з вами для підтвердження.")
					]), createVNode("form", {
						class: "checkout",
						onSubmit: withModifiers(submit, ["prevent"])
					}, [createVNode("div", { class: "checkout-fields" }, [
						createVNode("label", null, [
							createTextVNode(" Ім’я та прізвище "),
							withDirectives(createVNode("input", {
								"onUpdate:modelValue": ($event) => unref(form).customer_name = $event,
								autocomplete: "name",
								required: ""
							}, null, 8, ["onUpdate:modelValue"]), [[vModelText, unref(form).customer_name]]),
							unref(form).errors.customer_name ? (openBlock(), createBlock("small", { key: 0 }, toDisplayString(unref(form).errors.customer_name), 1)) : createCommentVNode("", true)
						]),
						createVNode("label", null, [
							createTextVNode(" Номер телефону "),
							createVNode("input", {
								value: unref(form).phone,
								class: { "is-invalid": unref(form).errors.phone },
								type: "tel",
								inputmode: "numeric",
								autocomplete: "tel",
								maxlength: "17",
								"aria-describedby": "phone-error",
								"aria-invalid": Boolean(unref(form).errors.phone),
								required: "",
								onInput: onPhoneInput,
								onFocus: onPhoneInput
							}, null, 42, ["value", "aria-invalid"]),
							unref(form).errors.phone ? (openBlock(), createBlock("small", {
								key: 0,
								id: "phone-error"
							}, toDisplayString(unref(form).errors.phone), 1)) : createCommentVNode("", true)
						]),
						createVNode("label", null, [
							createTextVNode(" Email "),
							withDirectives(createVNode("input", {
								"onUpdate:modelValue": ($event) => unref(form).email = $event,
								type: "email",
								inputmode: "email",
								autocomplete: "email",
								required: ""
							}, null, 8, ["onUpdate:modelValue"]), [[vModelText, unref(form).email]]),
							unref(form).errors.email ? (openBlock(), createBlock("small", { key: 0 }, toDisplayString(unref(form).errors.email), 1)) : createCommentVNode("", true)
						]),
						createVNode("label", { class: "checkout-autocomplete" }, [
							createTextVNode(" Місто "),
							createVNode("div", {
								class: "quick-cities",
								"aria-label": "Популярні міста"
							}, [(openBlock(), createBlock(Fragment, null, renderList(quickCities, (city) => {
								return createVNode("button", {
									key: city,
									type: "button",
									onClick: ($event) => chooseQuickCity(city)
								}, toDisplayString(city), 9, ["onClick"]);
							}), 64))]),
							withDirectives(createVNode("input", {
								"onUpdate:modelValue": ($event) => unref(form).city = $event,
								autocomplete: "off",
								placeholder: "Почніть вводити назву міста",
								required: "",
								onInput: onCityInput,
								onFocus: ($event) => cityOpen.value = citySuggestions.value.length > 0
							}, null, 40, ["onUpdate:modelValue", "onFocus"]), [[vModelText, unref(form).city]]),
							cityLoading.value ? (openBlock(), createBlock("span", {
								key: 0,
								class: "field-status"
							}, "Шукаємо місто…")) : createCommentVNode("", true),
							cityOpen.value && citySuggestions.value.length ? (openBlock(), createBlock("ul", {
								key: 1,
								class: "autocomplete-list"
							}, [(openBlock(true), createBlock(Fragment, null, renderList(citySuggestions.value, (city) => {
								return openBlock(), createBlock("li", { key: city.ref }, [createVNode("button", {
									type: "button",
									onClick: ($event) => chooseCity(city)
								}, [createVNode("strong", null, toDisplayString(city.name), 1), createVNode("small", null, toDisplayString([city.type, city.area && `${city.area} обл.`].filter(Boolean).join(", ")), 1)], 8, ["onClick"])]);
							}), 128))])) : createCommentVNode("", true),
							unref(form).errors.city ? (openBlock(), createBlock("small", { key: 2 }, toDisplayString(unref(form).errors.city), 1)) : createCommentVNode("", true)
						]),
						createVNode("label", { class: "checkout-address checkout-autocomplete" }, [
							createTextVNode(" Відділення або поштомат Нової пошти "),
							withDirectives(createVNode("input", {
								"onUpdate:modelValue": ($event) => unref(form).warehouse = $event,
								autocomplete: "off",
								disabled: !unref(form).city_ref,
								placeholder: unref(form).city_ref ? "Введіть номер або адресу" : "Спочатку оберіть місто",
								required: "",
								onInput: onWarehouseInput,
								onFocus: ($event) => warehouseOpen.value = warehouseSuggestions.value.length > 0
							}, null, 40, [
								"onUpdate:modelValue",
								"disabled",
								"placeholder",
								"onFocus"
							]), [[vModelText, unref(form).warehouse]]),
							warehouseLoading.value ? (openBlock(), createBlock("span", {
								key: 0,
								class: "field-status"
							}, "Шукаємо відділення…")) : createCommentVNode("", true),
							warehouseOpen.value && warehouseSuggestions.value.length ? (openBlock(), createBlock("ul", {
								key: 1,
								class: "autocomplete-list"
							}, [(openBlock(true), createBlock(Fragment, null, renderList(warehouseSuggestions.value, (warehouse) => {
								return openBlock(), createBlock("li", { key: warehouse.ref }, [createVNode("button", {
									type: "button",
									onClick: ($event) => chooseWarehouse(warehouse)
								}, [createVNode("strong", null, toDisplayString(warehouse.name), 1), warehouse.address ? (openBlock(), createBlock("small", { key: 0 }, toDisplayString(warehouse.address), 1)) : createCommentVNode("", true)], 8, ["onClick"])]);
							}), 128))])) : createCommentVNode("", true),
							unref(form).errors.warehouse ? (openBlock(), createBlock("small", { key: 2 }, toDisplayString(unref(form).errors.warehouse), 1)) : createCommentVNode("", true)
						])
					]), createVNode("aside", { class: "checkout-summary" }, [
						createVNode("h2", null, "Разом"),
						createVNode("div", { class: "checkout-summary-row" }, [createVNode("span", null, "Товарів: " + toDisplayString(__props.items.reduce((sum, item) => sum + item.quantity, 0)), 1), createVNode("b", null, toDisplayString((__props.total / 100).toLocaleString("uk-UA")) + " ₴", 1)]),
						createVNode("p", null, "Вартість доставки буде розрахована під час підтвердження замовлення."),
						createVNode("button", {
							class: "button",
							disabled: unref(form).processing
						}, toDisplayString(unref(form).processing ? "Оформлюємо…" : "Підтвердити замовлення"), 9, ["disabled"])
					])], 32)])];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/js/Pages/Checkout.vue
var _sfc_setup = Checkout_vue_vue_type_script_setup_true_lang_default.setup;
Checkout_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Checkout.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Checkout_default = Checkout_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { Checkout_default as default };
