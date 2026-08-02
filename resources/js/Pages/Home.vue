<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import StoreLayout from '../Layouts/StoreLayout.vue';

defineProps<{ categories: any[], newProducts:any[], hitProducts:any[] }>();

const openFaq = ref<number | null>(null);
const asset = (url:string) => url?.startsWith('http') ? url : `/storage/${url}`;
const productImage = (product:any) => asset(product.media?.find((item:any) => item.type === 'image')?.url || product.image_url);
const price = (product:any) => product.variants?.[0]?.effective_price_amount ?? product.variants?.[0]?.price_amount ?? 0;
const originalPrice = (product:any) => product.variants?.[0]?.discount_percentage
  ? product.variants[0].original_price_amount
  : product.compare_at_price_amount;
const categoryCards = [
  { name:'Кольє', slug:'necklaces', image:'/images/home/categories/necklaces.jpg' },
  { name:'Чокери', slug:'chokers', image:'/images/home/categories/chokers.jpg' },
  { name:'Сережки', slug:'earrings', image:'/images/home/categories/earrings.jpg' },
  { name:'Ланцюжки', slug:'chains', image:'/images/home/categories/chains.jpg' },
  { name:'Браслети', slug:'bracelets', image:'/images/home/categories/bracelets.jpg' },
  { name:'Анклети', slug:'anklets', image:'/images/home/categories/anklets.jpeg' },
  { name:'Каблучки', slug:'rings', image:'/images/home/categories/rings.jpg' },
  { name:'Комплекти', slug:'sets', image:'/images/home/categories/sets.jpg' },
  { name:'Літня колекція', slug:'summer', image:'/images/home/categories/summer.jpg' },
  { name:'Булавки', slug:'pins', image:'/images/home/categories/pins.jpg' },
];
const faqs = [
  { q:'Чому варто обирати прикраси LAMARI?', a:'Усі прикраси створені за авторською ідеєю та виконані з матеріалів найвищої якості. Виготовляємо прикраси на замовлення протягом 1–2 днів і відправляємо у брендованих коробочках, які зручно використовувати для зберігання або подарунка. Маємо широкий асортимент прикрас із натуральних перлин, каміння та ланцюжків, здійснюємо відправлення Україною та за кордон.' },
  { q:'Який матеріал фурнітури?', a:'Ми обираємо матеріали класу люкс із якісним, стійким та гіпоалергенним покриттям. У золотому кольорі це позолота 18 карат по латуні. У срібному — латунь із покриттям родій або ювелірна сталь. Усі матеріали спершу тестуємо особисто й лише потім виготовляємо з ними прикраси.' },
  { q:'Які перли та каміння використовуєте?', a:'Ми використовуємо тільки натуральні прісноводні перли та натуральне каміння.' },
  { q:'У мене чутливі вуха. Який матеріал сережок?', a:'Наші сережки мають гіпоалергенний сплав, не викликають алергії та дискомфорту.' },
  { q:'Чи темніє прикраса?', a:'Наші прикраси не темніють. Щоб прикраса якомога довше зберігала початковий вигляд, не рекомендуємо мочити її, особливо у солоній воді та басейні.' },
  { q:'Можна мочити прикраси?', a:'Прикраси з ювелірної сталі можна мочити та носити не знімаючи. Прикраси з покриттям золотом і родієм рекомендуємо знімати перед душем, морем або басейном. Якщо прикраса намокла, нічого страшного — просто просушіть її серветкою.' },
  { q:'Який догляд за прикрасами?', a:'Рекомендуємо знімати прикраси перед сном і душем, щоб уникнути зайвого тертя. Не наносити на них парфуми та креми безпосередньо, а також зберігати прикраси окремо одну від одної.' },
  { q:'Як довго мені прослужать прикраси?', a:'Ми обираємо найкращі матеріали та тестуємо їх особисто. Довговічність первинного вигляду залежить від багатьох факторів: pH шкіри, частоти носіння, контакту з парфумами та косметикою.' },
  { q:'Чи можна обрати довжину прикраси?', a:'Так, бажану довжину можна обрати у картці товару. Якщо потрібної довжини немає, зв’яжіться з нами через чат — ми допоможемо.' },
];
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
      <video
        :src="'/images/home/hero-video.mp4'"
        :poster="'/images/home/summer-collection-mobile-clean-v3.webp'"
        autoplay
        muted
        loop
        playsinline
        preload="metadata"
        aria-hidden="true"
      ></video>
    </Link>

    <section v-if="newProducts.length" class="home-showcase">
      <div class="home-section-heading"><h2>Новинки</h2><Link href="/catalog?sort=newest">Переглянути всі</Link></div>
      <div class="home-products">
        <Link v-for="product in newProducts" :key="product.id" :href="`/products/${product.slug}`" class="home-product-card">
          <div class="home-product-image"><img :src="productImage(product)" :alt="product.name" loading="lazy"><span>NEW</span></div>
          <h3>{{ product.name }}</h3>
          <p><del v-if="originalPrice(product)">{{ (originalPrice(product)/100).toLocaleString('uk-UA') }} ₴</del>{{ (price(product)/100).toLocaleString('uk-UA') }} ₴</p>
        </Link>
      </div>
    </section>

    <section v-if="hitProducts.length" class="home-showcase">
      <div class="home-section-heading"><h2>Хіти продажів</h2><Link href="/catalog">Переглянути всі</Link></div>
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

    <section class="home-faq">
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
      <h2>Ви і Lamari Jewelry</h2>
      <p>Діліться своїми образами, відзначайте нас у Instagram, і ми із задоволенням додамо ваші фото</p>
      <div class="instagram-gallery">
        <img v-for="n in 6" :key="n" :src="`/images/home/instagram/insta${n}.png`" :alt="`Відгук клієнтки Lamari ${n}`" loading="lazy">
      </div>
      <a class="instagram-button" href="https://www.instagram.com/lamari.jewelry/" target="_blank" rel="noopener">Наш Instagram</a>
    </section>
  </StoreLayout>
</template>
