import { t as StoreLayout_default } from "./StoreLayout-CSgOPgrw.js";
import { createTextVNode, createVNode, defineComponent, toDisplayString, unref, useSSRContext, vModelText, withCtx, withDirectives, withModifiers } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrRenderAttr, ssrRenderComponent } from "vue/server-renderer";
//#region resources/js/Pages/Checkout.vue?vue&type=script&setup=true&lang.ts
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
			phone: "",
			city: "",
			address: ""
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Оформлення" }, null, _parent));
			_push(ssrRenderComponent(StoreLayout_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<section class="narrow"${_scopeId}><p class="eyebrow"${_scopeId}>GUEST CHECKOUT</p><h1${_scopeId}>Оформлення</h1><form class="checkout"${_scopeId}><label${_scopeId}>Ім’я<input${ssrRenderAttr("value", unref(form).customer_name)} required${_scopeId}></label><label${_scopeId}>Email<input${ssrRenderAttr("value", unref(form).email)} type="email" required${_scopeId}></label><label${_scopeId}>Телефон<input${ssrRenderAttr("value", unref(form).phone)} required${_scopeId}></label><label${_scopeId}>Місто<input${ssrRenderAttr("value", unref(form).city)} required${_scopeId}></label><label${_scopeId}>Адреса / відділення<input${ssrRenderAttr("value", unref(form).address)} required${_scopeId}></label><div class="total"${_scopeId}><span${_scopeId}>До сплати</span><b${_scopeId}>${ssrInterpolate((__props.total / 100).toLocaleString("uk-UA"))} ₴</b></div><button class="button"${ssrIncludeBooleanAttr(unref(form).processing) ? " disabled" : ""}${_scopeId}>Створити тестове замовлення</button></form></section>`);
					else return [createVNode("section", { class: "narrow" }, [
						createVNode("p", { class: "eyebrow" }, "GUEST CHECKOUT"),
						createVNode("h1", null, "Оформлення"),
						createVNode("form", {
							onSubmit: withModifiers(($event) => unref(form).post("/checkout"), ["prevent"]),
							class: "checkout"
						}, [
							createVNode("label", null, [createTextVNode("Ім’я"), withDirectives(createVNode("input", {
								"onUpdate:modelValue": ($event) => unref(form).customer_name = $event,
								required: ""
							}, null, 8, ["onUpdate:modelValue"]), [[vModelText, unref(form).customer_name]])]),
							createVNode("label", null, [createTextVNode("Email"), withDirectives(createVNode("input", {
								"onUpdate:modelValue": ($event) => unref(form).email = $event,
								type: "email",
								required: ""
							}, null, 8, ["onUpdate:modelValue"]), [[vModelText, unref(form).email]])]),
							createVNode("label", null, [createTextVNode("Телефон"), withDirectives(createVNode("input", {
								"onUpdate:modelValue": ($event) => unref(form).phone = $event,
								required: ""
							}, null, 8, ["onUpdate:modelValue"]), [[vModelText, unref(form).phone]])]),
							createVNode("label", null, [createTextVNode("Місто"), withDirectives(createVNode("input", {
								"onUpdate:modelValue": ($event) => unref(form).city = $event,
								required: ""
							}, null, 8, ["onUpdate:modelValue"]), [[vModelText, unref(form).city]])]),
							createVNode("label", null, [createTextVNode("Адреса / відділення"), withDirectives(createVNode("input", {
								"onUpdate:modelValue": ($event) => unref(form).address = $event,
								required: ""
							}, null, 8, ["onUpdate:modelValue"]), [[vModelText, unref(form).address]])]),
							createVNode("div", { class: "total" }, [createVNode("span", null, "До сплати"), createVNode("b", null, toDisplayString((__props.total / 100).toLocaleString("uk-UA")) + " ₴", 1)]),
							createVNode("button", {
								class: "button",
								disabled: unref(form).processing
							}, "Створити тестове замовлення", 8, ["disabled"])
						], 40, ["onSubmit"])
					])];
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
