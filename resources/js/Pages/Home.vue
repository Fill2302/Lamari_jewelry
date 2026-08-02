<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import StoreLayout from '../Layouts/StoreLayout.vue';

const props = defineProps<{ categories: any[], newProducts:any[], hitProducts:any[], homepage:any }>();

const openFaq = ref<number | null>(null);
const asset = (url:string) => url?.startsWith('http') ? url : `/storage/${url}`;
const pageAsset = (url:string) => !url ? '' : (url.startsWith('http') || url.startsWith('/')) ? url : `/storage/${url}`;
const productImage = (product:any) => asset(product.media?.find((item:any) => item.type === 'image')?.url || product.image_url);
const price = (product:any) => product.variants?.[0]?.effective_price_amount ?? product.variants?.[0]?.price_amount ?? 0;
const originalPrice = (product:any) => product.variants?.[0]?.discount_percentage
  ? product.variants[0].original_price_amount
  : product.compare_at_price_amount;
const fallbackCategoryImages:Record<string,string> = {
  necklaces:'/images/home/categories/necklaces.jpg', chokers:'/images/home/categories/chokers.jpg',
  earrings:'/images/home/categories/earrings.jpg', chains:'/images/home/categories/chains.jpg',
  bracelets:'/images/home/categories/bracelets.jpg', anklets:'/images/home/categories/anklets.jpeg',
  rings:'/images/home/categories/rings.jpg', sets:'/images/home/categories/sets.jpg',
  summer:'/images/home/categories/summer.jpg', pins:'/images/home/categories/pins.jpg',
};
const categoryCards = computed(() => props.categories.map(item => ({
  ...item,
  image: item.image_url ? pageAsset(item.image_url) : fallbackCategoryImages[item.slug],
})).filter(item => item.image));
const faqs = computed(() => (props.homepage?.faq_items || []).map((item:any) => ({ q:item.question, a:item.answer })));
</script>

<template>
  <Head>
    <title>Авторські прикраси ручної роботи</title>
    <meta name="description" content="Lamari Jewelry — авторські прикраси ручної роботи: кольє, чокери, сережки, браслети та каблучки." />
    <link rel="canonical" href="http://localhost" />
    <meta property="og:title" content="Lamari Jewelry" />
  </Head>
  <StoreLayout home-overlay>
    <Link :href="homepage?.hero_link || '/catalog'" class="home-campaign" aria-label="Перейти до каталогу всіх товарів">
      <img
        class="home-campaign-desktop"
        :src="pageAsset(homepage?.desktop_hero_image || '/images/home/summer-collection-desktop.jpg?v=2')"
        alt="Літня колекція Lamari Jewelry"
        loading="eager"
        fetchpriority="high"
      >
      <video
        :src="pageAsset(homepage?.mobile_hero_video || '/images/home/hero-video.mp4')"
        :poster="pageAsset(homepage?.mobile_hero_poster || '/images/home/hero-video-first-frame.webp')"
        autoplay
        muted
        loop
        playsinline
        preload="metadata"
        aria-hidden="true"
      ></video>
    </Link>

    <div class="ticker desktop-home-ticker" aria-label="Безкоштовне брендоване пакування">
      <div class="ticker-track">
        <span v-for="index in 4" :key="index">{{ homepage?.ticker_text || 'БЕЗКОШТОВНЕ БРЕНДОВАНЕ ПАКУВАННЯ' }}</span>
      </div>
    </div>

    <section v-if="homepage?.show_new_products !== false && newProducts.length" class="home-showcase">
      <div class="home-section-heading"><h2>{{ homepage?.new_products_title || 'Новинки' }}</h2><Link href="/catalog?sort=newest">Переглянути всі</Link></div>
      <div class="home-products">
        <Link v-for="product in newProducts" :key="product.id" :href="`/products/${product.slug}`" class="home-product-card">
          <div class="home-product-image"><img :src="productImage(product)" :alt="product.name" loading="lazy"><span>NEW</span></div>
          <h3>{{ product.name }}</h3>
          <p><del v-if="originalPrice(product)">{{ (originalPrice(product)/100).toLocaleString('uk-UA') }} ₴</del>{{ (price(product)/100).toLocaleString('uk-UA') }} ₴</p>
        </Link>
      </div>
    </section>

    <section v-if="homepage?.show_hit_products !== false && hitProducts.length" class="home-showcase">
      <div class="home-section-heading"><h2>{{ homepage?.hit_products_title || 'Хіти продажів' }}</h2><Link href="/catalog">Переглянути всі</Link></div>
      <div class="home-products">
        <Link v-for="product in hitProducts" :key="product.id" :href="`/products/${product.slug}`" class="home-product-card">
          <div class="home-product-image"><img :src="productImage(product)" :alt="product.name" loading="lazy"><span class="hit">ХІТ</span></div>
          <h3>{{ product.name }}</h3>
          <p><del v-if="originalPrice(product)">{{ (originalPrice(product)/100).toLocaleString('uk-UA') }} ₴</del>{{ (price(product)/100).toLocaleString('uk-UA') }} ₴</p>
        </Link>
      </div>
    </section>

    <section class="home-categories">
      <Link v-for="item in categoryCards" :key="item.slug" :href="`/categories/${item.slug}`" class="home-category-card">
        <img :src="item.image" :alt="item.name" loading="lazy">
        <strong>{{ item.name }}</strong><span aria-hidden="true">⟶</span>
      </Link>
    </section>

    <section id="faq" class="home-faq">
      <h2>Поширені питання</h2>
      <div class="faq-list">
        <article v-for="(faq,index) in faqs" :key="faq.q" :class="{ open: openFaq === index }">
          <button type="button" :aria-expanded="openFaq === index" @click="openFaq = openFaq === index ? null : index"><span>{{ faq.q }}</span><b>{{ openFaq === index ? '−' : '+' }}</b></button>
          <p v-if="openFaq === index">{{ faq.a }}</p>
        </article>
      </div>
    </section>

    <section class="home-instagram">
      <div class="instagram-mark">◎</div>
      <h2>{{ homepage?.instagram_title || 'Ви і Lamari Jewelry' }}</h2>
      <p>{{ homepage?.instagram_text }}</p>
      <div class="instagram-gallery">
        <img v-for="(image,index) in (homepage?.instagram_images || [])" :key="image" :src="pageAsset(image)" :alt="`Відгук клієнтки Lamari ${index + 1}`" loading="lazy">
      </div>
      <a class="instagram-button" :href="homepage?.instagram_url || 'https://www.instagram.com/lamari.jewelry/'" target="_blank" rel="noopener">Наш Instagram</a>
    </section>
  </StoreLayout>
</template>
