<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StoreLayout from '../Layouts/StoreLayout.vue';

const props = defineProps<{ order: any }>();
const isCashOnDelivery = props.order.payment_status === 'cash_on_delivery';
</script>

<template>
  <Head title="Замовлення прийнято" />
  <StoreLayout>
    <section class="payment thank-you">
      <p class="eyebrow">ДЯКУЄМО ЗА ЗАМОВЛЕННЯ</p>
      <h1>Замовлення прийнято</h1>
      <p class="thank-you-number">Номер замовлення: <strong>{{ order.number }}</strong></p>
      <div class="thank-you-message">
        <template v-if="isCashOnDelivery">
          <strong>Оплата при отриманні</strong>
          <span>Ми зв’яжемося з вами для підтвердження замовлення та відправлення Новою поштою.</span>
        </template>
        <template v-else>
          <strong>Оплату успішно прийнято</strong>
          <span>Ми повідомимо вам, коли замовлення буде передано до доставки.</span>
        </template>
      </div>
      <Link href="/" class="button">Повернутися до каталогу</Link>
    </section>
  </StoreLayout>
</template>
