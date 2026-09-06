<script setup lang="ts">
import {useApi} from "../../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'مدیریت سفارش'});
const route = useRoute(), {request} = useApi();
const {
  data: o,
  refresh
} = await useAsyncData(`admin-order-${route.params.id}`, () => request<any>(`/admin/orders/${route.params.id}`));
const money = (v = 0) => new Intl.NumberFormat('fa-IR').format(v) + ' تومان', labels: any = {
  pending: 'در انتظار',
  confirmed: 'تأییدشده',
  processing: 'آماده‌سازی',
  shipped: 'ارسال‌شده',
  delivered: 'تحویل‌شده',
  cancelled: 'لغوشده',
  returned: 'مرجوع‌شده'
};
const shipment = reactive({carrier: 'پست پیشتاز', tracking_code: '', tracking_url: '', status: 'preparing', note: ''}),
    event = reactive({status: 'in_transit', title: 'مرسوله در مسیر است', location: '', description: ''});
watch(o, value => {
  if (value?.shipment) Object.assign(shipment, {
    carrier: value.shipment.carrier,
    tracking_code: value.shipment.tracking_code || '',
    tracking_url: value.shipment.tracking_url || '',
    status: value.shipment.status,
    note: value.shipment.note || ''
  })
}, {immediate: true});

async function saveShipment() {
  await request(`/admin/orders/${o.value.id}/shipment`, {method: 'PUT', body: shipment});
  await refresh()
}

async function addEvent() {
  await request(`/admin/orders/${o.value.id}/shipment/events`, {method: 'POST', body: event});
  event.description = '';
  await refresh()
}

async function status(value: string) {
  await request(`/admin/orders/${o.value.id}/status`, {method: 'PATCH', body: {status: value}});
  await refresh()
}
</script>
<template>
  <div v-if="o" class="space-y-6">
    <div class="flex items-center justify-between">
      <div><p class="text-xs text-slate-400">سفارش</p>
        <h1 class="mt-1 text-xl font-black">{{ o.number }}</h1></div>
      <NuxtLink to="/orders" class="text-sm font-bold text-brand-700">بازگشت</NuxtLink>
    </div>
    <div class="grid gap-6 xl:grid-cols-3">
      <section class="space-y-5 xl:col-span-2">
        <div class="rounded-2xl border bg-white p-5">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div><h2 class="font-black">{{ o.user?.name || 'مشتری' }}</h2>
              <p class="mt-1 text-sm text-slate-400">{{ o.user?.mobile }}</p></div>
            <select :value="o.status" class="rounded-xl border px-3 py-2 text-sm"
                    @change="status(($event.target as HTMLSelectElement).value)">
              <option v-for="s in ['pending','confirmed','processing','shipped','delivered','cancelled']" :key="s"
                      :value="s">{{ labels[s] }}
              </option>
            </select></div>
        </div>
        <div class="rounded-2xl border bg-white p-5"><h2 class="font-black">اقلام سفارش</h2>
          <div class="mt-4 divide-y">
            <div v-for="item in o.items" :key="item.id" class="flex justify-between py-3 text-sm">
              <div><b>{{ item.title }}</b>
                <p class="mt-1 text-xs text-slate-400">{{ item.sku }} • {{ item.quantity }} عدد</p></div>
              <b>{{ money(item.total) }}</b></div>
          </div>
        </div>
        <div class="rounded-2xl border bg-white p-5"><h2 class="font-black">تاریخچه سفارش</h2>
          <div class="mt-4 space-y-3">
            <div v-for="h in o.status_histories" :key="h.id" class="flex gap-3 text-sm"><span
                class="mt-1 size-2 rounded-full bg-brand-600"/>
              <div><b>{{ labels[h.to_status] || h.to_status }}</b>
                <p class="mt-1 text-xs text-slate-400">{{ h.note }} •
                  {{ new Date(h.created_at).toLocaleString('fa-IR') }}</p></div>
            </div>
          </div>
        </div>
      </section>
      <aside class="space-y-5">
        <form class="rounded-2xl border bg-white p-5" @submit.prevent="saveShipment"><h2 class="font-black">اطلاعات
          ارسال</h2><label class="mt-4 block text-xs font-bold">شرکت حمل<input v-model="shipment.carrier" required
                                                                               class="mt-2 h-10 w-full rounded-xl border px-3"></label><label
            class="mt-3 block text-xs font-bold">کد رهگیری<input v-model="shipment.tracking_code"
                                                                 class="mt-2 h-10 w-full rounded-xl border px-3"></label><label
            class="mt-3 block text-xs font-bold">لینک رهگیری<input v-model="shipment.tracking_url" dir="ltr"
                                                                   class="mt-2 h-10 w-full rounded-xl border px-3"></label><label
            class="mt-3 block text-xs font-bold">وضعیت<select v-model="shipment.status"
                                                              class="mt-2 h-10 w-full rounded-xl border px-3">
          <option value="preparing">آماده‌سازی</option>
          <option value="shipped">تحویل شرکت حمل</option>
          <option value="in_transit">در مسیر</option>
          <option value="out_for_delivery">در حال توزیع</option>
          <option value="delivered">تحویل‌شده</option>
          <option value="failed">ناموفق</option>
          <option value="returned">برگشت‌خورده</option>
        </select></label>
          <button class="mt-4 w-full rounded-xl bg-brand-700 py-3 text-sm font-bold text-white">ثبت اطلاعات ارسال
          </button>
        </form>
        <form v-if="o.shipment" class="rounded-2xl border bg-white p-5" @submit.prevent="addEvent"><h2
            class="font-black">رویداد رهگیری</h2><input v-model="event.title" required
                                                        class="mt-4 h-10 w-full rounded-xl border px-3"
                                                        placeholder="عنوان رویداد"><input v-model="event.location"
                                                                                          class="mt-3 h-10 w-full rounded-xl border px-3"
                                                                                          placeholder="موقعیت"><textarea
            v-model="event.description" rows="2" class="mt-3 w-full rounded-xl border p-3" placeholder="توضیحات"/>
          <button class="mt-3 w-full rounded-xl bg-slate-900 py-3 text-sm font-bold text-white">افزودن رویداد</button>
          <div class="mt-5 space-y-3 border-t pt-4">
            <div v-for="e in o.shipment.events" :key="e.id" class="text-xs"><b>{{ e.title }}</b>
              <p class="mt-1 text-slate-400">{{ e.location }} •
                {{ new Date(e.occurred_at).toLocaleString('fa-IR') }}</p></div>
          </div>
        </form>
      </aside>
    </div>
  </div>
</template>
