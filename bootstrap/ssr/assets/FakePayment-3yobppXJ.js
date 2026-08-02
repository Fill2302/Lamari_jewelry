import { t as StoreLayout_default } from "./StoreLayout-CI3WdeRz.js";
import { createTextVNode, createVNode, defineComponent, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrRenderComponent } from "vue/server-renderer";
//#region resources/js/Pages/FakePayment.vue?vue&type=script&setup=true&lang.ts
var FakePayment_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "FakePayment",
	__ssrInlineRender: true,
	props: { payment: {} },
	setup(__props) {
		const form = useForm({});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Тестова оплата" }, null, _parent));
			_push(ssrRenderComponent(StoreLayout_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<section class="payment"${_scopeId}><p class="eyebrow"${_scopeId}>SANDBOX · FAKE PROVIDER</p><h1${_scopeId}>Тестова оплата</h1><p class="payment-order"${_scopeId}>Замовлення <strong${_scopeId}>${ssrInterpolate(__props.payment.order.number)}</strong></p><div class="pay-card"${_scopeId}><span${_scopeId}>До сплати</span><b${_scopeId}>${ssrInterpolate((__props.payment.amount / 100).toLocaleString("uk-UA"))} ₴</b><button class="button"${ssrIncludeBooleanAttr(unref(form).processing) ? " disabled" : ""}${_scopeId}>${ssrInterpolate(unref(form).processing ? "Обробляємо…" : "Імітувати успішну оплату")}</button></div><small${_scopeId}>Жодні карткові дані не передаються.</small></section>`);
					else return [createVNode("section", { class: "payment" }, [
						createVNode("p", { class: "eyebrow" }, "SANDBOX · FAKE PROVIDER"),
						createVNode("h1", null, "Тестова оплата"),
						createVNode("p", { class: "payment-order" }, [createTextVNode("Замовлення "), createVNode("strong", null, toDisplayString(__props.payment.order.number), 1)]),
						createVNode("div", { class: "pay-card" }, [
							createVNode("span", null, "До сплати"),
							createVNode("b", null, toDisplayString((__props.payment.amount / 100).toLocaleString("uk-UA")) + " ₴", 1),
							createVNode("button", {
								class: "button",
								disabled: unref(form).processing,
								onClick: ($event) => unref(form).post(`/payments/fake/${__props.payment.id}/pay`)
							}, toDisplayString(unref(form).processing ? "Обробляємо…" : "Імітувати успішну оплату"), 9, ["disabled", "onClick"])
						]),
						createVNode("small", null, "Жодні карткові дані не передаються.")
					])];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/js/Pages/FakePayment.vue
var _sfc_setup = FakePayment_vue_vue_type_script_setup_true_lang_default.setup;
FakePayment_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/FakePayment.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var FakePayment_default = FakePayment_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { FakePayment_default as default };
