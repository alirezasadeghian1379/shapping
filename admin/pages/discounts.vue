<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'کدهای تخفیف'});
const {request} = useApi();
const {data, pending} = await useAsyncData('discounts', () => request<any>('/admin/discounts'));
const money = (v = 0) => new Intl.NumberFormat('fa-IR').format(v)</script>
<template>
  <div class="space-y-5">
    <SectionIntro title="کدهای تخفیف" text="کمپین‌های درصدی و مبلغ ثابت را با سقف مصرف کنترل کنید." action="کد تخفیف"/>
    <div class="overflow-hidden rounded-2xl border bg-white">
      <div v-if="pending" class="h-60 animate-pulse bg-slate-50"/>
      <EmptyState v-else-if="!data?.data?.length" title="کد تخفیفی ندارید"
                  text="برای اولین کمپین فروش یک کد درصدی یا مبلغ ثابت بسازید."/>
      <div v-else class="grid gap-4 p-4 md:grid-cols-2 xl:grid-cols-3">
        <article v-for="d in data.data" :key="d.id" class="rounded-2xl border border-dashed p-5">
          <div class="flex justify-between"><code dir="ltr"
                                                  class="rounded-lg bg-slate-100 px-3 py-1 font-bold">{{ d.code }}</code><span
              :class="d.is_active?'text-emerald-600':'text-slate-400'">●</span></div>
          <h3 class="mt-4 font-black">{{ d.title }}</h3>
          <p class="mt-2 text-sm text-slate-500">
            {{ d.type === 'percent' ? `${d.value} درصد تا سقف ${money(d.max_discount)} تومان` : `${money(d.value)} تومان` }}</p>
          <p class="mt-4 text-xs text-slate-400">{{ d.used_count }} بار استفاده شده</p></article>
      </div>
    </div>
  </div>
</template>
