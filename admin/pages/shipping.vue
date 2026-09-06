<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'ارسال و بازه‌ها'});
const {request} = useApi();
const [{data: methods}, {data: slots}] = await Promise.all([useAsyncData('shipping-methods', () => request<any[]>('/admin/shipping/methods')), useAsyncData('shipping-slots', () => request<any>('/admin/shipping/slots'))]);
const money = (v = 0) => new Intl.NumberFormat('fa-IR').format(v) + ' تومان'</script>
<template>
  <div class="space-y-6">
    <SectionIntro title="ارسال و بازه‌های تحویل" text="روش، هزینه، آستانه ارسال رایگان و ظرفیت روزانه را تنظیم کنید."
                  action="روش ارسال"/>
    <div class="grid gap-6 xl:grid-cols-2">
      <section class="rounded-2xl border bg-white p-5"><h3 class="mb-4 font-black">روش‌های ارسال</h3>
        <EmptyState v-if="!methods?.length" title="روش ارسالی ندارید" text="پست، پیک یا تحویل حضوری را اضافه کنید."/>
        <div v-else class="space-y-3">
          <div v-for="m in methods" :key="m.id" class="flex items-center justify-between rounded-xl border p-4">
            <div><p class="font-bold">{{ m.title }}</p>
              <p class="mt-1 text-xs text-slate-400">{{ m.description }}</p></div>
            <span class="text-sm font-bold">{{ money(m.base_cost) }}</span></div>
        </div>
      </section>
      <section class="rounded-2xl border bg-white p-5"><h3 class="mb-4 font-black">بازه‌های پیش رو</h3>
        <EmptyState v-if="!slots?.data?.length" title="بازه‌ای تعریف نشده"
                    text="ظرفیت ارسال روزهای آینده را برنامه‌ریزی کنید."/>
        <div v-else class="space-y-3">
          <div v-for="s in slots.data" :key="s.id" class="flex justify-between rounded-xl bg-slate-50 p-4 text-sm">
            <span>{{ new Date(s.date).toLocaleDateString('fa-IR') }}، {{ s.starts_at }} تا {{ s.ends_at }}</span><span
              class="font-bold">{{ s.reserved }} / {{ s.capacity }}</span></div>
        </div>
      </section>
    </div>
  </div>
</template>
