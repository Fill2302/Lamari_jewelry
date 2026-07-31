<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StoreLayout from '../Layouts/StoreLayout.vue';

defineProps<{ categories: any[] }>();
</script>

<template>
  <Head>
    <title>Авторські прикраси ручної роботи</title>
    <meta name="description" content="Lamari Jewelry — авторські прикраси ручної роботи: кольє, чокери, сережки, браслети та каблучки." />
    <link rel="canonical" href="http://localhost" />
    <meta property="og:title" content="Lamari Jewelry" />
  </Head>
  <StoreLayout>
    <Link href="/catalog" class="home-campaign" aria-label="Перейти до каталогу всіх товарів">
      <picture>
        <source media="(max-width: 600px)" type="image/webp" :srcset="'/images/home/summer-collection-mobile-clean-v3.webp'">
        <img :src="'/images/home/summer-collection-desktop.jpg'" alt="Summer Collection Lamari">
      </picture>
      <span class="mobile-campaign-copy">
        <strong>SUMMER COLLECTION</strong>
        <span>Каталог</span>
      </span>
    </Link>

    <section class="section">
      <div class="section-title">
        <div><p class="eyebrow">КАТАЛОГ</p><h2>Знайди свою прикрасу</h2></div>
      </div>
      <div class="department-grid">
        <Link v-for="category in categories.filter(c => !['sale', 'summer'].includes(c.slug))" :key="category.id" :href="`/categories/${category.slug}`" class="department">
          <span>{{ category.name }}</span>
          <small>{{ category.children.length ? `${category.children.length} розділів` : 'Переглянути' }}</small>
        </Link>
      </div>
    </section>
    <section class="brand-story">
      <p class="eyebrow">MADE BY HAND</p>
      <h2>Особливі деталі.<br>Твоя особиста історія.</h2>
      <p>Кожне замовлення безкоштовно пакуємо у брендовану подарункову коробочку.</p>
    </section>
  </StoreLayout>
</template>
