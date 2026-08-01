<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import StoreLayout from '../Layouts/StoreLayout.vue';

defineProps<{ payment: any }>();
const form = useForm({});
</script>

<template>
  <Head title="Тестова оплата" />
  <StoreLayout>
    <section class="payment">
      <p class="eyebrow">SANDBOX · FAKE PROVIDER</p>
      <h1>Тестова оплата</h1>
      <p class="payment-order">Замовлення <strong>{{ payment.order.number }}</strong></p>
      <div class="pay-card">
        <span>До сплати</span>
        <b>{{ (payment.amount / 100).toLocaleString('uk-UA') }} ₴</b>
        <button class="button" :disabled="form.processing" @click="form.post(`/payments/fake/${payment.id}/pay`)">
          {{ form.processing ? 'Обробляємо…' : 'Імітувати успішну оплату' }}
        </button>
      </div>
      <small>Жодні карткові дані не передаються.</small>
    </section>
  </StoreLayout>
</template>
