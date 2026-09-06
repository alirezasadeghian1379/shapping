<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'محتوا و نمایش'});
const tabs = [['اسلایدرها', 'تصاویر اصلی صفحه نخست', '/content/sliders'], ['بنرها', 'جایگاه‌های تبلیغاتی قابل تنظیم', '/content/banners'], ['سؤالات متداول', 'پاسخ‌های گروه‌بندی‌شده', '/content/faqs'], ['مقالات', 'مجله و محتوای سئو', '/articles'], ['نظرات', 'نظرات محصول و مقاله', '/comments']]</script>
<template>
  <div class="space-y-5">
    <SectionIntro title="محتوا و نمایش" text="همه محتوای بازاریابی و آموزشی فروشگاه در یک نمای مرتب."/>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
      <article v-for="(tab,i) in tabs" :key="tab[0]"
               class="group rounded-2xl border bg-white p-6 shadow-soft transition hover:-translate-y-1">
        <div class="grid size-12 place-items-center rounded-2xl bg-brand-50 text-xl font-black text-brand-700">
          {{ i + 1 }}
        </div>
        <h3 class="mt-5 font-black">{{ tab[0] }}</h3>
        <p class="mt-2 text-sm leading-6 text-slate-400">{{ tab[1] }}</p>
        <NuxtLink :to="tab[2]" class="mt-5 inline-block text-sm font-bold text-brand-700">مدیریت بخش ←</NuxtLink>
      </article>
    </div>
  </div>
</template>
