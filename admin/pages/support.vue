<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'مرکز پشتیبانی'});
const {request} = useApi();
const status = ref(''), selected = ref<any>(), body = ref('');
const {
  data,
  refresh
} = await useAsyncData('support-tickets', () => request<any>('/admin/support/tickets', {query: {status: status.value || undefined}}), {watch: [status]});
const labels: any = {
  open: 'باز',
  customer_reply: 'پاسخ مشتری',
  pending: 'در انتظار',
  answered: 'پاسخ داده‌شده',
  closed: 'بسته'
};

async function open(id: number) {
  selected.value = await request(`/admin/support/tickets/${id}`)
}

async function reply() {
  await request(`/admin/support/tickets/${selected.value.id}/replies`, {
    method: 'POST',
    body: {body: body.value, status: 'answered'}
  });
  body.value = '';
  await open(selected.value.id);
  await refresh()
}

async function close() {
  await request(`/admin/support/tickets/${selected.value.id}`, {method: 'PATCH', body: {status: 'closed'}});
  selected.value = null;
  await refresh()
}</script>
<template>
  <div class="space-y-5">
    <SectionIntro title="مرکز پشتیبانی"
                  text="گفت‌وگوهای مشتریان درباره سفارش، پرداخت و مرجوعی را از یک بخش مدیریت کنید."/>
    <div class="grid gap-5 lg:grid-cols-[360px_1fr]">
      <section class="overflow-hidden rounded-2xl border bg-white">
        <div class="border-b p-3"><select v-model="status" class="h-10 w-full rounded-xl border px-3 text-sm">
          <option value="">همه تیکت‌ها</option>
          <option value="open">باز</option>
          <option value="customer_reply">پاسخ مشتری</option>
          <option value="answered">پاسخ‌داده‌شده</option>
          <option value="closed">بسته</option>
        </select></div>
        <button v-for="t in data?.data" :key="t.id" class="block w-full border-b p-4 text-right hover:bg-slate-50"
                @click="open(t.id)">
          <div class="flex justify-between gap-2"><b class="truncate text-sm">{{ t.subject }}</b><span
              class="text-[10px] text-brand-700">{{ labels[t.status] || t.status }}</span></div>
          <p class="mt-2 text-xs text-slate-400">{{ t.user?.name || t.user?.mobile }} • {{ t.number }}</p></button>
        <EmptyState v-if="!data?.data?.length" title="تیکتی وجود ندارد"
                    text="پیام‌های مشتریان اینجا نمایش داده می‌شوند."/>
      </section>
      <section class="rounded-2xl border bg-white p-5">
        <EmptyState v-if="!selected" title="یک گفت‌وگو را انتخاب کنید"
                    text="برای مشاهده پیام‌ها یکی از تیکت‌ها را باز کنید."/>
        <template v-else>
          <div class="flex justify-between border-b pb-4">
            <div><h2 class="font-black">{{ selected.subject }}</h2>
              <p class="mt-1 text-xs text-slate-400">{{ selected.number }} • {{ selected.user?.mobile }}</p></div>
            <button class="text-xs font-bold text-rose-600" @click="close">بستن تیکت</button>
          </div>
          <div class="my-5 max-h-[480px] space-y-3 overflow-auto">
            <div v-for="m in selected.messages" :key="m.id"
                 :class="['max-w-[85%] rounded-2xl p-4 text-sm leading-7',m.is_staff?'mr-auto bg-brand-700 text-white':'ml-auto bg-slate-100']">
              <p>{{ m.body }}</p><span
                class="mt-2 block text-[10px] opacity-60">{{ new Date(m.created_at).toLocaleString('fa-IR') }}</span>
            </div>
          </div>
          <form class="border-t pt-4" @submit.prevent="reply"><textarea v-model="body" required rows="3"
                                                                        class="w-full rounded-xl border p-3"
                                                                        placeholder="پاسخ مدیر…"/>
            <button class="mt-3 rounded-xl bg-brand-700 px-5 py-2.5 text-sm font-bold text-white">ارسال پاسخ</button>
          </form>
        </template>
      </section>
    </div>
  </div>
</template>
