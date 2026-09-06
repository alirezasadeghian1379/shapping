<script setup lang="ts">
import {useApi} from "../composables/useApi";

definePageMeta({middleware: 'auth'});
useHead({title: 'مدیریت موجودی'});
const {request} = useApi();
const search = ref(''), lowStock = ref(false), editing = ref<any>(),
    form = reactive({quantity: 1, reason: 'purchase', note: ''}), saving = ref(false);
const query = computed(() => ({search: search.value || undefined, low_stock: lowStock.value ? 1 : undefined}));
const {
  data,
  pending,
  refresh
} = await useAsyncData('inventory', () => request<any>('/admin/inventory', {query: query.value}), {watch: [query]});
const number = (v: number) => new Intl.NumberFormat('fa-IR').format(v);

async function adjust() {
  saving.value = true;
  try {
    await request(`/admin/inventory/${editing.value.id}`, {method: 'PATCH', body: form});
    editing.value = null;
    form.quantity = 1;
    form.note = '';
    await refresh()
  } finally {
    saving.value = false
  }
}
</script>
<template>
  <div class="space-y-5">
    <SectionIntro title="مدیریت موجودی"
                  text="موجودی همه تنوع‌ها را کنترل کنید و تغییرات انبار را با دلیل مشخص ثبت کنید."/>
    <div class="flex flex-col gap-3 rounded-2xl border bg-white p-4 sm:flex-row"><input v-model="search"
                                                                                        class="h-11 flex-1 rounded-xl border px-4"
                                                                                        placeholder="جستجو با نام محصول یا SKU"><label
        class="flex items-center gap-2 rounded-xl bg-slate-50 px-4 text-sm font-bold"><input v-model="lowStock"
                                                                                             type="checkbox"
                                                                                             class="accent-brand-700">فقط
      کم‌موجودها</label></div>
    <div class="overflow-hidden rounded-2xl border bg-white">
      <div v-if="pending" class="h-64 animate-pulse bg-slate-50"/>
      <EmptyState v-else-if="!data?.data?.length" title="موردی پیدا نشد" text="فیلتر یا عبارت جستجو را تغییر دهید."/>
      <div v-else class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-right text-sm">
          <thead class="bg-slate-50 text-xs text-slate-400">
          <tr>
            <th class="p-4">محصول</th>
            <th class="p-4">شناسه انبار</th>
            <th class="p-4">ویژگی‌ها</th>
            <th class="p-4">موجودی</th>
            <th class="p-4">عملیات</th>
          </tr>
          </thead>
          <tbody class="divide-y">
          <tr v-for="v in data.data" :key="v.id">
            <td class="p-4 font-bold">{{ v.product?.title }}</td>
            <td class="p-4 font-mono text-xs">{{ v.sku }}</td>
            <td class="p-4 text-slate-500">{{ Object.values(v.attributes || {}).join(' / ') || '—' }}</td>
            <td class="p-4"><span
                :class="['rounded-lg px-3 py-1 font-black',v.stock<=v.low_stock_threshold?'bg-rose-50 text-rose-700':'bg-emerald-50 text-emerald-700']">{{ number(v.stock) }}</span>
            </td>
            <td class="p-4">
              <button class="rounded-lg bg-brand-50 px-3 py-2 text-xs font-bold text-brand-700" @click="editing=v">اصلاح
                موجودی
              </button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-if="editing" class="fixed inset-0 z-50 grid place-items-center bg-slate-950/40 p-4"
         @click.self="editing=null">
      <form class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl" @submit.prevent="adjust"><h2
          class="text-lg font-black">اصلاح موجودی {{ editing.product?.title }}</h2>
        <p class="mt-1 text-sm text-slate-400">موجودی فعلی: {{ number(editing.stock) }}</p><label
            class="mt-5 block text-sm font-bold">مقدار تغییر<input v-model.number="form.quantity" required type="number"
                                                                   class="mt-2 h-11 w-full rounded-xl border px-3"><span
            class="mt-1 block text-xs font-normal text-slate-400">برای کاهش موجودی عدد منفی وارد کنید.</span></label><label
            class="mt-4 block text-sm font-bold">دلیل<select v-model="form.reason"
                                                             class="mt-2 h-11 w-full rounded-xl border px-3">
          <option value="purchase">خرید و ورود به انبار</option>
          <option value="correction">اصلاح شمارش</option>
          <option value="return">مرجوعی مشتری</option>
          <option value="damage">خرابی یا ضایعات</option>
          <option value="manual">تغییر دستی</option>
        </select></label><label class="mt-4 block text-sm font-bold">یادداشت<input v-model="form.note"
                                                                                   class="mt-2 h-11 w-full rounded-xl border px-3"
                                                                                   placeholder="اختیاری"></label>
        <div class="mt-6 flex gap-2">
          <button :disabled="saving" class="flex-1 rounded-xl bg-brand-700 py-3 text-sm font-bold text-white">
            {{ saving ? 'در حال ثبت…' : 'ثبت تغییر' }}
          </button>
          <button type="button" class="rounded-xl border px-5 text-sm font-bold" @click="editing=null">انصراف</button>
        </div>
      </form>
    </div>
  </div>
</template>
