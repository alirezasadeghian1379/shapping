<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'تنظیمات'});
const {request} = useApi();
const {data, pending} = await useAsyncData('settings', () => request<any>('/admin/settings'));
const labels: any = {general: 'عمومی', branding: 'هویت بصری', pages: 'صفحات ثابت', social: 'شبکه‌های اجتماعی'};
const form = ref<any[]>([]);
watchEffect(() => {
  if (data.value && !form.value.length) form.value = Object.entries(data.value).flatMap(([group, items]: any) => items.map((x: any) => ({
    ...x,
    group
  })))
});
const saving = ref(false), done = ref(false);

async function save() {
  saving.value = true;
  await request('/admin/settings', {
    method: 'PUT',
    body: {settings: form.value.map(x => ({group: x.group, key: x.key, value: x.value, is_public: x.is_public}))}
  });
  saving.value = false;
  done.value = true;
  setTimeout(() => done.value = false, 2000)
}</script>
<template>
  <div class="space-y-5">
    <SectionIntro title="تنظیمات فروشگاه" text="نام، لوگو، رنگ، صفحات و راه‌های ارتباطی را بدون تغییر کد عوض کنید."/>
    <div v-if="pending" class="h-64 animate-pulse rounded-2xl bg-slate-200"/>
    <form v-else class="space-y-5" @submit.prevent="save">
      <section v-for="(items,group) in data" :key="group" class="rounded-2xl border bg-white p-5"><h3
          class="mb-5 font-black">{{ labels[group] || group }}</h3>
        <div class="grid gap-5 md:grid-cols-2"><label v-for="item in form.filter(x=>x.group===group)" :key="item.key"
                                                      class="block"><span
            class="mb-2 block text-xs font-bold text-slate-500">{{ item.key }}</span><textarea
            v-if="['about_us','contact_us'].includes(item.key)" v-model="item.value" rows="4"
            class="w-full rounded-xl border p-3 outline-none focus:border-brand-600"/><input v-else v-model="item.value"
                                                                                             class="h-11 w-full rounded-xl border px-3 outline-none focus:border-brand-600"/></label>
        </div>
      </section>
      <div class="sticky bottom-4 flex justify-end">
        <button :disabled="saving" class="rounded-xl bg-brand-700 px-7 py-3 font-bold text-white shadow-xl">
          {{ done ? 'ذخیره شد ✓' : saving ? 'در حال ذخیره…' : 'ذخیره تنظیمات' }}
        </button>
      </div>
    </form>
  </div>
</template>
