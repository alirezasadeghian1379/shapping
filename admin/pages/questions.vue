<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'پرسش‌های محصولات'});
const {request} = useApi();
const status = ref('pending');
const {
  data,
  refresh
} = await useAsyncData('admin-questions', () => request<any>('/admin/questions', {query: {status: status.value}}), {watch: [status]});
const answers = reactive<Record<number, string>>({});

async function answer(q: any, state = 'approved') {
  await request(`/admin/questions/${q.id}`, {
    method: 'PATCH',
    body: {answer: answers[q.id] || q.answer || 'پرسش رد شد.', status: state}
  });
  await refresh()
}</script>
<template>
  <div class="space-y-5">
    <SectionIntro title="پرسش‌های محصولات" text="پرسش‌های کاربران را پاسخ دهید یا برای انتشار رد کنید."/>
    <div class="flex gap-2">
      <button v-for="s in [['pending','در انتظار'],['approved','پاسخ‌داده‌شده'],['rejected','ردشده']]" :key="s[0]"
              :class="['rounded-xl px-4 py-2 text-sm font-bold',status===s[0]?'bg-brand-700 text-white':'border bg-white']"
              @click="status=s[0]">{{ s[1] }}
      </button>
    </div>
    <div class="space-y-4">
      <article v-for="q in data?.data" :key="q.id" class="rounded-2xl border bg-white p-5">
        <div class="flex flex-wrap justify-between gap-3">
          <div><p class="text-xs text-slate-400">{{ q.product_title }} • {{ q.user_name || q.mobile }}</p>
            <h2 class="mt-2 font-black">{{ q.question }}</h2></div>
          <span class="rounded-lg bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">{{ q.status }}</span></div>
        <textarea v-model="answers[q.id]" :placeholder="q.answer||'پاسخ مدیر…'" rows="3"
                  class="mt-4 w-full rounded-xl border p-3"/>
        <div class="mt-3 flex gap-2">
          <button class="rounded-xl bg-brand-700 px-4 py-2 text-sm font-bold text-white" @click="answer(q)">ثبت و انتشار
            پاسخ
          </button>
          <button class="rounded-xl bg-rose-50 px-4 py-2 text-sm font-bold text-rose-700" @click="answer(q,'rejected')">
            رد پرسش
          </button>
        </div>
      </article>
      <EmptyState v-if="!data?.data?.length" title="پرسشی در این وضعیت نیست"
                  text="پرسش‌های تازه کاربران در این بخش نمایش داده می‌شوند."/>
    </div>
  </div>
</template>
