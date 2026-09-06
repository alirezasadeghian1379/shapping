<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'کاربران و مدیران'});
const {request} = useApi();
const {data, pending} = await useAsyncData('users', () => request<any>('/admin/users'))</script>
<template>
  <div class="space-y-5">
    <div><h2 class="text-xl font-black">کاربران و مدیران</h2>
      <p class="mt-1 text-sm text-slate-400">هویت‌ها یکپارچه‌اند و مدیران با نقش مشخص می‌شوند.</p></div>
    <div class="overflow-hidden rounded-2xl border bg-white">
      <div v-if="pending" class="h-64 animate-pulse bg-slate-50"/>
      <EmptyState v-else-if="!data?.data?.length" title="کاربری پیدا نشد"
                  text="کاربران بعد از اولین ورود OTP در این فهرست قرار می‌گیرند."/>
      <div v-else class="divide-y">
        <div v-for="u in data.data" :key="u.id"
             class="flex flex-col justify-between gap-3 p-4 sm:flex-row sm:items-center">
          <div class="flex items-center gap-3">
            <div class="grid size-11 place-items-center rounded-xl bg-brand-50 font-black text-brand-700">
              {{ (u.name || 'ک')[0] }}
            </div>
            <div><p class="font-bold">{{ u.name || 'پروفایل تکمیل‌نشده' }}</p>
              <p dir="ltr" class="text-right text-xs text-slate-400">{{ u.mobile }}</p></div>
          </div>
          <div class="flex items-center gap-2"><span v-for="r in u.roles" :key="r.id"
                                                     class="rounded-lg bg-violet-50 px-2 py-1 text-xs font-bold text-violet-700">{{
              r.label
            }}</span><span
              :class="['rounded-lg px-2 py-1 text-xs font-bold',u.is_active?'bg-emerald-50 text-emerald-700':'bg-rose-50 text-rose-700']">{{ u.is_active ? 'فعال' : 'مسدود' }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
