<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
const page = usePage<any>();
const menuOpen = ref(false);
</script>

<template>
  <div class="ticker">БЕЗКОШТОВНЕ ПАКУВАННЯ КОЖНОГО ЗАМОВЛЕННЯ · MADE IN UKRAINE</div>
  <header>
    <button class="menu-trigger" @click="menuOpen = !menuOpen">Каталог</button>
    <Link href="/" class="brand">LAMARI</Link>
    <nav><a href="https://www.instagram.com/lamari.jewelry/" target="_blank">Instagram</a><Link href="/cart">Кошик ({{ page.props.cartCount }})</Link></nav>
  </header>
  <div class="catalog-drawer" :class="{ open: menuOpen }">
    <button class="drawer-close" @click="menuOpen = false">Закрити ×</button>
    <div class="drawer-grid">
      <section v-for="category in page.props.catalogMenu" :key="category.id">
        <Link :href="`/categories/${category.slug}`" class="drawer-title">{{ category.name }}</Link>
        <Link v-for="child in category.children" :key="child.id" :href="`/categories/${child.slug}`">{{ child.name }}</Link>
      </section>
    </div>
  </div>
  <div v-if="page.props.flash?.success" class="notice">{{ page.props.flash.success }}</div>
  <main><slot /></main>
  <footer><b>LAMARI</b><span>Авторські прикраси ручної роботи.</span></footer>
</template>
