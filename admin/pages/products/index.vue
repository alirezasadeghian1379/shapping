<script setup lang="ts">
import {useApi} from "../../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'محصولات'});
const {request} = useApi();
const search = ref('');
const {
  data,
  pending,
  refresh
} = await useAsyncData('admin-products', () => request<any>('/admin/products', {query: {search: search.value}}));
const money = (v = 0) => new Intl.NumberFormat('fa-IR').format(v) + ' تومان';
const status: any = {
  published: ['منتشرشده', 'bg-emerald-50 text-emerald-700'],
  draft: ['پیش‌نویس', 'bg-amber-50 text-amber-700'],
  archived: ['بایگانی', 'bg-slate-100 text-slate-600']
};
</script>
<template>
  <div class="space-y-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div><h2 class="text-xl font-black">مدیریت محصولات</h2>
        <p class="mt-1 text-sm text-slate-400">محصول، تنوع‌ها، قیمت و موجودی را یک‌جا مدیریت کنید.</p></div>
      <NuxtLink to="/products/new" class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-bold text-white">＋ محصول جدید
      </NuxtLink>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white shadow-soft">
      <div class="flex gap-3 border-b p-4"><input v-model="search"
                                                  class="h-11 flex-1 rounded-xl border px-4 outline-none focus:border-brand-600"
                                                  placeholder="جست‌وجو در نام محصول…" @keyup.enter="refresh">
        <button class="rounded-xl border px-5 text-sm font-bold" @click="refresh">جست‌وجو</button>
      </div>
      <div v-if="pending" class="h-64 animate-pulse bg-slate-50"/>
      <EmptyState v-else-if="!data?.data?.length" title="هنوز محصولی ثبت نشده"
                  text="اولین محصول و تنوع‌های رنگ، سایز یا جنس آن را ایجاد کنید."/>
      <div v-else class="overflow-x-auto">
        <table class="w-full min-w-[800px] text-right text-sm">
          <thead class="bg-slate-50 text-xs text-slate-400">
          <tr>
            <th class="p-4">محصول</th>
            <th class="p-4">دسته‌بندی</th>
            <th class="p-4">تنوع</th>
            <th class="p-4">قیمت پایه</th>
            <th class="p-4">موجودی</th>
            <th class="p-4">وضعیت</th>
            <th class="p-4"></th>
          </tr>
          </thead>
          <tbody class="divide-y">
          <tr v-for="product in data.data" :key="product.id" class="hover:bg-slate-50">
            <td class="p-4"><p class="font-bold">{{ product.title }}</p>
              <p class="mt-1 text-xs text-slate-400">{{ product.slug }}</p></td>
            <td class="p-4 text-slate-500">{{ product.category?.title }}</td>
            <td class="p-4">{{ product.variants?.length || 0 }}</td>
            <td class="p-4 font-bold">{{ money(product.variants?.[0]?.price) }}</td>
            <td class="p-4">{{ product.variants?.reduce((a: number, v: any) => a + v.stock, 0) || 0 }}</td>
            <td class="p-4"><span
                :class="['rounded-lg px-2.5 py-1 text-xs font-bold',status[product.status]?.[1]]">{{ status[product.status]?.[0] }}</span>
            </td>
            <td class="p-4">
              <NuxtLink :to="`/products/${product.id}`" class="rounded-lg border px-3 py-2 text-xs font-bold">ویرایش
              </NuxtLink>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
