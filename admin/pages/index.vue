<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'نمای کلی'})
const {request} = useApi();
const {data, pending} = await useAsyncData('dashboard', () => request<any>('/admin/dashboard'))
const money = (v = 0) => new Intl.NumberFormat('fa-IR').format(v) + ' تومان'
</script>
<template>
  <div class="space-y-6">
    <section
        class="overflow-hidden rounded-3xl bg-gradient-to-l from-brand-900 to-brand-600 p-6 text-white shadow-xl shadow-brand-700/10 lg:p-8">
      <div class="flex flex-col justify-between gap-6 md:flex-row md:items-center">
        <div><p class="text-sm text-brand-100">گزارش لحظه‌ای کسب‌وکار</p>
          <h2 class="mt-2 text-2xl font-black">سلام مدیر، فروشگاه آماده‌ی رشد است 👋</h2>
          <p class="mt-3 max-w-xl text-sm leading-7 text-brand-100">سفارش‌های تازه، موجودی رو به پایان و عملکرد فروش را
            از همین‌جا کنترل کنید.</p></div>
        <NuxtLink to="/products" class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-brand-900">افزودن محصول
          جدید
        </NuxtLink>
      </div>
    </section>
    <div v-if="pending" class="h-32 animate-pulse rounded-2xl bg-slate-200"/>
    <section v-else class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <StatCard label="فروش امروز" :value="money(data?.today_sales)" hint="سفارش‌های پرداخت‌شده">↗</StatCard>
      <StatCard label="سفارش در انتظار" :value="data?.pending_orders || 0" hint="نیازمند بررسی مدیر"
                tone="bg-amber-50 text-amber-700">▤
      </StatCard>
      <StatCard label="کل محصولات" :value="data?.products || 0" hint="پیش‌نویس و منتشرشده"
                tone="bg-blue-50 text-blue-700">◇
      </StatCard>
      <StatCard label="کمبود موجودی" :value="data?.low_stock || 0" hint="کمتر از آستانه هشدار"
                tone="bg-rose-50 text-rose-700">!
      </StatCard>
    </section>
    <section class="grid gap-6 xl:grid-cols-3">
      <div class="rounded-2xl border bg-white p-6 xl:col-span-2">
        <div class="mb-6 flex items-center justify-between"><h3 class="font-black">کارهای پیشنهادی امروز</h3><span
            class="text-xs text-slate-400">به ترتیب اهمیت</span></div>
        <div class="space-y-3">
          <NuxtLink
              v-for="task in [['سفارش‌های در انتظار را بررسی کنید','/orders','bg-amber-50 text-amber-700'],['موجودی محصولات را به‌روز کنید','/products','bg-rose-50 text-rose-700'],['دیدگاه‌های جدید را تأیید کنید','/content','bg-blue-50 text-blue-700']]"
              :key="task[0]" :to="task[1]"
              class="flex items-center justify-between rounded-xl border border-slate-100 p-4 hover:bg-slate-50">
            <div class="flex items-center gap-3"><span
                :class="['grid size-9 place-items-center rounded-xl', task[2]]">✓</span><span class="text-sm font-bold">{{
                task[0]
              }}</span></div>
            <span class="text-slate-300">←</span></NuxtLink>
        </div>
      </div>
      <div class="rounded-2xl border bg-white p-6"><h3 class="font-black">وضعیت سیستم</h3>
        <div class="mt-6 space-y-5">
          <div v-for="item in [['API فروشگاه','فعال'],['درگاه آزمایشی','زرین‌پال'],['سرویس پیامک','حالت توسعه']]"
               :key="item[0]" class="flex items-center justify-between text-sm"><span class="text-slate-500">{{
              item[0]
            }}</span><span class="rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{
              item[1]
            }}</span></div>
        </div>
      </div>
    </section>
  </div>
</template>
