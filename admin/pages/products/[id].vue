<script setup lang="ts">
import {useApi} from "../../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'ویرایش محصول'});
const route = useRoute(), {request} = useApi();
const {
  data: product,
  pending
} = await useAsyncData(`product-${route.params.id}`, () => request(`/admin/products/${route.params.id}`));
const saved = () => navigateTo('/products')</script>
<template>
  <div class="space-y-5">
    <SectionIntro title="ویرایش محصول" text="تغییرات محصول و تنوع‌های آن را مدیریت کنید."/>
    <div v-if="pending" class="h-96 animate-pulse rounded-2xl bg-slate-200"/>
    <ProductForm v-else-if="product" :product="product" @saved="saved"/>
  </div>
</template>
