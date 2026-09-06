<script setup lang="ts">
definePageMeta({middleware: 'auth'});
useHead({title: 'محصول جدید'});
const saved = () => navigateTo('/products')</script>
<template>
  <div class="space-y-5">
    <SectionIntro title="محصول جدید" text="اطلاعات، تصاویر، قیمت و موجودی تنوع‌ها را ثبت کنید."/>
    <ProductForm @saved="saved"/>
  </div>
</template>
