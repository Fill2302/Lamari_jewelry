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
  { q:'Чи можна мочити прикрасу?', a:'Залежить від матеріалу. Прикраси з нержавіючої сталі витривалі та добре підходять для щоденного носіння. Для моделей з латунними елементами, перлинами або натуральним камінням варто враховувати рекомендації у картці товару.' },
  { q:'Як довго тримається покриття?', a:'Нержавіюча сталь без покриття зберігає природний колір і може служити багато років. PVD-покриття на сталі добре витримує щоденне носіння. Позолота й родієве покриття на латуні також добре носяться за дбайливого догляду.' },
  { q:'Це медичний сплав?', a:'«Медичний сплав» не є точною назвою матеріалу. Ми вказуємо фактичний склад: переважно нержавіюча сталь 304, в окремих виробах 316, або латунь із відповідним покриттям.' },
  { q:'Чи може прикраса викликати реакцію шкіри?', a:'Реакція завжди індивідуальна. За досвідом Lamari нержавіюча сталь добре підходить більшості клієнтів. Якщо виникне проблема, напишіть нам — постараємося запропонувати альтернативний матеріал.' },
  { q:'Перлини натуральні?', a:'Ми використовуємо культивовані перлини. Вони формуються у молюску за участю людини, тому можуть мати природні відмінності форми, кольору та поверхні.' },
  { q:'Як обрати довжину кольє або чокера?', a:'Орієнтуйтеся на обхват шиї, бажану посадку та виріз одягу. Якщо сумніваєтеся, напишіть нам свої параметри або надішліть фото бажаної посадки — допоможемо визначити довжину.' },
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
      <picture>
        <source media="(max-width: 600px)" type="image/webp" :srcset="'/images/home/summer-collection-mobile-clean-v3.webp'">
        <img :src="'/images/home/summer-collection-desktop.jpg'" alt="Summer Collection Lamari">
      </picture>
      <span class="mobile-campaign-copy">
        <strong>SUMMER COLLECTION</strong>
        <span>Каталог</span>
      </span>
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
