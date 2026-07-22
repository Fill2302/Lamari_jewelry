<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import StoreLayout from '../Layouts/StoreLayout.vue';
const p = defineProps<{ product: any }>();
const selected = ref(p.product.variants[0]?.id);
const form = useForm({ quantity: 1 });
const add = () => form.post(`/cart/${selected.value}`, { preserveScroll: true });
const asset = (url?: string) => !url ? '' : url.startsWith('http') ? url : `/storage/${url}`;
const media = computed(() => p.product.media?.length ? p.product.media : [{ type: 'image', url: p.product.image_url, alt: p.product.name }]);
const selectedVariant = computed(() => p.product.variants.find((v: any) => v.id === selected.value));
const schema = { '@context': 'https://schema.org', '@type': 'Product', name: p.product.name, image: media.value.filter((m:any)=>m.type==='image').map((m:any)=>asset(m.url)), description: p.product.description, sku: p.product.variants[0]?.sku, offers: { '@type': 'Offer', priceCurrency: 'UAH', price: p.product.variants[0]?.price_amount / 100, availability: 'https://schema.org/InStock' } };
</script>

<template>
  <Head>
    <title>{{ product.seo_title || product.name }}</title>
    <meta name="description" :content="product.seo_description || product.description" />
    <link rel="canonical" :href="`http://localhost/products/${product.slug}`" />
    <meta property="og:type" content="product" /><meta property="og:title" :content="product.name" />
    <component is="script" type="application/ld+json">{{ JSON.stringify(schema) }}</component>
  </Head>
  <StoreLayout>
    <div class="breadcrumbs"><Link href="/">Головна</Link> / <Link :href="`/categories/${product.category.parent?.slug || product.category.slug}`">{{ product.category.parent?.name || product.category.name }}</Link> / {{ product.name }}</div>
    <section class="product-lace">
      <div class="media-grid">
        <figure v-for="item in media" :key="item.id || item.url" :class="{ video: item.type === 'video' }">
          <img v-if="item.type === 'image'" :src="asset(item.url)" :alt="item.alt || product.name" loading="lazy" />
          <video v-else :src="asset(item.url)" :poster="asset(item.poster_url)" controls muted playsinline preload="metadata">Ваш браузер не підтримує відео.</video>
          <span v-if="item.type === 'video'" class="media-label">Відео</span>
        </figure>
      </div>
      <aside class="buy-panel">
        <p class="eyebrow">{{ product.category.name }}</p>
        <h1>{{ product.name }}</h1>
        <p class="sku">Артикул {{ selectedVariant?.sku }} · <span class="in-stock">В наявності</span></p>
        <p class="price">{{ (selectedVariant?.price_amount / 100).toLocaleString('uk-UA') }} ₴</p>
        <label>Оберіть розмір
          <div class="variant-pills"><button v-for="v in product.variants" :key="v.id" :class="{ active: selected === v.id }" @click="selected = v.id">{{ v.name }}</button></div>
        </label>
        <button class="button buy" @click="add" :disabled="form.processing || !selected">Додати в кошик</button>
        <div class="product-benefits"><span>Безкоштовне брендоване пакування</span><span>Відправлення 1–3 робочі дні</span></div>
        <details open><summary>Характеристики</summary><dl><template v-for="(value,key) in product.characteristics"><dt>{{ key }}</dt><dd>{{ value }}</dd></template><dt>Матеріал</dt><dd>{{ product.material }}</dd></dl></details>
        <details><summary>Опис товару</summary><p>{{ product.description }}</p></details>
        <details><summary>Упаковка</summary><p>{{ product.packaging_text }}</p></details>
        <details><summary>Догляд</summary><p>{{ product.care_text }}</p></details>
        <details><summary>Доставка та оплата</summary><p>Доставка Україною та за кордон. Точний спосіб і вартість будуть доступні під час оформлення.</p></details>
      </aside>
    </section>
  </StoreLayout>
</template>
