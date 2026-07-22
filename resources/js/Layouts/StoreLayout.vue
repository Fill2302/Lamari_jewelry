<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
const page = usePage<any>();
const menuOpen = ref(false);
const cartOpen = ref(Boolean(page.props.flash?.cartOpen));
watch(() => page.props.flash?.cartOpen, value => { if (value) cartOpen.value = true; });
const money = (amount: number) => (amount / 100).toLocaleString('uk-UA');
const asset = (url?: string) => !url ? '' : url.startsWith('http') ? url : `/storage/${url}`;
const itemImage = (item: any) => asset(item.variant.product.media?.find((m:any) => m.type === 'image')?.url || item.variant.product.image_url);
const setQuantity = (item:any, quantity:number) => router.put(`/cart/${item.variant.id}`, { quantity }, { preserveScroll: true });
const remove = (item:any) => router.delete(`/cart/${item.variant.id}`, { preserveScroll: true });
</script>

<template>
  <div class="ticker">БЕЗКОШТОВНЕ ПАКУВАННЯ КОЖНОГО ЗАМОВЛЕННЯ · MADE IN UKRAINE</div>
  <header>
    <button class="menu-trigger" @click="menuOpen = !menuOpen">Каталог</button>
    <Link href="/" class="brand">LAMARI</Link>
    <nav><a href="https://www.instagram.com/lamari.jewelry/" target="_blank">Instagram</a><button class="cart-trigger" @click="cartOpen = true">Кошик ({{ page.props.cartCount }})</button></nav>
  </header>

  <div class="catalog-drawer" :class="{ open: menuOpen }">
    <button class="drawer-close" @click="menuOpen = false">Закрити ×</button>
    <div class="drawer-grid"><section v-for="category in page.props.catalogMenu" :key="category.id"><Link :href="`/categories/${category.slug}`" class="drawer-title">{{ category.name }}</Link><Link v-for="child in category.children" :key="child.id" :href="`/categories/${child.slug}`">{{ child.name }}</Link></section></div>
  </div>

  <div class="drawer-backdrop" :class="{ open: cartOpen }" @click="cartOpen = false"></div>
  <aside class="cart-drawer" :class="{ open: cartOpen }" aria-label="Кошик">
    <div class="cart-drawer-head"><h2>Кошик <small>{{ page.props.cartCount }}</small></h2><button @click="cartOpen = false">Закрити ×</button></div>
    <div v-if="!page.props.cartPreview.items.length" class="cart-drawer-empty"><p>У кошику ще немає прикрас.</p><button @click="cartOpen=false;menuOpen=true">Перейти до каталогу</button></div>
    <div v-else class="cart-drawer-body">
      <article v-for="item in page.props.cartPreview.items" :key="item.variant.id" class="drawer-item">
        <img :src="itemImage(item)" :alt="item.variant.product.name" />
        <div class="drawer-item-info"><Link :href="`/products/${item.variant.product.slug}`" @click="cartOpen=false">{{ item.variant.product.name }}</Link><small>{{ item.variant.name }} · {{ item.variant.sku }}</small><div class="qty"><button @click="setQuantity(item,item.quantity-1)">−</button><span>{{ item.quantity }}</span><button @click="setQuantity(item,item.quantity+1)" :disabled="item.quantity >= item.variant.stock_on_hand-item.variant.stock_reserved">+</button></div></div>
        <div class="drawer-item-price"><b>{{ money(item.total) }} ₴</b><button @click="remove(item)">Видалити</button></div>
      </article>
    </div>
    <div v-if="page.props.cartPreview.items.length" class="cart-drawer-footer"><p class="delivery-note">Вартість доставки буде розрахована під час оформлення.</p><div class="drawer-subtotal"><span>Разом</span><b>{{ money(page.props.cartPreview.subtotal) }} ₴</b></div><Link href="/checkout" class="button drawer-checkout" @click="cartOpen=false">Оформити замовлення</Link><Link href="/cart" class="view-cart" @click="cartOpen=false">Переглянути кошик</Link></div>
  </aside>

  <div v-if="page.props.flash?.success" class="notice">{{ page.props.flash.success }}</div>
  <main><slot /></main>
  <footer><b>LAMARI</b><span>Авторські прикраси ручної роботи.</span></footer>
</template>
