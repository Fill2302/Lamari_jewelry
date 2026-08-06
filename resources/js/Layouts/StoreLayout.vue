<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
const props = withDefaults(defineProps<{ homeOverlay?: boolean }>(), { homeOverlay: false });
const page = usePage<any>();
const menuOpen = ref(false);
const searchOpen = ref(false);
const searchQuery = ref('');
const searchInput = ref<HTMLInputElement | null>(null);
const expandedCategories = ref<number[]>([]);
const cartOpen = ref(Boolean(page.props.flash?.cartOpen));
const favoriteCount = ref(0);
const ticker = ref<HTMLElement | null>(null);
const headerPinned = ref(false);
const headerViewportOffset = ref(0);
const updateHeaderPosition = () => {
  headerPinned.value = window.innerWidth <= 800
    && window.scrollY >= (ticker.value?.offsetHeight || 0);
  headerViewportOffset.value = headerPinned.value
    ? Math.max(0, window.visualViewport?.offsetTop || 0)
    : 0;
};
const updateFavoriteCount = () => {
  try {
    favoriteCount.value = JSON.parse(localStorage.getItem('lamari-favorites') || '[]').length;
  } catch {
    favoriteCount.value = 0;
  }
};
onMounted(() => {
  updateFavoriteCount();
  updateHeaderPosition();
  window.addEventListener('storage', updateFavoriteCount);
  window.addEventListener('lamari-favorites', updateFavoriteCount);
  window.addEventListener('scroll', updateHeaderPosition, { passive: true });
  window.addEventListener('resize', updateHeaderPosition);
  window.visualViewport?.addEventListener('scroll', updateHeaderPosition);
  window.visualViewport?.addEventListener('resize', updateHeaderPosition);
});
onUnmounted(() => {
  window.removeEventListener('storage', updateFavoriteCount);
  window.removeEventListener('lamari-favorites', updateFavoriteCount);
  window.removeEventListener('scroll', updateHeaderPosition);
  window.removeEventListener('resize', updateHeaderPosition);
  window.visualViewport?.removeEventListener('scroll', updateHeaderPosition);
  window.visualViewport?.removeEventListener('resize', updateHeaderPosition);
});
watch(() => page.props.flash?.cartOpen, value => { if (value) cartOpen.value = true; });
const money = (amount: number) => (amount / 100).toLocaleString('uk-UA');
const cartDiscount = () => page.props.cartPreview.items.reduce((sum:number, item:any) => sum + (item.discount_total || 0), 0);
const asset = (url?: string) => !url ? '' : url.startsWith('http') ? url : `/storage/${url}`;
const itemImage = (item: any) => asset(item.variant.product.media?.find((m:any) => m.type === 'image')?.url || item.variant.product.image_url);
const displaySku = (variant:any) => /^\d+\s*см$/iu.test(variant?.name || '')
  ? String(variant?.sku || '').replace(/-\d+$/u, '')
  : String(variant?.sku || '');
const setQuantity = (item:any, quantity:number) => router.put(`/cart/${item.variant.id}`, { quantity }, { preserveScroll: true });
const setVariant = (item:any, variantId:number) => router.put(`/cart/${item.variant.id}/variant`, { variant_id: variantId }, { preserveScroll: true });
const remove = (item:any) => router.delete(`/cart/${item.variant.id}`, { preserveScroll: true });
const toggleCategory = (categoryId:number) => {
  expandedCategories.value = expandedCategories.value.includes(categoryId)
    ? expandedCategories.value.filter(id => id !== categoryId)
    : [...expandedCategories.value, categoryId];
};
const openSearch = () => {
  searchQuery.value = new URLSearchParams(window.location.search).get('q') || '';
  searchOpen.value = true;
  nextTick(() => searchInput.value?.focus());
};
const closeSearch = () => {
  searchOpen.value = false;
};
const submitSearch = () => {
  const query = searchQuery.value.trim();
  if (!query) return;
  searchOpen.value = false;
  router.get('/catalog', { q: query });
};
const handleEscape = (event: KeyboardEvent) => {
  if (event.key !== 'Escape') return;
  closeSearch();
  cartOpen.value = false;
  menuOpen.value = false;
};
onMounted(() => window.addEventListener('keydown', handleEscape));
onUnmounted(() => window.removeEventListener('keydown', handleEscape));
</script>

<template>
  <div ref="ticker" class="ticker" :class="{ 'home-ticker-top': props.homeOverlay }" aria-label="Безкоштовне брендоване пакування">
    <div class="ticker-track">
      <span v-for="index in 4" :key="index">{{ page.props.homepage?.ticker_text || 'БЕЗКОШТОВНЕ БРЕНДОВАНЕ ПАКУВАННЯ' }}</span>
    </div>
  </div>
  <div v-if="headerPinned" class="site-header-placeholder" aria-hidden="true"></div>
  <header
    :class="{ 'is-pinned': headerPinned, 'home-overlay-header': props.homeOverlay && !headerPinned }"
    :style="headerPinned ? { top: `${headerViewportOffset}px` } : undefined"
  >
    <Link href="/" class="brand" aria-label="Lamari Jewelry">
      <img :src="'/images/brand/lamari-logo-hq.png?v=1'" alt="Lamari Jewelry">
    </Link>
    <nav class="desktop-primary-nav" aria-label="Основна навігація">
      <Link href="/catalog">Каталог</Link>
      <Link href="/information/about">Про бренд</Link>
      <Link href="/information/delivery">Доставка і оплата</Link>
      <Link href="/#faq">Поширені питання</Link>
      <Link href="/information/cooperation">Співпраця</Link>
    </nav>
    <nav class="header-actions">
      <a href="https://www.instagram.com/lamari.jewelry/" target="_blank" aria-label="Instagram" class="header-icon">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg>
      </a>
      <button type="button" aria-label="Пошук у каталозі" class="header-icon" @click="openSearch">
        <svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="7"/><path d="m16 16 5 5"/></svg>
      </button>
      <Link href="/favorites" :aria-label="`Вподобане: ${favoriteCount}`" class="header-icon icon-with-count">
        <svg viewBox="0 0 24 24"><path d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.4 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>
        <small>{{ favoriteCount }}</small>
      </Link>
      <button class="cart-trigger header-icon icon-with-count" aria-label="Кошик" @click="cartOpen = true">
        <svg viewBox="0 0 24 24"><path d="M5 8h14l1 13H4L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
        <small>{{ page.props.cartCount || 0 }}</small>
      </button>
      <button class="menu-trigger mobile-menu-trigger header-icon" aria-label="Відкрити меню" @click="menuOpen = !menuOpen">
        <svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </nav>
  </header>

  <nav class="desktop-category-nav" aria-label="Категорії товарів">
    <Link
      v-for="category in page.props.catalogMenu"
      :key="category.id"
      :href="`/categories/${category.slug}`"
      :class="{ sale: category.slug.toLowerCase() === 'sale' }"
    >{{ category.name }}</Link>
  </nav>

  <div class="search-overlay" :class="{ open: searchOpen }" @click.self="closeSearch">
    <form class="site-search" role="search" @submit.prevent="submitSearch">
      <div class="site-search-head">
        <strong>Пошук</strong>
        <button type="button" aria-label="Закрити пошук" @click="closeSearch">×</button>
      </div>
      <label for="site-search-input">Назва товару або артикул</label>
      <div class="site-search-field">
        <input
          id="site-search-input"
          ref="searchInput"
          v-model="searchQuery"
          type="search"
          placeholder="Наприклад, кольє або K402-43"
          autocomplete="off"
        >
        <button type="submit" :disabled="!searchQuery.trim()" aria-label="Знайти">
          <svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="7"/><path d="m16 16 5 5"/></svg>
        </button>
      </div>
    </form>
  </div>

  <div class="catalog-backdrop" :class="{ open: menuOpen }" @click="menuOpen = false"></div>
  <aside class="catalog-drawer" :class="{ open: menuOpen }" aria-label="Каталог">
    <div class="catalog-drawer-head">
      <Link href="/" class="catalog-drawer-brand" aria-label="Lamari Jewelry" @click="menuOpen = false">
        <img :src="'/images/brand/lamari-logo-hq.png?v=1'" alt="Lamari Jewelry">
      </Link>
      <button class="drawer-close" aria-label="Закрити каталог" @click="menuOpen = false">×</button>
    </div>
    <nav class="drawer-grid">
      <section v-for="category in page.props.catalogMenu" :key="category.id" :class="{ expanded: expandedCategories.includes(category.id) }">
        <div class="drawer-category-row">
          <Link
            :href="`/categories/${category.slug}`"
            class="drawer-title"
            :class="{ sale: category.slug.toLowerCase() === 'sale' }"
            @click="menuOpen = false"
          >{{ category.name }}</Link>
          <button
            v-if="category.children?.length"
            class="drawer-expand"
            :aria-expanded="expandedCategories.includes(category.id)"
            :aria-label="`${expandedCategories.includes(category.id) ? 'Закрити' : 'Відкрити'} підкатегорії ${category.name}`"
            @click="toggleCategory(category.id)"
          >⌄</button>
        </div>
        <div v-if="category.children?.length" class="drawer-children">
          <Link
            v-for="child in category.children"
            :key="child.id"
            :href="`/categories/${child.slug}`"
            @click="menuOpen = false"
          >{{ child.name }}</Link>
        </div>
      </section>
    </nav>
  </aside>

  <div class="drawer-backdrop" :class="{ open: cartOpen }" @click="cartOpen = false"></div>
  <aside class="cart-drawer" :class="{ open: cartOpen }" aria-label="Кошик" :aria-hidden="!cartOpen">
    <div class="cart-drawer-head">
      <h2>Кошик <small>{{ page.props.cartCount }}</small></h2>
      <button type="button" class="cart-drawer-close" aria-label="Закрити кошик і продовжити покупки" @click="cartOpen = false">
        <span>Закрити</span><b aria-hidden="true">×</b>
      </button>
    </div>
    <div v-if="!page.props.cartPreview.items.length" class="cart-drawer-empty"><p>У кошику ще немає прикрас.</p><button @click="cartOpen=false;menuOpen=true">Перейти до каталогу</button></div>
    <div v-else class="cart-drawer-body">
      <article v-for="item in page.props.cartPreview.items" :key="item.variant.id" class="drawer-item">
        <img :src="itemImage(item)" :alt="item.variant.product.name" />
        <div class="drawer-item-info"><Link :href="`/products/${item.variant.product.slug}`" @click="cartOpen=false">{{ item.variant.product.name }}</Link><label class="cart-size">Довжина<select :value="item.variant.id" @change="setVariant(item, Number(($event.target as HTMLSelectElement).value))"><option v-for="variant in item.variant.product.variants" :key="variant.id" :value="variant.id" :disabled="!variant.is_active || variant.stock_on_hand <= variant.stock_reserved">{{ variant.name }}</option></select></label><small>Артикул {{ displaySku(item.variant) }}</small><div class="qty"><button @click="setQuantity(item,item.quantity-1)">−</button><span>{{ item.quantity }}</span><button @click="setQuantity(item,item.quantity+1)" :disabled="item.quantity >= item.variant.stock_on_hand-item.variant.stock_reserved">+</button></div></div>
        <div class="drawer-item-price"><div class="discounted-price"><span v-if="item.discount_total" class="discount-label">-{{ item.variant.discount_percentage }}%</span><del v-if="item.discount_total">{{ money(item.original_total) }} ₴</del><b>{{ money(item.total) }} ₴</b></div><button @click="remove(item)">Видалити</button></div>
      </article>
    </div>
    <div v-if="page.props.cartPreview.items.length" class="cart-drawer-footer"><p class="delivery-note">Вартість доставки буде розрахована під час оформлення.</p><div v-if="cartDiscount()" class="drawer-discount"><span>Ваша знижка</span><b>− {{ money(cartDiscount()) }} ₴</b></div><div class="drawer-subtotal"><span>Разом</span><b>{{ money(page.props.cartPreview.subtotal) }} ₴</b></div><Link href="/checkout" class="button drawer-checkout" @click="cartOpen=false">Оформити замовлення</Link><Link href="/cart" class="view-cart" @click="cartOpen=false">Переглянути кошик</Link></div>
  </aside>

  <div v-if="page.props.flash?.success" class="notice">{{ page.props.flash.success }}</div>
  <main><slot /></main>
  <footer class="site-footer">
    <div class="footer-inner">
      <section class="footer-contacts" aria-label="Контакти Lamari">
        <a class="footer-phone" href="tel:+380635463954">+38 063 546 39 54</a>
        <a class="footer-email" href="mailto:lamari.jewelry.site@gmail.com">jewelrylamari@gmail.com</a>
        <div class="footer-contact-row">
          <div class="footer-payments" aria-label="Способи оплати">
            <span class="payment-badge visa">VISA</span>
            <span class="payment-badge mastercard" aria-label="Mastercard"><i></i><i></i></span>
            <span class="payment-badge paw" aria-label="Оплата при отриманні">●</span>
          </div>
          <div class="footer-socials">
            <a href="https://www.instagram.com/lamari_jewelry/" target="_blank" rel="noopener" aria-label="Instagram">
              <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg>
            </a>
            <a href="https://t.me/lamari_jewelry" target="_blank" rel="noopener" aria-label="Telegram">
              <svg viewBox="0 0 24 24"><path d="M21 4 3.8 10.6c-1.2.5-1.2 1.2-.2 1.5l4.4 1.4 1.7 5.2c.2.7.1 1 .8 1 .5 0 .8-.2 1-.4l2.4-2.3 5 3.7c.9.5 1.6.2 1.8-.9L23.8 5c.3-1.2-.5-1.8-1.5-1.4Z"/><path d="m8 13.5 10.2-6.4"/></svg>
            </a>
          </div>
        </div>
      </section>

      <div class="footer-columns">
        <nav class="footer-column" aria-label="Каталог у футері">
          <h2>Каталог</h2>
          <Link href="/categories/necklaces">Кольє</Link>
          <Link href="/categories/chokers">Чокери</Link>
          <Link href="/categories/earrings">Сережки</Link>
          <Link href="/categories/chains">Ланцюжки</Link>
          <Link href="/categories/bracelets">Браслети</Link>
          <Link href="/categories/anklets">Анклети</Link>
          <Link href="/categories/rings">Каблучки</Link>
          <Link href="/categories/sets">Комплекти</Link>
          <Link href="/categories/summer">Літня колекція</Link>
          <Link href="/catalog?q=сертифікат">Сертифікати</Link>
          <Link href="/categories/pins">Булавки</Link>
          <Link href="/catalog?q=пакування">Пакування</Link>
        </nav>
        <nav class="footer-column" aria-label="Інформація у футері">
          <h2>Інформація</h2>
          <Link href="/information/about">Про бренд</Link>
          <Link href="/information/care">Догляд за виробами</Link>
          <Link href="/information/delivery">Доставка і оплата</Link>
          <Link href="/information/returns">Повернення та обмін</Link>
          <Link href="/information/contacts">Контакти</Link>
          <Link href="/information/cooperation">Співпраця</Link>
          <Link class="footer-spaced-link" href="/information/offer">Публічна оферта</Link>
          <Link href="/information/privacy">Політика обробки даних</Link>
        </nav>
      </div>
    </div>
  </footer>
</template>
