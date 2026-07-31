<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StoreLayout from '../Layouts/StoreLayout.vue';

defineProps<{
  title: string;
  sections: Array<{
    heading?: string | null;
    paragraphs?: string[];
    items?: string[];
  }>;
}>();
</script>

<template>
  <Head :title="title" />
  <StoreLayout>
    <main class="information-page">
      <nav class="information-breadcrumbs" aria-label="Навігація">
        <Link href="/">Головна</Link><span>/</span><span>{{ title }}</span>
      </nav>
      <article class="information-content">
        <h1>{{ title }}</h1>
        <section v-for="(section, index) in sections" :key="index">
          <h2 v-if="section.heading">{{ section.heading }}</h2>
          <p v-for="paragraph in section.paragraphs || []" :key="paragraph">{{ paragraph }}</p>
          <ol v-if="section.items">
            <li v-for="item in section.items" :key="item">{{ item }}</li>
          </ol>
        </section>
      </article>
    </main>
  </StoreLayout>
</template>
