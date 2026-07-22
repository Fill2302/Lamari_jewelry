import { t as StoreLayout_default } from "./StoreLayout-3_rn2Txl.js";
import { createTextVNode, createVNode, defineComponent, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderComponent } from "vue/server-renderer";
//#region resources/js/Pages/ThankYou.vue?vue&type=script&setup=true&lang.ts
var ThankYou_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "ThankYou",
	__ssrInlineRender: true,
	props: { order: {} },
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Замовлення створено" }, null, _parent));
			_push(ssrRenderComponent(StoreLayout_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<section class="payment"${_scopeId}><p class="eyebrow"${_scopeId}>ДЯКУЄМО</p><h1${_scopeId}>Замовлення прийнято</h1><p${_scopeId}>Номер: <b${_scopeId}>${ssrInterpolate(__props.order.number)}</b></p><p${_scopeId}>Статус оплати: ${ssrInterpolate(__props.order.payment_status)}</p><p class="muted"${_scopeId}>Після замовлення тут можна запропонувати створити акаунт.</p>`);
						_push(ssrRenderComponent(unref(Link), {
							href: "/",
							class: "button"
						}, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) _push(`Повернутися до каталогу`);
								else return [createTextVNode("Повернутися до каталогу")];
							}),
							_: 1
						}, _parent, _scopeId));
						_push(`</section>`);
					} else return [createVNode("section", { class: "payment" }, [
						createVNode("p", { class: "eyebrow" }, "ДЯКУЄМО"),
						createVNode("h1", null, "Замовлення прийнято"),
						createVNode("p", null, [createTextVNode("Номер: "), createVNode("b", null, toDisplayString(__props.order.number), 1)]),
						createVNode("p", null, "Статус оплати: " + toDisplayString(__props.order.payment_status), 1),
						createVNode("p", { class: "muted" }, "Після замовлення тут можна запропонувати створити акаунт."),
						createVNode(unref(Link), {
							href: "/",
							class: "button"
						}, {
							default: withCtx(() => [createTextVNode("Повернутися до каталогу")]),
							_: 1
						})
					])];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/js/Pages/ThankYou.vue
var _sfc_setup = ThankYou_vue_vue_type_script_setup_true_lang_default.setup;
ThankYou_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/ThankYou.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var ThankYou_default = ThankYou_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { ThankYou_default as default };
