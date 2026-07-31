import { t as StoreLayout_default } from "./StoreLayout-DpbhNzPq.js";
import { Fragment, createBlock, createCommentVNode, createTextVNode, createVNode, defineComponent, openBlock, renderList, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { ssrInterpolate, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
//#region resources/js/Pages/Information.vue?vue&type=script&setup=true&lang.ts
var Information_vue_vue_type_script_setup_true_lang_default = /*@__PURE__*/ defineComponent({
	__name: "Information",
	__ssrInlineRender: true,
	props: {
		title: {},
		sections: {}
	},
	setup(__props) {
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: __props.title }, null, _parent));
			_push(ssrRenderComponent(StoreLayout_default, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<main class="information-page"${_scopeId}><nav class="information-breadcrumbs" aria-label="Навігація"${_scopeId}>`);
						_push(ssrRenderComponent(unref(Link), { href: "/" }, {
							default: withCtx((_, _push, _parent, _scopeId) => {
								if (_push) _push(`Головна`);
								else return [createTextVNode("Головна")];
							}),
							_: 1
						}, _parent, _scopeId));
						_push(`<span${_scopeId}>/</span><span${_scopeId}>${ssrInterpolate(__props.title)}</span></nav><article class="information-content"${_scopeId}><h1${_scopeId}>${ssrInterpolate(__props.title)}</h1><!--[-->`);
						ssrRenderList(__props.sections, (section, index) => {
							_push(`<section${_scopeId}>`);
							if (section.heading) _push(`<h2${_scopeId}>${ssrInterpolate(section.heading)}</h2>`);
							else _push(`<!---->`);
							_push(`<!--[-->`);
							ssrRenderList(section.paragraphs || [], (paragraph) => {
								_push(`<p${_scopeId}>${ssrInterpolate(paragraph)}</p>`);
							});
							_push(`<!--]-->`);
							if (section.items) {
								_push(`<ol${_scopeId}><!--[-->`);
								ssrRenderList(section.items, (item) => {
									_push(`<li${_scopeId}>${ssrInterpolate(item)}</li>`);
								});
								_push(`<!--]--></ol>`);
							} else _push(`<!---->`);
							_push(`</section>`);
						});
						_push(`<!--]--></article></main>`);
					} else return [createVNode("main", { class: "information-page" }, [createVNode("nav", {
						class: "information-breadcrumbs",
						"aria-label": "Навігація"
					}, [
						createVNode(unref(Link), { href: "/" }, {
							default: withCtx(() => [createTextVNode("Головна")]),
							_: 1
						}),
						createVNode("span", null, "/"),
						createVNode("span", null, toDisplayString(__props.title), 1)
					]), createVNode("article", { class: "information-content" }, [createVNode("h1", null, toDisplayString(__props.title), 1), (openBlock(true), createBlock(Fragment, null, renderList(__props.sections, (section, index) => {
						return openBlock(), createBlock("section", { key: index }, [
							section.heading ? (openBlock(), createBlock("h2", { key: 0 }, toDisplayString(section.heading), 1)) : createCommentVNode("", true),
							(openBlock(true), createBlock(Fragment, null, renderList(section.paragraphs || [], (paragraph) => {
								return openBlock(), createBlock("p", { key: paragraph }, toDisplayString(paragraph), 1);
							}), 128)),
							section.items ? (openBlock(), createBlock("ol", { key: 1 }, [(openBlock(true), createBlock(Fragment, null, renderList(section.items, (item) => {
								return openBlock(), createBlock("li", { key: item }, toDisplayString(item), 1);
							}), 128))])) : createCommentVNode("", true)
						]);
					}), 128))])])];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
		};
	}
});
//#endregion
//#region resources/js/Pages/Information.vue
var _sfc_setup = Information_vue_vue_type_script_setup_true_lang_default.setup;
Information_vue_vue_type_script_setup_true_lang_default.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Information.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Information_default = Information_vue_vue_type_script_setup_true_lang_default;
//#endregion
export { Information_default as default };
