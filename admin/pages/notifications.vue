<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'ارسال اعلان'});
const {request} = useApi();
const {data, refresh} = await useAsyncData('admin-notifications', () => request<any>('/admin/notifications'));
const form = reactive({title: '', message: '', action_url: ''}), sent = ref('');

async function send() {
  const r = await request<any>('/admin/notifications', {method: 'POST', body: form});
  sent.value = `اعلان برای ${r.recipients} کاربر ارسال شد.`;
  Object.assign(form, {title: '', message: '', action_url: ''});
  await refresh()
}</script>
<template>
  <div class="space-y-6">
    <SectionIntro title="اعلان‌های عمومی" text="خبرها، جشنواره‌ها و پیام‌های مهم را داخل حساب همه کاربران نمایش دهید."/>
    <form class="rounded-2xl border bg-white p-6" @submit.prevent="send">
      <div class="grid gap-4 md:grid-cols-2"><input v-model="form.title" required class="h-11 rounded-xl border px-3"
                                                    placeholder="عنوان اعلان"><input v-model="form.action_url"
                                                                                     class="h-11 rounded-xl border px-3"
                                                                                     placeholder="لینک مقصد، مثال: /products">
      </div>
      <textarea v-model="form.message" required rows="4" class="mt-4 w-full rounded-xl border p-3"
                placeholder="متن اعلان…"/>
      <p v-if="sent" class="mt-3 text-sm text-emerald-700">{{ sent }}</p>
      <button class="mt-4 rounded-xl bg-brand-700 px-6 py-3 font-bold text-white">ارسال برای همه کاربران</button>
    </form>
    <section class="rounded-2xl border bg-white p-5"><h2 class="font-black">ارسال‌های قبلی</h2>
      <div class="mt-4 divide-y">
        <div v-for="item in data?.data" :key="item.sent_at" class="py-4">
          <div class="flex justify-between"><b>{{ item.title }}</b><span
              class="text-xs text-slate-400">{{ item.recipients }} گیرنده</span></div>
          <p class="mt-2 text-sm text-slate-500">{{ item.message }}</p></div>
      </div>
    </section>
  </div>
</template>
