<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'تحلیل جستجو'});
const {request} = useApi(), days = ref(30);
const {data} = await useAsyncData('discovery-analytics', () => request<any>('/admin/discovery-analytics', {query: {days: days.value}}), {watch: [days]})</script>
<template>
  <div class="space-y-6">
    <SectionIntro title="تحلیل جستجو و اعلان‌ها"
                  text="عبارت‌های پرتقاضا و جستجوهای بدون نتیجه را برای تصمیم‌گیری درباره موجودی بررسی کنید."/>
    <select v-model="days" class="rounded-xl border bg-white px-4 py-2 text-sm">
      <option :value="7">۷ روز اخیر</option>
      <option :value="30">۳۰ روز اخیر</option>
      <option :value="90">۹۰ روز اخیر</option>
    </select>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <StatCard title="کل جستجوها" :value="data?.summary.searches||0"/>
      <StatCard title="بدون نتیجه" :value="data?.summary.zero_results||0"/>
      <StatCard title="اعلان‌های فعال" :value="data?.summary.active_alerts||0"/>
      <StatCard title="اعلان‌های ارسال‌شده" :value="data?.summary.sent_alerts||0"/>
    </div>
    <div class="grid gap-6 lg:grid-cols-2">
      <section class="rounded-2xl border bg-white p-5"><h2 class="font-black">جستجوهای پرتکرار</h2>
        <div class="mt-4 divide-y">
          <div v-for="item in data?.top_searches" :key="item.query" class="flex justify-between py-3 text-sm">
            <span>{{ item.query }}</span><b>{{ item.searches }} بار</b></div>
          <EmptyState v-if="!data?.top_searches?.length" title="داده‌ای ثبت نشده"
                      text="پس از جستجوی کاربران، آمار اینجا نمایش داده می‌شود."/>
        </div>
      </section>
      <section class="rounded-2xl border bg-white p-5"><h2 class="font-black">فرصت‌های موجودی</h2>
        <p class="mt-1 text-xs text-slate-400">عبارت‌هایی که هیچ محصولی برایشان پیدا نشده است.</p>
        <div class="mt-4 divide-y">
          <div v-for="item in data?.zero_result_searches" :key="item.query" class="flex justify-between py-3 text-sm">
            <span>{{ item.query }}</span><b class="text-rose-600">{{ item.searches }} بار</b></div>
          <EmptyState v-if="!data?.zero_result_searches?.length" title="جستجوی ناموفق ندارید"
                      text="در بازه انتخاب‌شده همه جستجوها نتیجه داشته‌اند."/>
        </div>
      </section>
    </div>
  </div>
</template>
