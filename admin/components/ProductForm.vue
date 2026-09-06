<script setup lang="ts">
import {useApi} from "../composables/useApi";

const props = defineProps<{ product?: any }>(), emit = defineEmits(['saved']);
const {request} = useApi();
const saving = ref(false), uploading = ref(false), error = ref('');
const {data: categories} = await useAsyncData('form-categories', () => request<any[]>('/admin/categories'));
const {data: brands} = await useAsyncData('form-brands', () => request<any>('/admin/brands'));
const emptyVariant = () => ({
  sku: '',
  price: 0,
  compare_at_price: null,
  stock: 0,
  attributes: {color: '', size: '', material: ''},
  is_active: true
});
const form = reactive<any>({
  title: '',
  slug: '',
  category_id: '',
  brand_id: null,
  short_description: '',
  description: '',
  status: 'draft',
  is_featured: false,
  product_type: 'physical',
  variants: [emptyVariant()],
  media: []
});
if (props.product) Object.assign(form, {
  ...props.product,
  variants: props.product.variants?.length ? props.product.variants : [emptyVariant()],
  media: props.product.media || []
});
watch(() => form.title, title => {
  if (!props.product) form.slug = title.trim().toLowerCase().replace(/\s+/g, '-').replace(/[^\u0600-\u06ff\w-]/g, '')
});

async function upload(event: Event) {
  const files = (event.target as HTMLInputElement).files;
  if (!files?.length) return;
  uploading.value = true;
  try {
    for (const file of Array.from(files)) {
      const body = new FormData();
      body.append('file', file);
      body.append('directory', 'products');
      form.media.push(await request('/admin/media', {method: 'POST', body}))
    }
  } finally {
    uploading.value = false
  }
}

async function save() {
  saving.value = true;
  error.value = '';
  try {
    const payload = {
      ...form,
      brand_id: form.brand_id || null,
      media: form.media.map((m: any, i: number) => ({path: m.path, alt: m.alt || form.title, sort_order: i}))
    };
    const result = await request(props.product ? `/admin/products/${props.product.id}` : '/admin/products', {
      method: props.product ? 'PUT' : 'POST',
      body: payload
    });
    emit('saved', result)
  } catch (e: any) {
    error.value = e?.data?.message || Object.values(e?.data?.errors || {})[0]?.[0] || 'ذخیره محصول انجام نشد.'
  } finally {
    saving.value = false
  }
}
</script>
<template>
  <form class="space-y-6" @submit.prevent="save">
    <div v-if="error" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">{{ error }}</div>
    <section class="rounded-2xl border bg-white p-5 shadow-soft"><h3 class="mb-5 font-black">اطلاعات اصلی</h3>
      <div class="grid gap-5 md:grid-cols-2"><label><span class="mb-2 block text-sm font-bold">نام محصول</span><input
          v-model="form.title" required class="h-11 w-full rounded-xl border px-3 outline-none focus:border-brand-600"></label><label><span
          class="mb-2 block text-sm font-bold">نامک URL</span><input v-model="form.slug" dir="ltr" required
                                                                     class="h-11 w-full rounded-xl border px-3 text-left outline-none focus:border-brand-600"></label><label><span
          class="mb-2 block text-sm font-bold">دسته‌بندی</span><select v-model="form.category_id" required
                                                                       class="h-11 w-full rounded-xl border bg-white px-3">
        <option value="">انتخاب کنید</option>
        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.title }}</option>
      </select></label><label><span class="mb-2 block text-sm font-bold">برند</span><select v-model="form.brand_id"
                                                                                            class="h-11 w-full rounded-xl border bg-white px-3">
        <option :value="null">بدون برند</option>
        <option v-for="b in brands?.data" :key="b.id" :value="b.id">{{ b.title }}</option>
      </select></label><label class="md:col-span-2"><span
          class="mb-2 block text-sm font-bold">توضیح کوتاه</span><textarea v-model="form.short_description" rows="2"
                                                                           class="w-full rounded-xl border p-3 outline-none focus:border-brand-600"/></label><label
          class="md:col-span-2"><span class="mb-2 block text-sm font-bold">توضیحات کامل</span><textarea
          v-model="form.description" rows="6" class="w-full rounded-xl border p-3 outline-none focus:border-brand-600"/></label>
      </div>
    </section>
    <section class="rounded-2xl border bg-white p-5 shadow-soft">
      <div class="mb-5 flex items-center justify-between">
        <div><h3 class="font-black">تصاویر محصول</h3>
          <p class="mt-1 text-xs text-slate-400">حداکثر ۵ مگابایت برای هر تصویر</p></div>
        <label
            class="cursor-pointer rounded-xl border border-brand-200 bg-brand-50 px-4 py-2 text-sm font-bold text-brand-700"><input
            type="file" multiple accept="image/*" class="hidden"
            @change="upload">{{ uploading ? 'در حال آپلود…' : 'انتخاب تصویر' }}</label></div>
      <div v-if="form.media.length" class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
        <div v-for="(m,i) in form.media" :key="m.path"
             class="relative aspect-square overflow-hidden rounded-xl border bg-slate-50"><img
            :src="m.url||`http://127.0.0.1:8000/storage/${m.path}`" class="size-full object-cover">
          <button type="button" class="absolute left-2 top-2 size-7 rounded-lg bg-white text-rose-600 shadow"
                  @click="form.media.splice(i,1)">×
          </button>
        </div>
      </div>
      <EmptyState v-else title="تصویری انتخاب نشده" text="تصویر اول، تصویر اصلی محصول خواهد بود."/>
    </section>
    <section class="rounded-2xl border bg-white p-5 shadow-soft">
      <div class="mb-5 flex items-center justify-between">
        <div><h3 class="font-black">تنوع‌ها و موجودی</h3>
          <p class="mt-1 text-xs text-slate-400">برای هر رنگ، سایز یا جنس یک ردیف بسازید.</p></div>
        <button type="button" class="rounded-xl border px-4 py-2 text-sm font-bold"
                @click="form.variants.push(emptyVariant())">＋ تنوع جدید
        </button>
      </div>
      <div class="space-y-3">
        <div v-for="(v,i) in form.variants" :key="i"
             class="grid gap-3 rounded-xl bg-slate-50 p-4 sm:grid-cols-2 xl:grid-cols-7"><input v-model="v.sku"
                                                                                                dir="ltr" required
                                                                                                placeholder="SKU"
                                                                                                class="h-10 rounded-lg border px-3 text-left"><input
            v-model.number="v.price" type="number" min="0" placeholder="قیمت فروش"
            class="h-10 rounded-lg border px-3"><input v-model.number="v.compare_at_price" type="number" min="0"
                                                       placeholder="قیمت قبل" class="h-10 rounded-lg border px-3"><input
            v-model.number="v.stock" type="number" min="0" placeholder="موجودی"
            class="h-10 rounded-lg border px-3"><input v-model="v.attributes.color" placeholder="رنگ"
                                                       class="h-10 rounded-lg border px-3"><input
            v-model="v.attributes.size" placeholder="سایز/ابعاد" class="h-10 rounded-lg border px-3">
          <div class="flex gap-2"><input v-model="v.attributes.material" placeholder="جنس"
                                         class="h-10 min-w-0 flex-1 rounded-lg border px-3">
            <button v-if="form.variants.length>1" type="button" class="size-10 rounded-lg bg-rose-50 text-rose-600"
                    @click="form.variants.splice(i,1)">×
            </button>
          </div>
        </div>
      </div>
    </section>
    <section class="flex flex-col justify-between gap-4 rounded-2xl border bg-white p-5 sm:flex-row sm:items-center">
      <div class="flex gap-4"><select v-model="form.status" class="h-11 rounded-xl border bg-white px-3">
        <option value="draft">پیش‌نویس</option>
        <option value="published">انتشار</option>
        <option value="archived">بایگانی</option>
      </select><label class="flex items-center gap-2 text-sm font-bold"><input v-model="form.is_featured"
                                                                               type="checkbox"
                                                                               class="size-4 accent-brand-700">محصول
        ویژه</label></div>
      <div class="flex gap-3">
        <NuxtLink to="/products" class="rounded-xl border px-5 py-3 text-sm font-bold">انصراف</NuxtLink>
        <button :disabled="saving"
                class="rounded-xl bg-brand-700 px-7 py-3 text-sm font-bold text-white disabled:opacity-50">
          {{ saving ? 'در حال ذخیره…' : 'ذخیره محصول' }}
        </button>
      </div>
    </section>
  </form>
</template>
