<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'مدیریت نظرات'});
const {request} = useApi();
const status = ref('pending'), answers = reactive<Record<number, string>>({});
const {
  data,
  refresh
} = await useAsyncData('admin-comments', () => request<any>('/admin/comments', {query: {status: status.value}}), {watch: [status]});

async function moderate(id: number, state: string) {
  await request(`/admin/comments/${id}`, {method: 'PATCH', body: {status: state}});
  await refresh()
}

async function reply(id: number) {
  await request(`/admin/comments/${id}/replies`, {method: 'POST', body: {body: answers[id]}});
  answers[id] = '';
  await refresh()
}</script>
<template>
  <div class="space-y-5">
    <SectionIntro title="نظرات کاربران" text="دیدگاه‌ها را بررسی کنید، منتشر یا رد کنید و از طرف فروشگاه پاسخ بدهید."/>
    <div class="flex gap-2">
      <button v-for="s in [['pending','در انتظار'],['approved','منتشرشده'],['rejected','ردشده']]" :key="s[0]"
              :class="['rounded-xl px-4 py-2 text-sm font-bold',status===s[0]?'bg-brand-700 text-white':'border bg-white']"
              @click="status=s[0]">{{ s[1] }}
      </button>
    </div>
    <div class="space-y-4">
      <article v-for="c in data?.data" :key="c.id" class="rounded-2xl border bg-white p-5">
        <div class="flex justify-between">
          <div><b>{{ c.user_name || c.mobile }}</b>
            <p class="mt-1 text-xs text-slate-400">{{ c.commentable_type.includes('Product') ? 'محصول' : 'مقاله' }} •
              امتیاز {{ c.rating || '—' }}</p></div>
          <span v-if="c.is_buyer" class="h-fit rounded-lg bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">خریدار</span>
        </div>
        <p class="mt-4 text-sm leading-7">{{ c.body }}</p>
        <div class="mt-4 flex gap-2">
          <button v-if="c.status!=='approved'" class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white"
                  @click="moderate(c.id,'approved')">تأیید
          </button>
          <button v-if="c.status!=='rejected'" class="rounded-lg bg-rose-50 px-4 py-2 text-xs font-bold text-rose-700"
                  @click="moderate(c.id,'rejected')">رد
          </button>
        </div>
        <form v-if="c.status==='approved'&&!c.parent_id" class="mt-4 flex gap-2 border-t pt-4"
              @submit.prevent="reply(c.id)"><input v-model="answers[c.id]" required
                                                   class="h-10 flex-1 rounded-xl border px-3 text-sm"
                                                   placeholder="پاسخ فروشگاه…">
          <button class="rounded-xl bg-slate-900 px-4 text-xs font-bold text-white">ارسال پاسخ</button>
        </form>
      </article>
      <EmptyState v-if="!data?.data?.length" title="نظری وجود ندارد"
                  text="دیدگاه‌های کاربران در این بخش نمایش داده می‌شوند."/>
    </div>
  </div>
</template>
