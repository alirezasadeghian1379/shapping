<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'سفارش‌ها'});
const {request} = useApi();
const search = ref(''), status = ref('');
const query = computed(() => ({search: search.value || undefined, status: status.value || undefined}));
const {
  data,
  pending
} = await useAsyncData('orders', () => request<any>('/admin/orders', {query: query.value}), {watch: [query]});
const money = (v = 0) => new Intl.NumberFormat('fa-IR').format(v) + ' تومان', labels: any = {
  pending: 'در انتظار',
  confirmed: 'تأییدشده',
  processing: 'آماده‌سازی',
  shipped: 'ارسال‌شده',
  delivered: 'تحویل‌شده',
  cancelled: 'لغوشده',
  returned: 'مرجوع‌شده'
}</script>
<template>
  <div class="space-y-5">
    <SectionIntro title="سفارش‌ها" text="از ثبت سفارش تا تحویل به مشتری را دنبال کنید."/>
    <div class="flex gap-3 rounded-2xl border bg-white p-4"><input v-model="search"
                                                                   class="h-11 flex-1 rounded-xl border px-4"
                                                                   placeholder="شماره سفارش یا موبایل"><select
        v-model="status" class="rounded-xl border px-3 text-sm">
      <option value="">همه وضعیت‌ها</option>
      <option v-for="s in ['pending','confirmed','processing','shipped','delivered','cancelled','returned']" :key="s"
              :value="s">{{ labels[s] }}
      </option>
    </select></div>
    <div class="overflow-hidden rounded-2xl border bg-white">
      <div v-if="pending" class="h-64 animate-pulse bg-slate-50"/>
      <EmptyState v-else-if="!data?.data?.length" title="سفارشی وجود ندارد"
                  text="سفارش‌های مشتریان پس از ثبت در این بخش نمایش داده می‌شوند."/>
      <div v-else class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-right text-sm">
          <thead class="bg-slate-50 text-xs text-slate-400">
          <tr>
            <th class="p-4">شماره سفارش</th>
            <th class="p-4">مشتری</th>
            <th class="p-4">اقلام</th>
            <th class="p-4">مبلغ</th>
            <th class="p-4">وضعیت</th>
            <th class="p-4"></th>
          </tr>
          </thead>
          <tbody class="divide-y">
          <tr v-for="o in data.data" :key="o.id">
            <td class="p-4 font-bold">{{ o.number }}</td>
            <td class="p-4"><p>{{ o.user?.name || 'تکمیل‌نشده' }}</p>
              <p dir="ltr" class="text-right text-xs text-slate-400">{{ o.user?.mobile }}</p></td>
            <td class="p-4">{{ o.items_count }}</td>
            <td class="p-4 font-bold">{{ money(o.payable_amount) }}</td>
            <td class="p-4"><span
                class="rounded-lg bg-amber-50 px-2 py-1 text-xs font-bold text-amber-700">{{ labels[o.status] }}</span>
            </td>
            <td class="p-4">
              <NuxtLink :to="`/orders/${o.id}`" class="font-bold text-brand-700">مدیریت ←</NuxtLink>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
