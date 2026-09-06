<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'درخواست‌های مرجوعی'});
const {request} = useApi();
const status = ref('pending'), note = reactive<Record<number, string>>({}),
    restock = reactive<Record<number, boolean>>({});
const {
  data,
  pending,
  refresh
} = await useAsyncData('admin-returns', () => request<any>('/admin/returns', {query: {status: status.value || undefined}}), {watch: [status]});
const reasons: any = {
  damaged: 'آسیب‌دیده',
  wrong_item: 'کالای اشتباه',
  not_as_described: 'مغایرت با توضیحات',
  quality: 'کیفیت نامناسب',
  other: 'سایر'
};
const labels: any = {
  pending: 'در انتظار',
  reviewing: 'در حال بررسی',
  approved: 'تأییدشده',
  rejected: 'ردشده',
  received: 'دریافت‌شده',
  refund_processing: 'در حال بازپرداخت',
  refunded: 'بازپرداخت‌شده'
};
const next: any = {
  pending: [['reviewing', 'شروع بررسی'], ['approved', 'تأیید درخواست'], ['rejected', 'رد درخواست']],
  reviewing: [['approved', 'تأیید درخواست'], ['rejected', 'رد درخواست']],
  approved: [['received', 'ثبت دریافت کالا']],
  received: [['refunded', 'ارسال بازپرداخت به درگاه']]
};

async function update(item: any, state: string) {
  if (state === 'refunded') await request(`/admin/returns/${item.id}/refund`, {method: 'POST'}); else await request(`/admin/returns/${item.id}`, {
    method: 'PATCH',
    body: {status: state, admin_note: note[item.id] || undefined, restock: state === 'received' && !!restock[item.id]}
  });
  await refresh()
}
</script>
<template>
  <div class="space-y-5">
    <SectionIntro title="درخواست‌های مرجوعی"
                  text="درخواست مشتری را بررسی کنید، دریافت کالا و بازگشت موجودی را ثبت کنید و پرونده را تا بازپرداخت پیگیری کنید."/>
    <div class="flex gap-2 overflow-auto">
      <button
          v-for="item in [['pending','در انتظار'],['reviewing','در حال بررسی'],['approved','تأییدشده'],['received','دریافت‌شده'],['refunded','بازپرداخت'],['rejected','ردشده'],['','همه']]"
          :key="item[0]"
          :class="['shrink-0 rounded-xl px-4 py-2 text-sm font-bold',status===item[0]?'bg-brand-700 text-white':'border bg-white']"
          @click="status=item[0]">{{ item[1] }}
      </button>
    </div>
    <div v-if="pending" class="h-64 animate-pulse rounded-2xl bg-slate-100"/>
    <div v-else class="space-y-4">
      <article v-for="r in data?.data" :key="r.id" class="rounded-2xl border bg-white p-5">
        <div class="flex flex-wrap justify-between gap-3">
          <div><p class="text-xs text-slate-400">{{ r.number }} • سفارش {{ r.order?.number }}</p>
            <h2 class="mt-2 font-black">{{ r.user?.name || r.user?.mobile }} — {{ reasons[r.reason] }}</h2></div>
          <span
              class="h-fit rounded-lg bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">{{ labels[r.status] }}</span>
        </div>
        <p v-if="r.description" class="mt-4 rounded-xl bg-slate-50 p-4 text-sm leading-7">{{ r.description }}</p>
        <div class="mt-4 divide-y rounded-xl border px-4">
          <div v-for="i in r.items" :key="i.id" class="flex justify-between py-3 text-sm">
            <span>{{ i.order_item?.title }}</span><b>{{ i.quantity }} عدد</b></div>
        </div>
        <textarea v-if="next[r.status]" v-model="note[r.id]" rows="2" class="mt-4 w-full rounded-xl border p-3 text-sm"
                  placeholder="یادداشت مدیر برای مشتری…"/><label v-if="r.status==='approved'"
                                                                 class="mt-3 flex items-center gap-2 text-sm font-bold"><input
          v-model="restock[r.id]" type="checkbox" class="accent-brand-700">بازگرداندن اقلام سالم به موجودی انبار</label>
        <div v-if="next[r.status]" class="mt-4 flex flex-wrap gap-2">
          <button v-for="action in next[r.status]" :key="action[0]"
                  :class="['rounded-xl px-4 py-2 text-sm font-bold text-white',action[0]==='rejected'?'bg-rose-600':'bg-brand-700']"
                  @click="update(r,action[0])">{{ action[1] }}
          </button>
        </div>
      </article>
      <EmptyState v-if="!data?.data?.length" title="درخواستی وجود ندارد"
                  text="درخواست‌های مرجوعی مشتریان در این بخش نمایش داده می‌شوند."/>
    </div>
  </div>
</template>
