<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import StoreLayout from '../Layouts/StoreLayout.vue';

const props = defineProps<{ products:any[] }>();
const favoriteIds = ref<number[]>([]);
const loadFavorites = () => {
  try {
    favoriteIds.value = JSON.parse(localStorage.getItem('lamari-favorites') || '[]');
  } catch {
    favoriteIds.value = [];
  }
};
const favoriteProducts = computed(() => props.products.filter(product => favoriteIds.value.includes(product.id)));
const asset = (url:string) => url?.startsWith('http') ? url : `/storage/${url}`;
const image = (product:any) => asset(product.media?.find((item:any) => item.type === 'image')?.url || product.image_url);
const price = (product:any) => product.variants?.[0]?.effective_price_amount ?? product.variants?.[0]?.price_amount ?? 0;
const removeFavorite = (productId:number) => {
  const next = favoriteIds.value.filter(id => id !== productId);
  localStorage.setItem('lamari-favorites', JSON.stringify(next));
  favoriteIds.value = next;
  window.dispatchEvent(new Event('lamari-favorites'));
};
onMounted(() => {
  loadFavorites();
  window.addEventListener('storage', loadFavorites);
  window.addEventListener('lamari-favorites', loadFavorites);
});
onUnmounted(() => {
  window.removeEventListener('storage', loadFavorites);
  window.removeEventListener('lamari-favorites', loadFavorites);
});
</script>

<template>
  <Head><title>Вподобане</title></Head>
  <StoreLayout>
    <section v-if="favoriteProducts.length" class="product-catalog favorites-catalog">
      <article v-for="product in favoriteProducts" :key="product.id" class="catalog-card">
        <Link :href="`/products/${product.slug}`" class="catalog-card-link">
          <div class="catalog-image"><img :src="image(product)" :alt="product.name" loading="lazy"></div>
          <h3>{{ product.name }}</h3>
          <p class="catalog-price"><span>{{ (price(product)/100).toLocaleString('uk-UA') }} ₴</span></p>
        </Link>
        <button type="button" class="catalog-favorite active" aria-label="Видалити з вподобаного" @click="removeFavorite(product.id)">
          <svg viewBox="0 0 24 24"><path d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>
        </button>
      </article>
    </section>
    <section v-else class="empty-category favorites-empty"><h2>Ще немає вподобаних товарів</h2><p>Натисніть серце на товарі, щоб зберегти його тут.</p><Link href="/catalog" class="button">Перейти до каталогу</Link></section>
  </StoreLayout>
</template>
