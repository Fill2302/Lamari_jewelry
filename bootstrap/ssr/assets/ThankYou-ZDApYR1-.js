import { t as StoreLayout_default } from "./StoreLayout-CqSO763L.js";
import { Fragment, createBlock, createTextVNode, createVNode, defineComponent, openBlock, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderComponent } from "vue/server-renderer";
//#region resources/js/Pages/ThankYou.vue?vue&type=script&setup=true&lang.ts
var ThankYou_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "ThankYou",
	__ssrInlineRender: true,
	props: { order: {} },
	setup(__props) {
		const isCashOnDelivery = __props.order.payment_status === "cash_on_delivery";
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: "Замовлення прийнято" }, null, _parent));
			_push(ssrRenderComponent(StoreLayout_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<section class="payment thank-you"${_scopeId}><p class="eyebrow"${_scopeId}>ДЯКУЄМО ЗА ЗАМОВЛЕННЯ</p><h1${_scopeId}>Замовлення прийнято</h1><p class="thank-you-number"${_scopeId}>Номер замовлення: <strong${_scopeId}>${ssrInterpolate(__props.order.number)}</strong></p><div class="thank-you-message"${_scopeId}>`);
						if (isCashOnDelivery) _push(`<!--[--><strong${_scopeId}>Оплата при отриманні</strong><span${_scopeId}>Ми зв’яжемося з вами для підтвердження замовлення та відправлення Новою поштою.</span><!--]-->`);
						else _push(`<!--[--><strong${_scopeId}>Оплату успішно прийнято</strong><span${_scopeId}>Ми повідомимо вам, коли замовлення буде передано до доставки.</span><!--]-->`);
						_push(`</div>`);
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
					} else return [createVNode("section", { class: "payment thank-you" }, [
						createVNode("p", { class: "eyebrow" }, "ДЯКУЄМО ЗА ЗАМОВЛЕННЯ"),
						createVNode("h1", null, "Замовлення прийнято"),
						createVNode("p", { class: "thank-you-number" }, [createTextVNode("Номер замовлення: "), createVNode("strong", null, toDisplayString(__props.order.number), 1)]),
						createVNode("div", { class: "thank-you-message" }, [isCashOnDelivery ? (openBlock(), createBlock(Fragment, { key: 0 }, [createVNode("strong", null, "Оплата при отриманні"), createVNode("span", null, "Ми зв’яжемося з вами для підтвердження замовлення та відправлення Новою поштою.")], 64)) : (openBlock(), createBlock(Fragment, { key: 1 }, [createVNode("strong", null, "Оплату успішно прийнято"), createVNode("span", null, "Ми повідомимо вам, коли замовлення буде передано до доставки.")], 64))]),
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
