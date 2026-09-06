<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'گزارش فروش'});
const {request} = useApi();
const now = new Date(), from = ref(new Date(now.getTime() - 29 * 86400000).toISOString().slice(0, 10)),
    to = ref(now.toISOString().slice(0, 10));
const query = computed(() => ({from: from.value, to: to.value}));
const {
  data,
  pending
} = await useAsyncData('sales-report', () => request<any>('/admin/reports/sales', {query: query.value}), {watch: [query]});
const money = (v = 0) => new Intl.NumberFormat('fa-IR').format(v) + ' تومان';
const maxRevenue = computed(() => Math.max(...(data.value?.daily || []).map((d: any) => Number(d.revenue)), 1));

async function download() {
  const blob = await request<Blob>('/admin/reports/sales/export', {query: query.value, responseType: 'blob'});
  const url = URL.createObjectURL(blob), link = document.createElement('a');
  link.href = url;
  link.download = 'sales-report.csv';
  link.click();
  URL.revokeObjectURL(url)
}
</script>
<template>
  <div class="space-y-6">
    <SectionIntro title="گزارش فروش" text="عملکرد مالی، روند روزانه و محصولات پرفروش را در بازه دلخواه بررسی کنید."/>
    <div class="flex flex-wrap items-end gap-3 rounded-2xl border bg-white p-4"><label class="text-xs font-bold">از
      تاریخ<input v-model="from" type="date" class="mt-2 block h-10 rounded-xl border px-3"></label><label
        class="text-xs font-bold">تا تاریخ<input v-model="to" type="date"
                                                 class="mt-2 block h-10 rounded-xl border px-3"></label>
      <button class="h-10 rounded-xl bg-slate-900 px-5 text-sm font-bold text-white" @click="download">دریافت CSV
      </button>
    </div>
    <div v-if="pending" class="h-32 animate-pulse rounded-2xl bg-slate-200"/>
    <template v-else>
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard label="فروش خالص" :value="money(data?.summary.revenue)" hint="سفارش‌های پرداخت‌شده">↗</StatCard>
        <StatCard label="تعداد سفارش" :value="data?.summary.orders||0" hint="در بازه انتخاب‌شده">▤</StatCard>
        <StatCard label="میانگین سبد" :value="money(data?.summary.average_order)" hint="میانگین مبلغ هر سفارش">◇
        </StatCard>
        <StatCard label="تخفیف‌ها" :value="money(data?.summary.discounts)" hint="جمع تخفیف اعمال‌شده">٪</StatCard>
      </div>
      <section class="rounded-2xl border bg-white p-6"><h2 class="font-black">روند فروش روزانه</h2>
        <div v-if="data?.daily?.length" class="mt-6 flex h-56 items-end gap-2 overflow-x-auto border-b pb-1">
          <div v-for="day in data.daily" :key="day.date"
               class="group flex min-w-10 flex-1 flex-col items-center justify-end"><span
              class="mb-2 hidden text-[10px] font-bold group-hover:block">{{ money(day.revenue) }}</span>
            <div class="w-full rounded-t-lg bg-brand-500 transition hover:bg-brand-700"
                 :style="{height:`${Math.max(Number(day.revenue)/maxRevenue*180,4)}px`}"/>
            <span class="mt-2 text-[10px] text-slate-400">{{
                new Date(day.date).toLocaleDateString('fa-IR', {
                  month: 'numeric',
                  day: 'numeric'
                })
              }}</span></div>
        </div>
        <EmptyState v-else title="فروشی ثبت نشده" text="در بازه انتخاب‌شده سفارش پرداخت‌شده‌ای وجود ندارد."/>
      </section>
      <section class="rounded-2xl border bg-white p-6"><h2 class="font-black">محصولات پرفروش</h2>
        <div class="mt-4 divide-y">
          <div v-for="(p,i) in data?.top_products" :key="p.product_id"
               class="grid grid-cols-[32px_1fr_auto] items-center gap-3 py-3 text-sm"><span
              class="grid size-7 place-items-center rounded-lg bg-slate-100 text-xs font-black">{{ i + 1 }}</span><span
              class="font-bold">{{ p.title }}</span>
            <div class="text-left"><b>{{ p.quantity }} عدد</b>
              <p class="text-xs text-slate-400">{{ money(p.revenue) }}</p></div>
          </div>
        </div>
      </section>
    </template>
  </div>
</template>
