<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import StoreLayout from '../Layouts/StoreLayout.vue';

const props = defineProps<{ items: any[], total: number, promo: null | { code: string, discount: number }, amountDue: number }>();
const discountTotal = computed(() => props.items.reduce((sum:number, item:any) => sum + (item.discount_total || 0), 0));
const promoOpen = ref(Boolean(props.promo));
const promoForm = useForm({ code: props.promo?.code || '' });

function applyPromo() {
  promoForm.code = promoForm.code.trim();
  promoForm.post('/checkout/promo-code', { preserveScroll: true, onSuccess: () => { promoOpen.value = true; } });
}

const form = useForm({
  first_name: '',
  last_name: '',
  email: '',
  phone: '+38',
  city: '',
  city_ref: '',
  warehouse: '',
  warehouse_ref: '',
  payment_method: 'online',
});

type City = { ref: string; name: string; area?: string; type?: string };
type Warehouse = { ref: string; name: string; address?: string; number?: string; category?: string };

const quickCities = ['Київ', 'Харків', 'Одеса', 'Львів'];
const citySuggestions = ref<City[]>([]);
const warehouseSuggestions = ref<Warehouse[]>([]);
const cityLoading = ref(false);
const warehouseLoading = ref(false);
const cityOpen = ref(false);
const warehouseOpen = ref(false);
let cityTimer: ReturnType<typeof setTimeout>;
let warehouseTimer: ReturnType<typeof setTimeout>;

async function loadCities(query: string) {
  if (query.trim().length < 2) {
    citySuggestions.value = [];
    return;
  }
  cityLoading.value = true;
  try {
    const response = await fetch(`/api/delivery/nova-poshta/cities?q=${encodeURIComponent(query.trim())}`);
    const payload = await response.json();
    citySuggestions.value = response.ok ? payload.data : [];
    cityOpen.value = true;
  } finally {
    cityLoading.value = false;
  }
}

async function chooseQuickCity(name: string) {
  await loadCities(name);
  const city = citySuggestions.value.find((item) => item.name === name) || citySuggestions.value[0];
  if (city) chooseCity(city);
}

function chooseCity(city: City) {
  form.city = city.name;
  form.city_ref = city.ref;
  form.warehouse = '';
  form.warehouse_ref = '';
  cityOpen.value = false;
  citySuggestions.value = [];
  form.clearErrors('city', 'city_ref');
}

function chooseWarehouse(warehouse: Warehouse) {
  form.warehouse = warehouse.name;
  form.warehouse_ref = warehouse.ref;
  warehouseOpen.value = false;
  warehouseSuggestions.value = [];
  form.clearErrors('warehouse', 'warehouse_ref');
}

watch(() => form.city, (value) => {
  if (form.city_ref) return;
  clearTimeout(cityTimer);
  cityTimer = setTimeout(() => loadCities(value), 300);
});

watch(() => form.warehouse, (value) => {
  if (!form.city_ref || form.warehouse_ref) return;
  clearTimeout(warehouseTimer);
  warehouseTimer = setTimeout(async () => {
    warehouseLoading.value = true;
    try {
      const params = new URLSearchParams({ city_ref: form.city_ref, q: value.trim() });
      const response = await fetch(`/api/delivery/nova-poshta/warehouses?${params}`);
      const payload = await response.json();
      warehouseSuggestions.value = response.ok ? payload.data : [];
      warehouseOpen.value = true;
    } finally {
      warehouseLoading.value = false;
    }
  }, 300);
});

function onCityInput() {
  form.city_ref = '';
  form.warehouse = '';
  form.warehouse_ref = '';
  form.clearErrors('city', 'city_ref');
}

function onWarehouseInput() {
  form.warehouse_ref = '';
  form.clearErrors('warehouse', 'warehouse_ref');
}

const phoneError = 'Введіть повний номер у форматі +38 0XX XXX XX XX';

function formatPhone(value: string): string {
  let digits = value.replace(/\D/g, '');

  if (digits.startsWith('38')) {
    digits = digits.slice(2);
  }

  const local = digits.slice(0, 10);
  const groups = [
    local.slice(0, 3),
    local.slice(3, 6),
    local.slice(6, 8),
    local.slice(8, 10),
  ].filter(Boolean);

  return `+38${groups.length ? ` ${groups.join(' ')}` : ''}`;
}

function onPhoneInput(event: Event) {
  form.phone = formatPhone((event.target as HTMLInputElement).value);
  form.clearErrors('phone');
}

function submit() {
  form.phone = formatPhone(form.phone);

  if (!/^\+38 0\d{2} \d{3} \d{2} \d{2}$/.test(form.phone)) {
    form.setError('phone', phoneError);
    return;
  }

  if (!form.city_ref) {
    form.setError('city', 'Оберіть місто зі списку Нової пошти.');
    return;
  }

  if (!form.warehouse_ref) {
    form.setError('warehouse', 'Оберіть відділення або поштомат зі списку.');
    return;
  }

  form.post('/checkout');
}
</script>

<template>
  <Head title="Оформлення замовлення" />
  <StoreLayout>
    <section class="checkout-page">
      <header class="checkout-heading">
        <p class="eyebrow">ВАШЕ ЗАМОВЛЕННЯ</p>
        <h1>Оформлення замовлення</h1>
        <p>Заповніть контактні дані — ми зв’яжемося з вами для підтвердження.</p>
      </header>

      <form class="checkout" @submit.prevent="submit">
        <div class="checkout-fields">
          <label>
            Ім’я
            <input v-model="form.first_name" autocomplete="given-name" required />
            <small v-if="form.errors.first_name">{{ form.errors.first_name }}</small>
          </label>
          <label>
            Прізвище
            <input v-model="form.last_name" autocomplete="family-name" required />
            <small v-if="form.errors.last_name">{{ form.errors.last_name }}</small>
          </label>
          <label>
            Номер телефону
            <input
              :value="form.phone"
              :class="{ 'is-invalid': form.errors.phone }"
              type="tel"
              inputmode="numeric"
              autocomplete="tel"
              maxlength="17"
              aria-describedby="phone-error"
              :aria-invalid="Boolean(form.errors.phone)"
              required
              @input="onPhoneInput"
              @focus="onPhoneInput"
            />
            <small v-if="form.errors.phone" id="phone-error">{{ form.errors.phone }}</small>
          </label>
          <label>
            Email
            <input v-model="form.email" type="email" inputmode="email" autocomplete="email" required />
            <small v-if="form.errors.email">{{ form.errors.email }}</small>
          </label>
          <label class="checkout-autocomplete">
            Місто
            <div class="quick-cities" aria-label="Популярні міста">
              <button v-for="city in quickCities" :key="city" type="button" @click="chooseQuickCity(city)">{{ city }}</button>
            </div>
            <input v-model="form.city" autocomplete="off" placeholder="Почніть вводити назву міста" required @input="onCityInput" @focus="cityOpen = citySuggestions.length > 0" />
            <span v-if="cityLoading" class="field-status">Шукаємо місто…</span>
            <ul v-if="cityOpen && citySuggestions.length" class="autocomplete-list">
              <li v-for="city in citySuggestions" :key="city.ref">
                <button type="button" @click="chooseCity(city)"><strong>{{ city.name }}</strong><small>{{ [city.type, city.area && `${city.area} обл.`].filter(Boolean).join(', ') }}</small></button>
              </li>
            </ul>
            <small v-if="form.errors.city">{{ form.errors.city }}</small>
          </label>
          <label class="checkout-address checkout-autocomplete">
            Відділення або поштомат Нової пошти
            <input v-model="form.warehouse" autocomplete="off" :disabled="!form.city_ref" :placeholder="form.city_ref ? 'Введіть номер або адресу' : 'Спочатку оберіть місто'" required @input="onWarehouseInput" @focus="warehouseOpen = warehouseSuggestions.length > 0" />
            <span v-if="warehouseLoading" class="field-status">Шукаємо відділення…</span>
            <ul v-if="warehouseOpen && warehouseSuggestions.length" class="autocomplete-list">
              <li v-for="warehouse in warehouseSuggestions" :key="warehouse.ref">
                <button type="button" @click="chooseWarehouse(warehouse)"><strong>{{ warehouse.name }}</strong><small v-if="warehouse.address">{{ warehouse.address }}</small></button>
              </li>
            </ul>
            <small v-if="form.errors.warehouse">{{ form.errors.warehouse }}</small>
          </label>
          <fieldset class="checkout-payment-methods">
            <legend>Спосіб оплати</legend>
            <label :class="{ selected: form.payment_method === 'online' }">
              <input v-model="form.payment_method" type="radio" value="online" />
              <span><strong>Онлайн-оплата</strong><small>Оплата карткою, Apple Pay або Google Pay</small></span>
            </label>
            <label :class="{ selected: form.payment_method === 'cash_on_delivery' }">
              <input v-model="form.payment_method" type="radio" value="cash_on_delivery" />
              <span><strong>Оплата при отриманні</strong><small>Післяплата у відділенні Нової пошти</small></span>
            </label>
            <small v-if="form.errors.payment_method">{{ form.errors.payment_method }}</small>
          </fieldset>
        </div>

        <aside class="checkout-summary">
          <h2>Разом</h2>
          <div class="checkout-summary-row">
            <span>Товарів: {{ items.reduce((sum, item) => sum + item.quantity, 0) }}</span>
            <b>{{ (total / 100).toLocaleString('uk-UA') }} ₴</b>
          </div>
          <div v-if="discountTotal" class="checkout-discount-row">
            <span>Ваша знижка</span>
            <b>− {{ (discountTotal / 100).toLocaleString('uk-UA') }} ₴</b>
          </div>
          <div class="checkout-promo">
            <button type="button" class="checkout-promo-toggle" :aria-expanded="promoOpen" @click="promoOpen = !promoOpen">
              <span>Промокод</span>
              <svg viewBox="0 0 20 20" aria-hidden="true" :class="{ open: promoOpen }"><path d="m5 7.5 5 5 5-5" /></svg>
            </button>
            <form v-if="promoOpen" class="checkout-promo-form" @submit.prevent="applyPromo">
              <input v-model="promoForm.code" aria-label="Промокод" autocomplete="off" placeholder="Введіть промокод" :disabled="Boolean(promo)" />
              <button type="submit" :disabled="promoForm.processing || Boolean(promo)">{{ promo ? 'Застосовано' : 'Застосувати' }}</button>
              <small v-if="promoForm.errors.code">{{ promoForm.errors.code }}</small>
              <small v-else-if="promo" class="checkout-promo-success">Промокод {{ promo.code }} активовано</small>
            </form>
          </div>
          <div v-if="promo" class="checkout-discount-row checkout-promo-discount">
            <span>Знижка за промокодом</span>
            <b>− {{ (promo.discount / 100).toLocaleString('uk-UA') }} ₴</b>
          </div>
          <div class="checkout-amount-due">
            <span>До сплати</span>
            <b>{{ (amountDue / 100).toLocaleString('uk-UA') }} ₴</b>
          </div>
          <p>Вартість доставки буде розрахована під час підтвердження замовлення.</p>
          <button class="button" :disabled="form.processing">
            {{ form.processing ? 'Оформлюємо…' : 'Підтвердити замовлення' }}
          </button>
        </aside>
      </form>
    </section>
  </StoreLayout>
</template>
