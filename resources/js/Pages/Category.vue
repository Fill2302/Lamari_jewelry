<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import StoreLayout from '../Layouts/StoreLayout.vue';

const props = defineProps<{
  category:any,
  categoryNavigation?:any,
  products:any[],
  productTotal?:number,
  pagination?:{currentPage:number,lastPage:number,prevUrl?:string|null,nextUrl?:string|null,pageUrls?:Record<number,string>},
  filters:any[],
  selectedFilters:Record<string,string[]>,
  catalogControls?:any,
  catalogUrl?:string,
  searchQuery?:string
}>();
const filtersOpen = ref(false);
const catalogColumns = ref<1 | 2>(2);
const favorites = ref<number[]>([]);
const toggleFavorite = (productId:number) => {
  const next = favorites.value.includes(productId)
    ? favorites.value.filter(id => id !== productId)
    : [...favorites.value, productId];
  localStorage.setItem('lamari-favorites', JSON.stringify(next));
  favorites.value = next;
  window.dispatchEvent(new Event('lamari-favorites'));
};
const selected = ref<Record<string,string[]>>(Object.fromEntries(
  (props.filters || []).map((filter:any) => [filter.slug, [...(props.selectedFilters?.[filter.slug] || [])]]),
));
const selectedFilterCount = computed(() => Object.values(selected.value).reduce((total, values) => total + values.length, 0));
const priceFrom = ref(props.catalogControls?.priceFrom || props.catalogControls?.priceMin || 0);
const priceTo = ref(props.catalogControls?.priceTo || props.catalogControls?.priceMax || 0);
const availability = ref(props.catalogControls?.availability || '');
const sort = ref(props.catalogControls?.sort || 'manual');
const activeControlCount = computed(() =>
  selectedFilterCount.value
  + (availability.value ? 1 : 0)
  + (priceFrom.value > (props.catalogControls?.priceMin || 0) ? 1 : 0)
  + (priceTo.value < (props.catalogControls?.priceMax || 0) ? 1 : 0)
);
const visiblePages = computed(() => {
  if (!props.pagination) return [];
  const total = props.pagination.lastPage;
  const windowSize = Math.min(5, total);
  let start = Math.max(1, props.pagination.currentPage - 2);
  start = Math.min(start, total - windowSize + 1);
  return Array.from({ length: windowSize }, (_, index) => start + index);
});
const mediaItems = (product:any) => {
  const media = product.media?.filter((item:any) => item.url && (item.type === 'image' || item.type === 'video')) || [];
  return media.length ? media : [product.image_url ? { type: 'image', url: product.image_url } : null].filter(Boolean);
};
const asset = (url:string) => url?.startsWith('http') ? url : `/storage/${url}`;
const slideIndexes = ref<Record<number, number>>({});
const slideOffsets = ref<Record<number, number>>({});
const draggingSlides = ref<Record<number, boolean>>({});
const touchStarts = new Map<number, { x:number, y:number }>();
const swipeBlockedLinks = new Set<number>();
const activeSlide = (product:any) => slideIndexes.value[product.id] || 0;
const slideStyle = (product:any) => ({
  transform: `translate3d(calc(${-activeSlide(product) * 100}% + ${slideOffsets.value[product.id] || 0}px), 0, 0)`,
});
const startProductSwipe = (event:TouchEvent, product:any) => {
  const touch = event.touches[0];
  if (touch) {
    touchStarts.set(product.id, { x: touch.clientX, y: touch.clientY });
    draggingSlides.value[product.id] = false;
    slideOffsets.value[product.id] = 0;
  }
};
const moveProductSwipe = (event:TouchEvent, product:any) => {
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
  const atEdge = (index === 0 && deltaX > 0) || (index === lastIndex && deltaX < 0);
  slideOffsets.value[product.id] = atEdge ? deltaX * 0.28 : deltaX;
};
const endProductSwipe = (event:TouchEvent, product:any) => {
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
const cancelProductSwipe = (product:any) => {
  touchStarts.delete(product.id);
  draggingSlides.value[product.id] = false;
  slideOffsets.value[product.id] = 0;
};
const openProduct = (event:MouseEvent, product:any) => {
  if (!swipeBlockedLinks.has(product.id)) return;
  event.preventDefault();
  swipeBlockedLinks.delete(product.id);
};
const price = (product:any) => product.variants[0]?.effective_price_amount ?? product.variants[0]?.price_amount ?? 0;
const originalPrice = (product:any) => product.variants[0]?.discount_percentage ? product.variants[0]?.original_price_amount : product.compare_at_price_amount;
const catalogBadges = (product:any) => {
  const badges = Array.isArray(product.catalog_badges) ? [...product.catalog_badges] : [];
  const percentage = Number(product.variants?.[0]?.discount_percentage || 0);

  if (percentage > 0) {
    const label = `-${Math.round(percentage)}%`;
    if (!badges.some((badge:any) => badge.type === 'sale' && badge.label === label)) {
      badges.push({ type: 'sale', label });
    }
  }

  return badges;
};
const availableVariant = (product:any) => product.variants?.find((variant:any) =>
  variant.is_active && variant.stock_on_hand > variant.stock_reserved
);
const addingProduct = ref<number | null>(null);
const addToCart = (product:any) => {
  const variant = availableVariant(product);
  if (!variant || addingProduct.value) return;
  addingProduct.value = product.id;
  router.post(`/cart/${variant.id}`, { quantity: 1 }, {
    preserveScroll: true,
    onFinish: () => addingProduct.value = null,
  });
};
const applyFilters = () => {
  router.get(props.catalogUrl || `/categories/${props.category.slug}`, {
    filters: selected.value,
    price_from: priceFrom.value,
    price_to: priceTo.value,
    availability: availability.value || undefined,
    sort: sort.value,
    q: props.searchQuery || undefined,
  }, { preserveState: true, preserveScroll: true, onSuccess: () => filtersOpen.value = false });
};
const clearFilters = () => {
  selected.value = {};
  priceFrom.value = props.catalogControls?.priceMin || 0;
  priceTo.value = props.catalogControls?.priceMax || 0;
  availability.value = '';
  sort.value = 'manual';
  applyFilters();
};
const setCatalogColumns = (columns: 1 | 2) => {
  catalogColumns.value = columns;
};

onMounted(() => {
  catalogColumns.value = localStorage.getItem('lamari-catalog-columns') === '1' ? 1 : 2;
  try {
    favorites.value = JSON.parse(localStorage.getItem('lamari-favorites') || '[]');
  } catch {
    favorites.value = [];
  }
});

watch(catalogColumns, (columns) => {
  localStorage.setItem('lamari-catalog-columns', String(columns));
});
</script>

<template>
  <Head><title>{{category.seo_title||category.name}}</title><meta name="description" :content="category.seo_description||category.description"/></Head>
  <StoreLayout>
    <nav v-if="categoryNavigation" class="category-scroll" aria-label="Категорії товарів">
      <Link :href="categoryNavigation.allHref" :class="{ active: categoryNavigation.allHref === (catalogUrl || `/categories/${category.slug}`) }">Усі товари</Link>
      <Link
        v-if="categoryNavigation.root"
        :href="`/categories/${categoryNavigation.root.slug}`"
        :class="{ active: category.slug === categoryNavigation.root.slug || category.parent_id === categoryNavigation.root.id }"
      >{{ categoryNavigation.root.name }}</Link>
      <Link
        v-for="item in categoryNavigation.items"
        :key="item.id"
        :href="`/categories/${item.slug}`"
        :class="{ active: item.slug === category.slug }"
      >{{ item.name }}</Link>
    </nav>
    <div class="catalog-tools">
      <div class="catalog-view-switcher" role="group" aria-label="Вигляд товарів">
        <button
          type="button"
          class="catalog-view-button"
          :class="{ active: catalogColumns === 1 }"
          :aria-pressed="catalogColumns === 1"
          aria-label="Один товар у ряд"
          @click="setCatalogColumns(1)"
        ><span class="view-icon view-icon-one" aria-hidden="true"></span></button>
        <button
          type="button"
          class="catalog-view-button"
          :class="{ active: catalogColumns === 2 }"
          :aria-pressed="catalogColumns === 2"
          aria-label="Два товари в ряд"
          @click="setCatalogColumns(2)"
        ><span class="view-icon view-icon-two" aria-hidden="true"><i></i><i></i></span></button>
      </div>
      <span>{{productTotal ?? products.length}} товарів</span>
      <button @click="filtersOpen=true"><span class="filter-icon" aria-hidden="true">☷</span> Фільтр ({{ activeControlCount }})</button>
    </div>
    <section class="page-head catalog-head">
      <h1>{{ searchQuery ? 'Результати пошуку' : category.name }}</h1>
      <p v-if="searchQuery">За запитом «{{ searchQuery }}» знайдено: {{ productTotal ?? products.length }}</p>
    </section>
    <Teleport to="body">
      <div v-if="filtersOpen" class="filter-overlay" @click.self="filtersOpen=false">
        <aside class="catalog-filters" aria-label="Фільтри каталогу">
          <header><strong>Фільтри</strong><button type="button" aria-label="Закрити" @click="filtersOpen=false">×</button></header>
          <fieldset v-for="filter in filters" :key="filter.id">
            <legend>{{filter.name}}</legend>
            <label v-for="value in filter.values" :key="value.id"><input v-model="selected[filter.slug]" type="checkbox" :value="value.slug"><i v-if="value.color_hex" :style="{background:value.color_hex}"></i>{{value.value}}</label>
          </fieldset>
          <fieldset class="price-filter">
            <legend>Ціна, грн</legend>
            <div><label>від <input v-model.number="priceFrom" type="number" :min="catalogControls?.priceMin" :max="priceTo"></label><span>—</span><label>до <input v-model.number="priceTo" type="number" :min="priceFrom" :max="catalogControls?.priceMax"></label></div>
          </fieldset>
          <fieldset>
            <legend>Наявність</legend>
            <label><input v-model="availability" type="radio" value="in_stock">В наявності</label>
            <label><input v-model="availability" type="radio" value="preorder">Під замовлення</label>
          </fieldset>
          <fieldset>
            <legend>Сортувати</legend>
            <label><input v-model="sort" type="radio" value="manual">Порядок з адмінки</label>
            <label><input v-model="sort" type="radio" value="newest">За новизною</label>
            <label><input v-model="sort" type="radio" value="price_asc">Ціна: від нижчої</label>
            <label><input v-model="sort" type="radio" value="price_desc">Ціна: від вищої</label>
          </fieldset>
          <div class="filter-actions"><button class="button" @click="applyFilters">Показати товари</button><button class="link" @click="clearFilters">Очистити все</button></div>
        </aside>
      </div>
    </Teleport>
    <section v-if="products.length" class="product-catalog" :class="`catalog-columns-${catalogColumns}`">
      <article v-for="product in products" :key="product.id" class="catalog-card">
        <Link :href="`/products/${product.slug}`" class="catalog-card-link" @click="openProduct($event, product)">
          <div
            class="catalog-image"
            @touchstart.passive="startProductSwipe($event, product)"
            @touchmove="moveProductSwipe($event, product)"
            @touchend="endProductSwipe($event, product)"
            @touchcancel="cancelProductSwipe(product)"
          >
            <div
              class="catalog-image-track"
              :class="{ 'is-dragging': draggingSlides[product.id] }"
              :style="slideStyle(product)"
            >
              <template v-for="(item, index) in mediaItems(product)" :key="`${item.type}-${item.url}`">
                <img
                  v-if="item.type === 'image'"
                  :src="asset(item.url)"
                  :alt="index === 0 ? product.name : `${product.name}, фото ${index + 1}`"
                  loading="lazy"
                  draggable="false"
                >
                <video
                  v-else
                  :src="asset(item.url)"
                  :poster="item.poster_url ? asset(item.poster_url) : undefined"
                  muted
                  autoplay
                  loop
                  playsinline
                  disablepictureinpicture
                  disableremoteplayback
                  preload="metadata"
                  tabindex="-1"
                ></video>
              </template>
            </div>
            <div v-if="catalogBadges(product).length" class="catalog-badges"><span v-for="badge in catalogBadges(product)" :key="`${badge.type}-${badge.label}`" class="catalog-badge" :class="`catalog-badge-${badge.type}`">{{badge.label}}</span></div>
            <div v-if="mediaItems(product).length > 1" class="catalog-image-dots" :aria-label="`${mediaItems(product).length} медіафайлів`">
              <span
                v-for="(_, index) in mediaItems(product)"
                :key="index"
                :class="{ active: index === activeSlide(product) }"
              ></span>
            </div>
          </div>
          <h3>{{product.name}}</h3>
          <p class="catalog-price"><del v-if="originalPrice(product)">{{(originalPrice(product)/100).toLocaleString('uk-UA')}} ₴</del><span>{{(price(product)/100).toLocaleString('uk-UA')}} ₴</span></p>
        </Link>
        <button
          type="button"
          class="catalog-favorite"
          :class="{ active: favorites.includes(product.id) }"
          :aria-label="favorites.includes(product.id) ? 'Видалити з обраного' : 'Додати в обране'"
          @click="toggleFavorite(product.id)"
        >
          <svg viewBox="0 0 24 24"><path d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>
        </button>
        <button
          type="button"
          class="catalog-add-button"
          :disabled="!availableVariant(product) || addingProduct === product.id"
          @click="addToCart(product)"
        >{{ availableVariant(product) ? (addingProduct === product.id ? 'Додаємо…' : 'Додати в кошик') : 'Немає в наявності' }}</button>
      </article>
    </section>
    <nav v-if="pagination && pagination.lastPage > 1" class="catalog-pagination" aria-label="Сторінки каталогу">
      <template v-if="visiblePages[0] > 1">
        <Link :href="pagination.pageUrls?.[1] || '?page=1'" class="pagination-number">1</Link>
        <span v-if="visiblePages[0] > 2" class="pagination-ellipsis">…</span>
      </template>
      <Link
        v-for="page in visiblePages"
        :key="page"
        :href="pagination.pageUrls?.[page] || `?page=${page}`"
        class="pagination-number"
        :class="{ active: page === pagination.currentPage }"
        :aria-current="page === pagination.currentPage ? 'page' : undefined"
      >{{ page }}</Link>
      <template v-if="visiblePages[visiblePages.length - 1] < pagination.lastPage">
        <span v-if="visiblePages[visiblePages.length - 1] < pagination.lastPage - 1" class="pagination-ellipsis">…</span>
        <Link :href="pagination.pageUrls?.[pagination.lastPage] || `?page=${pagination.lastPage}`" class="pagination-number">{{ pagination.lastPage }}</Link>
      </template>
      <Link v-if="pagination.nextUrl" :href="pagination.nextUrl" class="pagination-arrow" aria-label="Наступна сторінка">→</Link>
    </nav>
    <section v-else class="empty-category">За вибраними фільтрами товарів не знайдено.</section>
  </StoreLayout>
</template>
