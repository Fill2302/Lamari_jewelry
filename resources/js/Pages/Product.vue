<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import StoreLayout from '../Layouts/StoreLayout.vue';
const p = defineProps<{ product: any, recommendedProducts: any[], productCardSetting?: any }>();
const selected = ref(p.product.variants[0]?.id);
const form = useForm({ quantity: 1 });
const add = () => form.post(`/cart/${selected.value}`, { preserveScroll: true });
const asset = (url?: string) => !url ? '' : url.startsWith('http') ? url : `/storage/${url}`;
const media = computed(() => p.product.media?.length ? p.product.media : [{ type: 'image', url: p.product.image_url, alt: p.product.name }]);
const carouselMedia = computed(() => media.value.length > 1
  ? [
      { ...media.value[media.value.length - 1], cloneKey: 'last-clone' },
      ...media.value.map((item: any, index: number) => ({ ...item, cloneKey: `media-${item.id || item.url}-${index}` })),
      { ...media.value[0], cloneKey: 'first-clone' },
    ]
  : media.value.map((item: any) => ({ ...item, cloneKey: `media-${item.id || item.url}` })));
const gallery = ref<HTMLElement | null>(null);
const buyButton = ref<HTMLElement | null>(null);
const showStickyBuy = ref(false);
const sizeGuideOpen = ref(false);
const favorites = ref<number[]>([]);
const isFavorite = computed(() => favorites.value.includes(p.product.id));
const toggleFavorite = (productId = p.product.id) => {
  const next = favorites.value.includes(productId)
    ? favorites.value.filter(id => id !== productId)
    : [...favorites.value, productId];
  localStorage.setItem('lamari-favorites', JSON.stringify(next));
  favorites.value = next;
  window.dispatchEvent(new Event('lamari-favorites'));
};
const relatedImage = (product: any) => asset(product.media?.find((item: any) => item.type === 'image')?.url || product.image_url);
const relatedVariant = (product: any) => product.variants?.find((variant: any) => variant.is_active && variant.stock_on_hand > variant.stock_reserved);
const relatedPrice = (product: any) => relatedVariant(product)?.effective_price_amount ?? relatedVariant(product)?.price_amount ?? 0;
const addRelated = (product: any) => {
  const variant = relatedVariant(product);
  if (variant) router.post(`/cart/${variant.id}`, { quantity: 1 }, { preserveScroll: true });
};
const activeMedia = ref(0);
const zoomKey = ref<string | null>(null);
const zoomScale = ref(1);
const zoomX = ref(0);
const zoomY = ref(0);
let pinchStartDistance = 0;
let pinchStartScale = 1;
let panStartX = 0;
let panStartY = 0;
let panOriginX = 0;
let panOriginY = 0;
const touchDistance = (touches: TouchList) => Math.hypot(
  touches[0].clientX - touches[1].clientX,
  touches[0].clientY - touches[1].clientY,
);
const resetZoom = () => {
  zoomKey.value = null;
  zoomScale.value = 1;
  zoomX.value = 0;
  zoomY.value = 0;
  pinchStartDistance = 0;
};
const imageTransform = (key: string) => key === zoomKey.value
  ? { transform: `translate3d(${zoomX.value}px, ${zoomY.value}px, 0) scale(${zoomScale.value})` }
  : undefined;
const startImageGesture = (event: TouchEvent, key: string) => {
  if (event.touches.length === 2) {
    zoomKey.value = key;
    pinchStartDistance = touchDistance(event.touches);
    pinchStartScale = zoomScale.value;
  } else if (event.touches.length === 1 && zoomKey.value === key && zoomScale.value > 1) {
    panStartX = event.touches[0].clientX;
    panStartY = event.touches[0].clientY;
    panOriginX = zoomX.value;
    panOriginY = zoomY.value;
  }
};
const moveImageGesture = (event: TouchEvent, key: string) => {
  if (zoomKey.value !== key) return;

  if (event.touches.length === 2) {
    event.preventDefault();
    zoomScale.value = Math.min(4, Math.max(1, pinchStartScale * touchDistance(event.touches) / pinchStartDistance));
    if (zoomScale.value === 1) {
      zoomX.value = 0;
      zoomY.value = 0;
    }
  } else if (event.touches.length === 1 && zoomScale.value > 1) {
    event.preventDefault();
    const limitX = gallery.value?.clientWidth ? gallery.value.clientWidth * (zoomScale.value - 1) / 2 : 0;
    const limitY = gallery.value?.clientHeight ? gallery.value.clientHeight * (zoomScale.value - 1) / 2 : 0;
    zoomX.value = Math.min(limitX, Math.max(-limitX, panOriginX + event.touches[0].clientX - panStartX));
    zoomY.value = Math.min(limitY, Math.max(-limitY, panOriginY + event.touches[0].clientY - panStartY));
  }
};
const endImageGesture = (event: TouchEvent) => {
  if (event.touches.length === 0 && zoomScale.value <= 1.02) resetZoom();
  if (event.touches.length === 1 && zoomScale.value > 1) {
    panStartX = event.touches[0].clientX;
    panStartY = event.touches[0].clientY;
    panOriginX = zoomX.value;
    panOriginY = zoomY.value;
  }
};
let scrollTimer: ReturnType<typeof setTimeout> | undefined;
const scrollToPhysical = (index: number, behavior: ScrollBehavior = 'auto') => {
  const element = gallery.value;
  if (!element) return;
  element.scrollTo({ left: index * element.clientWidth, behavior });
};
const goToMedia = (index: number) => {
  resetZoom();
  const count = media.value.length;
  if (count < 2) return;
  if (index < 0) return scrollToPhysical(0, 'smooth');
  if (index >= count) return scrollToPhysical(count + 1, 'smooth');
  scrollToPhysical(index + 1, 'smooth');
};
const updateActiveMedia = () => {
  const element = gallery.value;
  if (!element || media.value.length < 2) return;
  const physicalIndex = Math.round(element.scrollLeft / element.clientWidth);
  if (physicalIndex !== activeMedia.value + 1 && zoomScale.value === 1) resetZoom();
  activeMedia.value = physicalIndex === 0
    ? media.value.length - 1
    : physicalIndex === media.value.length + 1 ? 0 : physicalIndex - 1;
  clearTimeout(scrollTimer);
  scrollTimer = setTimeout(() => {
    const settledIndex = Math.round(element.scrollLeft / element.clientWidth);
    if (settledIndex === 0) scrollToPhysical(media.value.length);
    if (settledIndex === media.value.length + 1) scrollToPhysical(1);
  }, 80);
};
const updateStickyBuy = () => {
  if (!buyButton.value) {
    showStickyBuy.value = false;
    return;
  }

  const buttonTop = buyButton.value.getBoundingClientRect().top;
  const headerBottom = document.querySelector('header')?.getBoundingClientRect().bottom ?? 0;
  showStickyBuy.value = buttonTop <= headerBottom;
};
onMounted(() => {
  try {
    favorites.value = JSON.parse(localStorage.getItem('lamari-favorites') || '[]');
  } catch {
    favorites.value = [];
  }
  nextTick(() => {
    if (media.value.length > 1) scrollToPhysical(1);
    updateStickyBuy();
  });
  window.addEventListener('scroll', updateStickyBuy, { passive: true });
  window.addEventListener('resize', updateStickyBuy);
});
onUnmounted(() => {
  window.removeEventListener('scroll', updateStickyBuy);
  window.removeEventListener('resize', updateStickyBuy);
});
const selectedVariant = computed(() => p.product.variants.find((v: any) => v.id === selected.value));
const displaySku = (variant:any) => /^\d+\s*см$/iu.test(variant?.name || '')
  ? String(variant?.sku || '').replace(/-\d+$/u, '')
  : String(variant?.sku || '');
const productSku = computed(() => displaySku(p.product.variants[0]));
const compareAtPrice = computed(() => Number(selectedVariant.value?.discount_percentage ? selectedVariant.value.original_price_amount : p.product.compare_at_price_amount || 0));
const currentPrice = computed(() => Number(selectedVariant.value?.effective_price_amount ?? selectedVariant.value?.price_amount ?? 0));
const discountLabel = computed(() => {
  if (!compareAtPrice.value || !currentPrice.value || compareAtPrice.value <= currentPrice.value) return '';
  const catalogLabel = p.product.catalog_badges?.find((badge: any) => badge.type === 'sale')?.label;
  return catalogLabel || `-${Math.round((1 - currentPrice.value / compareAtPrice.value) * 100)}%`;
});
const defaultProductCardSetting = {
  characteristics_title: 'Характеристики',
  description_title: 'Опис товару',
  packaging_title: 'Упаковка',
  care_title: 'Догляд',
  care_text: 'Зберігайте прикраси окремо в сухому місці. Уникайте контакту з парфумами, косметикою та побутовою хімією. Після носіння протирайте виріб м’якою сухою серветкою.',
  delivery_payment_title: 'Доставка та оплата',
  delivery_text: 'Доставка по Україні здійснюється Новою поштою. Також доступна міжнародна доставка. Точний спосіб, вартість і термін доставки будуть зазначені під час оформлення замовлення.',
  payment_text: 'Замовлення можна оплатити банківською карткою, через Apple Pay або Google Pay. Також доступні передплата та оплата частинами.',
  warranty_question: 'Яка гарантія на вироби?',
  warranty_answer: 'На всі прикраси LAMARI діє гарантія 1 місяць. Якщо протягом цього часу виявиться виробничий дефект, ми безкоштовно відремонтуємо або замінимо виріб. Гарантія не поширюється на механічні пошкодження та пошкодження через недотримання рекомендацій із догляду.',
  returns_question: 'Чи можу я обміняти або повернути товар?',
  returns_answer: 'Так, ви можете обміняти товар на інший або повернути його протягом 14 днів із моменту отримання.',
  water_question: 'Чи можна мочити прикраси?',
  water_answer: 'Прикраси з ювелірної сталі можна мочити та носити не знімаючи. Прикраси з покриттям золотом або родієм рекомендуємо знімати перед душем, морем чи басейном, щоб вони якомога довше зберігали свій початковий вигляд.',
  tarnish_question: 'Чи темніють прикраси?',
  tarnish_answer: 'Наші прикраси не темніють. За умови дотримання рекомендацій із догляду та правильного зберігання вони довго зберігатимуть свій початковий вигляд.',
};
const productCardSetting = computed(() => ({ ...defaultProductCardSetting, ...(p.productCardSetting || {}) }));
const productFaqs = computed(() => [
  {
    question: productCardSetting.value.warranty_question,
    answer: productCardSetting.value.warranty_answer,
  },
  {
    question: productCardSetting.value.returns_question,
    answer: productCardSetting.value.returns_answer,
  },
  {
    question: productCardSetting.value.water_question,
    answer: productCardSetting.value.water_answer,
  },
  {
    question: productCardSetting.value.tarnish_question,
    answer: productCardSetting.value.tarnish_answer,
  },
]);
const deliveryPaymentText = computed(() => [productCardSetting.value.delivery_text, productCardSetting.value.payment_text]);
const careText = computed(() => p.product.care_text?.trim() || productCardSetting.value.care_text);
const sizeGuideKind = computed(() => {
  if (['necklace', 'bracelet', 'ring'].includes(p.product.size_guide_type)) return p.product.size_guide_type;
  const slug = p.product.category?.parent?.slug || p.product.category?.slug;
  if (['necklaces', 'chokers', 'chains'].includes(slug)) return 'necklace';
  if (['bracelets', 'anklets'].includes(slug)) return 'bracelet';
  if (slug === 'rings') return 'ring';
  return null;
});
const hasSizeVariants = computed(() => p.product.variants.some((variant: any) => /^\d+(?:[.,]\d+)?(?:\s*см)?$/iu.test(variant.name?.trim() || '')));
const showSizeGuide = computed(() => Boolean(p.product.size_guide_type || (sizeGuideKind.value && hasSizeVariants.value)));
const sizeGuideLabel = computed(() => {
  const label = p.product.size_guide_label?.trim() || '';
  return /^Виберіть розмір:?$/iu.test(label) ? '' : label;
});
const sizeGuideContent = computed(() => ({
  necklace: {
    title: 'Як визначити розмір кольє або ланцюжка',
    text: 'Зробити це нескладно в домашніх умовах. Оберніть нитку навколо шиї та зафіксуйте її на потрібному місці. Зробіть позначку й прикладіть нитку до лінійки. Або виміряйте шию впритул сантиметровою стрічкою. До отриманого значення додайте 10 см — так ви отримаєте комфортний розмір кольє.',
    image: '/images/product/necklace-size-guide.jpg',
    alt: 'Як виміряти обхват шиї сантиметровою стрічкою',
  },
  bracelet: {
    title: 'Як визначити розмір браслета',
    text: 'Виміряйте зап’ястя впритул за виступаючою кісточкою сантиметром або звичайною ниткою. Якщо вимірювали ниткою, прикладіть її до лінійки та оберіть відповідний розмір. Необхідний запас ми додамо самі залежно від обраного виробу.',
    image: '/images/product/bracelet-size-guide.jpg',
    alt: 'Як виміряти обхват зап’ястя',
  },
  ring: {
    title: 'Як визначити розмір каблучки',
    text: 'Виміряйте внутрішній діаметр каблучки, яка вам підходить, або обгорніть палець ниткою без надмірного натягу. Виміряйте отриману довжину лінійкою та звірте її з розмірною підказкою.',
    image: '/images/product/ring-size-guide.jpg',
    alt: 'Як визначити розмір каблучки',
  },
}[sizeGuideKind.value || 'necklace']));
const schema = { '@context': 'https://schema.org', '@type': 'Product', name: p.product.name, image: media.value.filter((m:any)=>m.type==='image').map((m:any)=>asset(m.url)), description: p.product.description, sku: productSku.value, offers: { '@type': 'Offer', priceCurrency: 'UAH', price: (p.product.variants[0]?.effective_price_amount ?? p.product.variants[0]?.price_amount) / 100, availability: 'https://schema.org/InStock' } };
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
      <div class="product-gallery">
        <span v-if="discountLabel" class="product-sale-badge">{{ discountLabel }}</span>
        <div ref="gallery" class="media-carousel" :class="{ 'is-image-zoomed': zoomScale > 1 }" @scroll.passive="updateActiveMedia">
        <figure
          v-for="item in carouselMedia"
          :key="item.cloneKey"
          @touchstart="item.type === 'image' && startImageGesture($event, item.cloneKey)"
          @touchmove="item.type === 'image' && moveImageGesture($event, item.cloneKey)"
          @touchend="item.type === 'image' && endImageGesture($event)"
          @touchcancel="resetZoom"
        >
          <img
            v-if="item.type === 'image'"
            :src="asset(item.url)"
            :alt="item.alt || product.name"
            :style="imageTransform(item.cloneKey)"
            loading="lazy"
          />
          <video
            v-else
            :src="asset(item.url)"
            :poster="asset(item.poster_url)"
            muted
            autoplay
            loop
            playsinline
            disablepictureinpicture
            disableremoteplayback
            tabindex="-1"
            preload="auto"
            @contextmenu.prevent
          >Ваш браузер не підтримує відео.</video>
        </figure>
        </div>
        <template v-if="media.length > 1">
          <button class="gallery-arrow gallery-prev" aria-label="Попереднє медіа" @click="goToMedia(activeMedia - 1)">←</button>
          <button class="gallery-arrow gallery-next" aria-label="Наступне медіа" @click="goToMedia(activeMedia + 1)">→</button>
          <div class="gallery-dots"><button v-for="(_,index) in media" :key="index" :class="{active:activeMedia===index}" :aria-label="`Медіа ${index+1}`" @click="goToMedia(index)"></button></div>
        </template>
      </div>
      <aside class="buy-panel">
        <button class="product-favorite" :class="{ active: isFavorite }" :aria-label="isFavorite ? 'Видалити з обраного' : 'Додати в обране'" @click="toggleFavorite()">
          <svg viewBox="0 0 24 24"><path d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>
        </button>
        <h1>{{ product.name }}</h1>
        <p class="sku">Артикул {{ productSku }} · <span class="in-stock">В наявності</span></p>
        <p class="price" :class="{ 'product-sale-price': compareAtPrice }">
          <del v-if="compareAtPrice">{{ (compareAtPrice / 100).toLocaleString('uk-UA') }} ₴</del>
          <span>{{ (currentPrice / 100).toLocaleString('uk-UA') }} ₴</span>
        </p>
        <div v-if="showSizeGuide" class="product-size-guide">
          <p v-if="sizeGuideLabel">{{ sizeGuideLabel }}</p>
          <button type="button" @click="sizeGuideOpen = true">Як визначити розмір</button>
        </div>
        <span class="visually-hidden">Оберіть розмір</span>
        <div class="variant-pills"><button v-for="v in product.variants" :key="v.id" :class="{ active: selected === v.id }" @click="selected = v.id">{{ v.name }}</button></div>
        <button ref="buyButton" class="button buy" @click="add" :disabled="form.processing || !selected">Додати в кошик</button>
        <div class="product-benefits"><span>Безкоштовне брендоване пакування</span><span>Відправлення 1–2 робочі дні</span></div>
        <section v-if="recommendedProducts.length" class="complete-look" aria-labelledby="complete-look-title">
          <h2 id="complete-look-title">Доповнити образ</h2>
          <div class="complete-look-grid">
            <article v-for="item in recommendedProducts" :key="item.id" class="complete-look-card">
              <div class="complete-look-image">
                <Link :href="`/products/${item.slug}`"><img :src="relatedImage(item)" :alt="item.name" loading="lazy"></Link>
                <button type="button" :class="{ active: favorites.includes(item.id) }" :aria-label="favorites.includes(item.id) ? 'Видалити з обраного' : 'Додати в обране'" @click="toggleFavorite(item.id)">
                  <svg viewBox="0 0 24 24"><path d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>
                </button>
              </div>
              <Link :href="`/products/${item.slug}`" class="complete-look-name">{{ item.name }}</Link>
              <p>{{ (relatedPrice(item) / 100).toLocaleString('uk-UA') }} ₴</p>
              <button type="button" class="complete-look-add" :disabled="!relatedVariant(item)" @click="addRelated(item)">Додати в кошик</button>
            </article>
          </div>
        </section>
        <details open><summary>{{ productCardSetting.characteristics_title }}</summary><dl><template v-for="(value,key) in product.characteristics"><dt>{{ key }}</dt><dd>{{ value }}</dd></template><dt>Матеріал</dt><dd>{{ product.material }}</dd></dl></details>
        <details><summary>{{ productCardSetting.description_title }}</summary><p>{{ product.description }}</p></details>
        <details><summary>{{ productCardSetting.packaging_title }}</summary><p>{{ product.packaging_text }}</p><img class="packaging-image" :src="'/images/product/lamari-packaging.webp'" alt="Подарункова брендована упаковка Lamari" loading="lazy"></details>
        <details><summary>{{ productCardSetting.care_title }}</summary><p>{{ careText }}</p></details>
        <details><summary>{{ productCardSetting.delivery_payment_title }}</summary><p v-for="paragraph in deliveryPaymentText" :key="paragraph">{{ paragraph }}</p></details>
        <details v-for="faq in productFaqs" :key="faq.question" class="product-faq"><summary>{{ faq.question }}</summary><p>{{ faq.answer }}</p></details>
      </aside>
    </section>
    <div v-if="showStickyBuy" class="sticky-buy-bar">
      <strong class="sticky-product-price">
        <del v-if="compareAtPrice">{{ (compareAtPrice / 100).toLocaleString('uk-UA') }} ₴</del>
        <span>{{ (currentPrice / 100).toLocaleString('uk-UA') }} ₴</span>
      </strong>
      <button class="button" @click="add" :disabled="form.processing || !selected">Додати в кошик</button>
    </div>
    <Teleport to="body">
      <div v-if="sizeGuideOpen" class="size-guide-overlay" role="presentation" tabindex="-1" autofocus @click.self="sizeGuideOpen = false" @keydown.esc="sizeGuideOpen = false">
        <section class="size-guide-modal" role="dialog" aria-modal="true" aria-labelledby="size-guide-title">
          <button type="button" class="size-guide-close" aria-label="Закрити" @click="sizeGuideOpen = false">×</button>
          <h2 id="size-guide-title">{{ sizeGuideContent.title }}</h2>
          <p>{{ sizeGuideContent.text }}</p>
          <img :src="sizeGuideContent.image" :alt="sizeGuideContent.alt" loading="lazy">
        </section>
      </div>
    </Teleport>
  </StoreLayout>
</template>
