<script setup lang="ts">
import {useApi} from "../composables/useApi";



definePageMeta({middleware: 'auth'});
useHead({title: 'کمپین‌های فروش'});
const {request} = useApi();
const {data, refresh} = await useAsyncData('campaigns', () => request<any[]>('/admin/campaigns'));
const {data: products} = await useAsyncData('campaign-products', () => request<any>('/admin/products', {query: {per_page: 100}}));
const open = ref(false), saving = ref(false);
const initial = () => ({
  title: '',
  slug: '',
  badge: 'فروش ویژه',
  starts_at: new Date().toISOString().slice(0, 16),
  ends_at: new Date(Date.now() + 7 * 86400000).toISOString().slice(0, 16),
  is_active: true,
  items: [{variant_id: '', sale_price: 0, stock_limit: null}]
});
const form = reactive<any>(initial());
const variants = computed(() => products.value?.data?.flatMap((p: any) => p.variants.map((v: any) => ({
  ...v,
  product_title: p.title
}))) || []);
watch(() => form.title, v => form.slug = v.trim().toLowerCase().replace(/\s+/g, '-').replace(/[^\u0600-\u06ff\w-]/g, ''));

async function save() {
  saving.value = true;
  try {
    await request('/admin/campaigns', {method: 'POST', body: form});
    Object.assign(form, initial());
    open.value = false;
    await refresh()
  } finally {
    saving.value = false
  }
}

async function remove(id: number) {
  await request(`/admin/campaigns/${id}`, {method: 'DELETE'});
  await refresh()
}

const money = (v = 0) => new Intl.NumberFormat('fa-IR').format(v)</script>
<template>
  <div class="space-y-5">
    <div class="flex items-center justify-between">
      <SectionIntro title="کمپین‌های فروش"
                    text="فروش ویژه زمان‌دار بسازید و برای هر تنوع قیمت و ظرفیت مجزا تعیین کنید."/>
      <button class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-bold text-white" @click="open=!open">کمپین جدید
      </button>
    </div>
    <form v-if="open" class="rounded-2xl border bg-white p-6" @submit.prevent="save">
      <div class="grid gap-4 md:grid-cols-2"><input v-model="form.title" required class="h-11 rounded-xl border px-3"
                                                    placeholder="عنوان کمپین"><input v-model="form.slug" required
                                                                                     dir="ltr"
                                                                                     class="h-11 rounded-xl border px-3 text-left"
                                                                                     placeholder="slug"><input
          v-model="form.badge" class="h-11 rounded-xl border px-3" placeholder="برچسب"><input v-model="form.starts_at"
                                                                                              required
                                                                                              type="datetime-local"
                                                                                              class="h-11 rounded-xl border px-3"><input
          v-model="form.ends_at" required type="datetime-local" class="h-11 rounded-xl border px-3"></div>
      <div class="mt-5 space-y-3">
        <div v-for="(item,i) in form.items" :key="i"
             class="grid gap-3 rounded-xl bg-slate-50 p-3 md:grid-cols-[1fr_160px_130px_40px]"><select
            v-model="item.variant_id" required class="h-10 rounded-lg border px-2">
          <option value="">انتخاب محصول و تنوع</option>
          <option v-for="v in variants" :key="v.id" :value="v.id">{{ v.product_title }} — {{ v.sku }}
            ({{ money(v.price) }})
          </option>
        </select><input v-model.number="item.sale_price" required type="number" min="1"
                        class="h-10 rounded-lg border px-2" placeholder="قیمت ویژه"><input
            v-model.number="item.stock_limit" type="number" min="1" class="h-10 rounded-lg border px-2"
            placeholder="ظرفیت">
          <button v-if="form.items.length>1" type="button" class="rounded-lg bg-rose-50 text-rose-600"
                  @click="form.items.splice(i,1)">×
          </button>
        </div>
        <button type="button" class="rounded-lg border px-4 py-2 text-sm font-bold"
                @click="form.items.push({variant_id:'',sale_price:0,stock_limit:null})">افزودن محصول
        </button>
      </div>
      <button :disabled="saving" class="mt-5 rounded-xl bg-slate-900 px-6 py-3 font-bold text-white">
        {{ saving ? 'در حال ذخیره…' : 'ذخیره کمپین' }}
      </button>
    </form>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <article v-for="c in data" :key="c.id" class="rounded-2xl border bg-white p-5">
        <div class="flex justify-between">
          <div><span class="rounded-lg bg-rose-50 px-2 py-1 text-xs font-bold text-rose-700">{{ c.badge }}</span>
            <h2 class="mt-3 font-black">{{ c.title }}</h2></div>
          <button class="text-rose-600" @click="remove(c.id)">حذف</button>
        </div>
        <p class="mt-3 text-xs text-slate-400">{{ new Date(c.starts_at).toLocaleString('fa-IR') }} تا
          {{ new Date(c.ends_at).toLocaleString('fa-IR') }}</p>
        <div class="mt-4 divide-y">
          <div v-for="item in c.items" :key="item.id" class="flex justify-between py-2 text-xs"><span>{{ item.title }} — {{ item.sku }}</span><b>{{ money(item.sale_price) }}</b>
          </div>
        </div>
      </article>
      <EmptyState v-if="!data?.length" title="کمپینی ساخته نشده" text="اولین فروش ویژه زمان‌دار را ایجاد کنید."/>
    </div>
  </div>
</template>
